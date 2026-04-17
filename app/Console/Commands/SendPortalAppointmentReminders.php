<?php

namespace App\Console\Commands;

use App\Models\Scheduling;
use App\Services\WhatsappNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendPortalAppointmentReminders extends Command
{
    protected $signature = 'portal:send-appointment-reminders
        {--windows=1440,180,60 : Janelas em minutos (ex.: 1440,180,60)}
        {--tolerance=10 : Tolerancia em minutos para cada janela}
        {--chunk=200 : Tamanho do lote}';

    protected $description = 'Envia lembretes de consulta (WhatsApp) em multiplas janelas (ex.: 24h, 3h, 1h)';

    public function __construct(private readonly WhatsappNotificationService $whatsappNotificationService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $windows = $this->parseWindows((string) $this->option('windows'));
        $tolerance = max(1, min(120, (int) $this->option('tolerance')));
        $chunk = max(1, min(500, (int) $this->option('chunk')));

        if ($windows === []) {
            $this->error('Nenhuma janela valida foi informada. Exemplo: --windows=1440,180,60');
            return self::FAILURE;
        }

        $maxWindow = max($windows);
        $now = now();
        $end = $now->copy()->addMinutes($maxWindow + $tolerance);

        $processed = 0;
        $sent = 0;
        $failed = 0;
        $skipped = 0;

        Scheduling::query()
            ->with(['paciente', 'profissional', 'procedimento'])
            ->whereDate('date', '>=', $now->toDateString())
            ->whereDate('date', '<=', $end->toDateString())
            ->whereNotIn('status', ['canceled', 'completed', 'no_show'])
            ->orderBy('id')
            ->chunkById($chunk, function ($schedulings) use (&$processed, &$sent, &$failed, &$skipped, $windows, $tolerance, $now) {
                foreach ($schedulings as $scheduling) {
                    $processed++;

                    $appointmentAt = Carbon::parse($scheduling->date->toDateString() . ' ' . $scheduling->time);
                    $minutesToAppointment = $now->diffInMinutes($appointmentAt, false);

                    if ($minutesToAppointment < 0) {
                        $skipped++;
                        continue;
                    }

                    $targetWindow = $this->resolveWindowToSend($minutesToAppointment, $windows, $tolerance);

                    if ($targetWindow === null) {
                        $skipped++;
                        continue;
                    }

                    $windowMap = is_array($scheduling->reminder_windows_sent_at)
                        ? $scheduling->reminder_windows_sent_at
                        : [];

                    if (isset($windowMap[(string) $targetWindow])) {
                        $skipped++;
                        continue;
                    }

                    $ok = $this->whatsappNotificationService->sendAppointmentReminder($scheduling, $targetWindow);

                    if (! $ok) {
                        $failed++;
                        continue;
                    }

                    $windowMap[(string) $targetWindow] = now()->toIso8601String();

                    $scheduling->update([
                        'reminder_sent_at' => now(),
                        'reminder_windows_sent_at' => $windowMap,
                    ]);

                    $sent++;
                }
            });

        $this->info("Lembretes processados: {$processed} | enviados: {$sent} | falhas: {$failed} | ignorados: {$skipped}");
        $this->line('Janelas ativas (min): ' . implode(', ', $windows));

        return self::SUCCESS;
    }

    /**
     * @return array<int>
     */
    private function parseWindows(string $raw): array
    {
        $windows = collect(explode(',', $raw))
            ->map(fn (string $item) => (int) trim($item))
            ->filter(fn (int $item) => $item > 0)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        return array_values($windows);
    }

    /**
     * @param array<int> $windows
     */
    private function resolveWindowToSend(int $minutesToAppointment, array $windows, int $tolerance): ?int
    {
        foreach ($windows as $window) {
            if ($minutesToAppointment <= $window && $minutesToAppointment >= ($window - $tolerance)) {
                return $window;
            }
        }

        return null;
    }
}
