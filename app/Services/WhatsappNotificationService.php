<?php

namespace App\Services;

use App\Models\WhatsappDispatchLog;
use App\Models\Scheduling;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappNotificationService
{
    public function sendAppointmentConfirmation(Scheduling $scheduling): bool
    {
        $phone = $this->resolvePatientPhone($scheduling);
        if (! $phone) {
            Log::warning('WhatsApp confirmation skipped: patient phone not found', [
                'scheduling_id' => $scheduling->id,
                'patient_id' => $scheduling->patient_id,
            ]);

            $this->logDispatch('skipped', [
                'type' => 'appointment_confirmation',
                'scheduling_id' => $scheduling->id,
                'patient_id' => $scheduling->patient_id,
                'reason' => 'patient_phone_not_found',
            ], null, null, null);

            return false;
        }

        $message = $this->buildConfirmationMessage($scheduling);

        return $this->sendText($phone, $message, [
            'type' => 'appointment_confirmation',
            'scheduling_id' => $scheduling->id,
        ]);
    }

    public function sendAppointmentReminder(Scheduling $scheduling, ?int $windowMinutes = null): bool
    {
        $phone = $this->resolvePatientPhone($scheduling);
        if (! $phone) {
            Log::warning('WhatsApp reminder skipped: patient phone not found', [
                'scheduling_id' => $scheduling->id,
                'patient_id' => $scheduling->patient_id,
            ]);

            $this->logDispatch('skipped', [
                'type' => 'appointment_reminder',
                'scheduling_id' => $scheduling->id,
                'patient_id' => $scheduling->patient_id,
                'window_minutes' => $windowMinutes,
                'reason' => 'patient_phone_not_found',
            ], null, null, null);

            return false;
        }

        $message = $this->buildReminderMessage($scheduling, $windowMinutes);

        return $this->sendText($phone, $message, [
            'type' => 'appointment_reminder',
            'scheduling_id' => $scheduling->id,
            'window_minutes' => $windowMinutes,
        ]);
    }

    public function sendText(string $phone, string $message, array $context = []): bool
    {
        $provider = (string) config('services.whatsapp.provider', 'webhook');
        $webhookUrl = (string) config('services.whatsapp.webhook_url', '');
        $baseUrl = (string) config('services.whatsapp.base_url', '');
        $instance = (string) config('services.whatsapp.instance', '');
        $token = (string) config('services.whatsapp.token', '');
        $enabled = (bool) config('services.whatsapp.enabled', false);
        $timeout = (int) config('services.whatsapp.timeout', 10);

        if (! $enabled) {
            Log::info('WhatsApp provider not configured. Message not sent.', [
                'provider' => $provider,
                'phone' => $phone,
                'preview' => mb_substr($message, 0, 120),
                'context' => $context,
            ]);

            $this->logDispatch('skipped', $context, $phone, null, null, 'provider_disabled');

            return false;
        }

        if ($provider === 'evolution') {
            return $this->sendViaEvolution($phone, $message, $token, $timeout, $baseUrl, $instance, $context);
        }

        if ($webhookUrl === '') {
            Log::warning('WhatsApp webhook URL missing.', [
                'provider' => $provider,
                'phone' => $phone,
                'context' => $context,
            ]);

            $this->logDispatch('failed', $context, $phone, null, null, 'missing_webhook_url');

            return false;
        }

        try {
            $response = Http::timeout($timeout)
                ->withToken($token)
                ->post($webhookUrl, [
                    'phone' => $phone,
                    'message' => $message,
                    'context' => $context,
                ]);

            if ($response->successful()) {
                $this->logDispatch('sent', $context, $phone, $response->status(), $response->body());
                return true;
            }

            Log::warning('WhatsApp provider returned non-success response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'phone' => $phone,
                'context' => $context,
            ]);

            $this->logDispatch('failed', $context, $phone, $response->status(), $response->body(), 'provider_non_success');

            return false;
        } catch (\Throwable $e) {
            Log::error('WhatsApp send failed', [
                'message' => $e->getMessage(),
                'phone' => $phone,
                'context' => $context,
            ]);

            $this->logDispatch('failed', $context, $phone, null, $e->getMessage(), 'provider_exception');

            return false;
        }
    }

    private function sendViaEvolution(
        string $phone,
        string $message,
        string $token,
        int $timeout,
        string $baseUrl,
        string $instance,
        array $context = []
    ): bool {
        if ($baseUrl === '' || $instance === '' || $token === '') {
            Log::warning('Evolution WhatsApp not configured completely.', [
                'has_base_url' => $baseUrl !== '',
                'has_instance' => $instance !== '',
                'has_token' => $token !== '',
                'phone' => $phone,
                'context' => $context,
            ]);

            $this->logDispatch('failed', $context, $phone, null, null, 'evolution_config_incomplete');

            return false;
        }

        $url = rtrim($baseUrl, '/') . '/message/sendText/' . $instance;

        try {
            $response = Http::timeout($timeout)
                ->withHeaders([
                    'apikey' => $token,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'number' => $phone,
                    'text' => $message,
                ]);

            if ($response->successful()) {
                $this->logDispatch('sent', $context, $phone, $response->status(), $response->body());
                return true;
            }

            Log::warning('Evolution WhatsApp returned non-success response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'phone' => $phone,
                'context' => $context,
            ]);

            $this->logDispatch('failed', $context, $phone, $response->status(), $response->body(), 'provider_non_success');

            return false;
        } catch (\Throwable $e) {
            Log::error('Evolution WhatsApp send failed', [
                'message' => $e->getMessage(),
                'phone' => $phone,
                'context' => $context,
            ]);

            $this->logDispatch('failed', $context, $phone, null, $e->getMessage(), 'provider_exception');

            return false;
        }
    }

    private function resolvePatientPhone(Scheduling $scheduling): ?string
    {
        $rawPhone = (string) ($scheduling->paciente?->celular ?: $scheduling->paciente?->telefone ?: '');
        $digits = preg_replace('/\D/', '', $rawPhone ?? '');

        if (! $digits) {
            return null;
        }

        if (str_starts_with($digits, '55')) {
            return $digits;
        }

        return '55' . $digits;
    }

    private function buildConfirmationMessage(Scheduling $scheduling): string
    {
        $patientName = (string) ($scheduling->paciente?->name ?? 'Paciente');
        $dentistName = (string) ($scheduling->profissional?->name ?? 'Dentista');
        $procedure = (string) ($scheduling->procedimento?->name ?? 'Consulta odontologica');
        $dateFormatted = $this->formatDate($scheduling->date);
        $time = (string) ($scheduling->time ?? '--:--');

        return "Ola {$patientName}! Seu agendamento foi confirmado.\n\n" .
            "Dentista: {$dentistName}\n" .
            "Servico: {$procedure}\n" .
            "Data: {$dateFormatted}\n" .
            "Horario: {$time}\n\n" .
            "Se precisar remarcar, entre em contato com a clinica.";
    }

    private function buildReminderMessage(Scheduling $scheduling, ?int $windowMinutes = null): string
    {
        $patientName = (string) ($scheduling->paciente?->name ?? 'Paciente');
        $dentistName = (string) ($scheduling->profissional?->name ?? 'Dentista');
        $procedure = (string) ($scheduling->procedimento?->name ?? 'Consulta odontologica');
        $dateFormatted = $this->formatDate($scheduling->date);
        $time = (string) ($scheduling->time ?? '--:--');

        $windowText = $this->buildReminderWindowText($windowMinutes);

        return "Ola {$patientName}! {$windowText}\n\n" .
            "Dentista: {$dentistName}\n" .
            "Servico: {$procedure}\n" .
            "Data: {$dateFormatted}\n" .
            "Horario: {$time}\n\n" .
            "Aguardamos voce.";
    }

    private function buildReminderWindowText(?int $windowMinutes): string
    {
        if ($windowMinutes === null || $windowMinutes <= 0) {
            return 'Lembrete da sua consulta.';
        }

        if ($windowMinutes % 1440 === 0) {
            $days = (int) ($windowMinutes / 1440);
            return $days === 1
                ? 'Lembrete da sua consulta de amanha.'
                : "Lembrete da sua consulta em {$days} dias.";
        }

        if ($windowMinutes % 60 === 0) {
            $hours = (int) ($windowMinutes / 60);
            return $hours === 1
                ? 'Lembrete da sua consulta em 1 hora.'
                : "Lembrete da sua consulta em {$hours} horas.";
        }

        return "Lembrete da sua consulta em {$windowMinutes} minutos.";
    }

    private function logDispatch(
        string $status,
        array $context,
        ?string $destination,
        ?int $responseCode,
        ?string $responseBody,
        ?string $reason = null
    ): void {
        try {
            WhatsappDispatchLog::create([
                'scheduling_id' => isset($context['scheduling_id']) ? (int) $context['scheduling_id'] : null,
                'message_type' => isset($context['type']) ? (string) $context['type'] : null,
                'provider' => (string) config('services.whatsapp.provider', 'webhook'),
                'destination' => $destination,
                'status' => $status,
                'reason' => $reason,
                'response_code' => $responseCode,
                'response_body' => $responseBody,
                'context' => $context,
                'dispatched_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to persist WhatsApp dispatch telemetry.', [
                'error' => $e->getMessage(),
                'status' => $status,
                'context' => $context,
            ]);
        }
    }

    private function formatDate($date): string
    {
        if ($date instanceof Carbon) {
            return $date->format('d/m/Y');
        }

        return Carbon::parse((string) $date)->format('d/m/Y');
    }
}
