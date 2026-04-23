<?php

namespace App\Console\Commands;

use App\Models\WhatsappDispatchLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MonitorWhatsappDelivery extends Command
{
    protected $signature = 'portal:monitor-whatsapp-delivery
        {--minutes=30 : Janela de analise em minutos}
        {--min-attempts=10 : Minimo de tentativas para avaliar alerta}
        {--max-failure-rate=0.30 : Taxa maxima tolerada de falhas (0 a 1)}';

    protected $description = 'Monitora taxa de falha de envio WhatsApp e emite alertas operacionais';

    public function handle(): int
    {
        $minutes = max(5, min(1440, (int) $this->option('minutes')));
        $minAttempts = max(1, (int) $this->option('min-attempts'));
        $maxFailureRate = (float) $this->option('max-failure-rate');

        if ($maxFailureRate < 0 || $maxFailureRate > 1) {
            $this->error('Opcao --max-failure-rate deve estar entre 0 e 1.');
            return self::FAILURE;
        }

        $from = now()->subMinutes($minutes);

        $baseQuery = WhatsappDispatchLog::query()->where('dispatched_at', '>=', $from);
        $total = (clone $baseQuery)->count();
        $sent = (clone $baseQuery)->where('status', 'sent')->count();
        $failed = (clone $baseQuery)->where('status', 'failed')->count();
        $skipped = (clone $baseQuery)->where('status', 'skipped')->count();

        $failureRate = $total > 0 ? $failed / $total : 0.0;

        $this->info("Janela: {$minutes}min | tentativas: {$total} | enviadas: {$sent} | falhas: {$failed} | ignoradas: {$skipped} | taxa_falha: " . number_format($failureRate * 100, 2) . '%');

        if ($total < $minAttempts) {
            $this->line("Sem alerta: tentativas insuficientes (minimo {$minAttempts}).");
            return self::SUCCESS;
        }

        if ($failureRate <= $maxFailureRate) {
            $this->line('Sem alerta: taxa de falha dentro do limite.');
            return self::SUCCESS;
        }

        $message = sprintf(
            'ALERTA WhatsApp: taxa de falha %.2f%% acima do limite %.2f%% na ultima janela de %d minutos. Tentativas=%d, falhas=%d, enviadas=%d, ignoradas=%d.',
            $failureRate * 100,
            $maxFailureRate * 100,
            $minutes,
            $total,
            $failed,
            $sent,
            $skipped
        );

        Log::warning($message, [
            'minutes' => $minutes,
            'attempts' => $total,
            'failed' => $failed,
            'sent' => $sent,
            'skipped' => $skipped,
            'failure_rate' => $failureRate,
            'threshold' => $maxFailureRate,
        ]);

        $alertEmail = (string) config('services.whatsapp.alert_email', '');
        if ($alertEmail !== '') {
            try {
                Mail::raw($message, function ($mail) use ($alertEmail) {
                    $mail->to($alertEmail)
                        ->subject('Alerta WhatsApp - Falhas de envio');
                });
            } catch (\Throwable $e) {
                Log::warning('Falha ao enviar e-mail de alerta WhatsApp.', [
                    'error' => $e->getMessage(),
                    'alert_email' => $alertEmail,
                ]);
            }
        }

        return self::SUCCESS;
    }
}
