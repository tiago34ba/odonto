<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\Paciente;
use App\Models\Procedure;
use App\Models\Scheduling;
use App\Services\WhatsappNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TestPortalWhatsappFlow extends Command
{
    protected $signature = 'portal:test-whatsapp-flow
        {--name=Paciente Teste WhatsApp : Nome do paciente}
        {--phone= : WhatsApp do paciente (com DDD)}';

    protected $description = 'Cria agendamento de teste no portal e tenta enviar confirmacao por WhatsApp';

    public function __construct(private readonly WhatsappNotificationService $whatsappNotificationService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $name = (string) $this->option('name');
        $phoneDigits = preg_replace('/\D/', '', (string) $this->option('phone')) ?: '';

        if ($phoneDigits === '') {
            $this->error('Telefone invalido para teste.');
            return self::FAILURE;
        }

        $paciente = Paciente::query()->updateOrCreate(
            ['email' => 'paciente.teste.whatsapp@odonto.local'],
            [
                'name' => $name,
                'telefone' => $phoneDigits,
                'celular' => $phoneDigits,
                'data_nascimento' => '1990-01-01',
                'cpf_cnpj' => '98765432100',
                'cidade' => 'Salvador',
                'estado' => 'BA',
            ]
        );

        $dentista = Employee::query()
            ->where('active', true)
            ->where('role', 'dentist')
            ->orderBy('id')
            ->first();

        if (! $dentista) {
            $this->error('Nenhum dentista ativo encontrado em employees para o teste.');
            return self::FAILURE;
        }

        $procedimento = Procedure::query()->where('active', true)->orderBy('id')->first();

        if (! $procedimento) {
            $procedimento = Procedure::query()->orderBy('id')->first();
        }

        if (! $procedimento) {
            $procedimento = Procedure::query()->create([
                'name' => 'Consulta Inicial WhatsApp',
                'value' => 120,
                'time' => 30,
                'active' => true,
            ]);
        }

        $date = Carbon::now()->addDays(3)->toDateString(); // Use 3 days ahead to avoid conflicts
        $time = '14:00'; // Use 14:00 instead of 10:00

        $scheduling = Scheduling::query()->create([
            'patient_id' => $paciente->id,
            'professional_id' => $dentista->id,
            'procedure_id' => $procedimento->id,
            'date' => $date,
            'time' => $time,
            'return' => false,
            'obs' => 'Teste automatico WhatsApp portal',
            'status' => 'scheduled',
            'scheduled_at' => now(),
            'duration' => (int) ($procedimento->time ?: 30),
        ]);

        $scheduling->load(['paciente', 'profissional', 'procedimento']);

        $confirmSent = $this->whatsappNotificationService->sendAppointmentConfirmation($scheduling);
        if ($confirmSent) {
            $scheduling->update(['whatsapp_notification_sent_at' => now()]);
        }

        $this->line('--- TESTE PORTAL WHATSAPP ---');
        $this->line('Scheduling ID: ' . $scheduling->id);
        $this->line('Paciente: ' . $paciente->name);
        $this->line('WhatsApp: ' . $phoneDigits);
        $this->line('Dentista: ' . $dentista->name);
        $this->line('Procedimento: ' . $procedimento->name);
        $this->line('Data/Hora: ' . $date . ' ' . $time);
        $this->line('Confirmacao enviada: ' . ($confirmSent ? 'SIM' : 'NAO'));

        return self::SUCCESS;
    }
}
