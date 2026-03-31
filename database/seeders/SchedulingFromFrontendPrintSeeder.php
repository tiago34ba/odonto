<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Paciente;
use App\Models\Procedure;
use App\Models\Scheduling;
use Illuminate\Database\Seeder;

class SchedulingFromFrontendPrintSeeder extends Seeder
{
    public function run(): void
    {
        $date = now()->toDateString();

        $rows = [
            [
                'paciente' => 'Luciana',
                'dentista' => 'Dra. Maria Santos',
                'procedimento' => 'Limpeza Dental (Profilaxia)',
                'hora' => '15:00',
                'status' => 'completed',
                'observacoes' => null,
            ],
            [
                'paciente' => 'Paulo Tiago Oliveira de Magalhaes',
                'dentista' => 'Dr. Joao Silva',
                'procedimento' => 'Aparelho Ortodontico',
                'hora' => '11:00',
                'status' => 'completed',
                'observacoes' => 'Procedimento concluido com sucesso',
            ],
            [
                'paciente' => 'Paulo Tiago Oliveira de Magalhaes',
                'dentista' => 'Carlos Oliveira',
                'procedimento' => 'Implante Dentario',
                'hora' => '08:00',
                'status' => 'in_progress',
                'observacoes' => 'Procedimento em andamento',
            ],
            [
                'paciente' => 'Paulo Tiago Oliveira de Magalhaes',
                'dentista' => 'Dr. Joao Silva',
                'procedimento' => 'Limpeza Dental (Profilaxia)',
                'hora' => '08:00',
                'status' => 'scheduled',
                'observacoes' => 'Primeira consulta do paciente',
            ],
            [
                'paciente' => 'Luciana',
                'dentista' => 'Dra. Maria Santos',
                'procedimento' => 'Restauracao com Resina',
                'hora' => '09:30',
                'status' => 'scheduled',
                'observacoes' => 'Paciente com historico de alergia a anestesico',
            ],
            [
                'paciente' => 'Paulo Tiago Oliveira de Magalhaes',
                'dentista' => 'Dr. Joao Silva',
                'procedimento' => 'Extracao Dental Simples',
                'hora' => '10:00',
                'status' => 'confirmed',
                'observacoes' => null,
            ],
        ];

        $inserted = 0;

        foreach ($rows as $row) {
            $paciente = Paciente::firstOrCreate(
                ['name' => $row['paciente']],
                [
                    'data_nascimento' => now()->subYears(30)->toDateString(),
                    'telefone' => null,
                ]
            );

            $employee = Employee::firstOrCreate(
                ['name' => $row['dentista']],
                [
                    'phone' => '(11) 90000-0000',
                    'email' => strtolower(str_replace(' ', '.', preg_replace('/[^A-Za-z0-9 ]/', '', $row['dentista']))) . '@odonto.local',
                    'role' => 'dentist',
                    'active' => true,
                ]
            );

            $procedure = Procedure::firstOrCreate(
                ['name' => $row['procedimento']],
                [
                    'value' => 200.00,
                    'time' => 60,
                    'accepts_agreement' => false,
                ]
            );

            $alreadyExists = Scheduling::query()
                ->where('patient_id', $paciente->id)
                ->where('professional_id', $employee->id)
                ->where('procedure_id', $procedure->id)
                ->whereDate('date', $date)
                ->where('time', $row['hora'])
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            Scheduling::create([
                'patient_id' => $paciente->id,
                'professional_id' => $employee->id,
                'procedure_id' => $procedure->id,
                'date' => $date,
                'time' => $row['hora'],
                'return' => false,
                'obs' => $row['observacoes'],
                'status' => $row['status'],
                'duration' => 60,
                'scheduled_at' => now(),
                'confirmed_at' => in_array($row['status'], ['confirmed', 'in_progress', 'completed'], true) ? now() : null,
                'canceled_at' => null,
                'cancellation_reason' => null,
            ]);

            $inserted++;
        }

        $this->command?->info("SchedulingFromFrontendPrintSeeder: {$inserted} novos registros inseridos.");
    }
}
