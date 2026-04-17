<?php

namespace App\Console\Commands;

use App\Models\Scheduling;
use App\Services\WhatsappNotificationService;
use Illuminate\Console\Command;

class SendPortalAppointmentConfirmations extends Command
{
    protected $signature = 'portal:send-appointment-confirmations
        {--hours=48 : Janela em horas para agendamentos pendentes de confirmacao}
        {--chunk=100 : Tamanho do lote por processamento}';

    protected $description = 'Envia confirmacoes pendentes de agendamentos via WhatsApp';

    public function __construct(private readonly WhatsappNotificationService $whatsappNotificationService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $hours = max(1, (int) $this->option('hours'));
        $chunk = max(1, min(500, (int) $this->option('chunk')));
        $now = now();
        $limitDate = $now->copy()->addHours($hours)->toDateString();

        $processed = 0;
        $sent = 0;
        $failed = 0;
        $skipped = 0;

        Scheduling::query()
            ->with(['paciente', 'profissional', 'procedimento'])
            ->whereNull('whatsapp_notification_sent_at')
            ->whereNotIn('status', ['canceled', 'completed', 'no_show'])
            ->whereDate('date', '>=', $now->toDateString())
            ->whereDate('date', '<=', $limitDate)
            ->orderBy('id')
            ->chunkById($chunk, function ($schedulings) use (&$processed, &$sent, &$failed, &$skipped, $hours, $now) {
                foreach ($schedulings as $scheduling) {
                    $processed++;

                    $appointmentAt = \Carbon\Carbon::parse($scheduling->date->toDateString() . ' ' . $scheduling->time);
                    $minutesToAppointment = $now->diffInMinutes($appointmentAt, false);

                    if ($minutesToAppointment < 0 || $minutesToAppointment > ($hours * 60)) {
                        $skipped++;
                        continue;
                    }

                    $ok = $this->whatsappNotificationService->sendAppointmentConfirmation($scheduling);
                    if ($ok) {
                        $scheduling->update(['whatsapp_notification_sent_at' => now()]);
                        $sent++;
                    } else {
                        $failed++;
                    }
                }
            });

        $this->info("Confirmacoes processadas: {$processed} | enviadas: {$sent} | falhas: {$failed} | ignoradas: {$skipped}");

        return self::SUCCESS;
    }
}
