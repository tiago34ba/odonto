<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Scheduling;
use App\Models\Paciente;
use App\Models\Employee;
use App\Models\Procedure;
use Carbon\Carbon;

class SchedulingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar pacientes, funcionários e procedimentos cadastrados
        $pacientes = Paciente::limit(10)->get();
        $employees = Employee::limit(5)->get();
        $procedures = Procedure::limit(8)->get();

        if ($pacientes->isEmpty() || $employees->isEmpty() || $procedures->isEmpty()) {
            $this->command->warn('SchedulingSeeder: pacientes, employees ou procedures não encontrados. Execute os seeders anteriores primeiro.');
            return;
        }

        // Limpar registros existentes desabilitando FK checks temporariamente
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Scheduling::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $now = Carbon::now();

        $schedulings = [
            // Agendados (hoje e futuros)
            [
                'patient_id'      => $pacientes->get(0)?->id ?? $pacientes->random()->id,
                'professional_id' => $employees->get(0)?->id ?? $employees->random()->id,
                'procedure_id'    => $procedures->get(0)?->id ?? $procedures->random()->id,
                'date'            => Carbon::today()->addDays(1)->toDateString(),
                'time'            => '08:00',
                'return'          => false,
                'obs'             => 'Primeira consulta do paciente',
                'status'          => 'scheduled',
                'duration'        => 60,
                'scheduled_at'    => $now,
            ],
            [
                'patient_id'      => $pacientes->get(1)?->id ?? $pacientes->random()->id,
                'professional_id' => $employees->get(1)?->id ?? $employees->random()->id,
                'procedure_id'    => $procedures->get(1)?->id ?? $procedures->random()->id,
                'date'            => Carbon::today()->addDays(1)->toDateString(),
                'time'            => '09:30',
                'return'          => false,
                'obs'             => 'Paciente com histórico de alergia a anestésico',
                'status'          => 'scheduled',
                'duration'        => 45,
                'scheduled_at'    => $now,
            ],
            // Confirmados
            [
                'patient_id'      => $pacientes->get(2)?->id ?? $pacientes->random()->id,
                'professional_id' => $employees->get(0)?->id ?? $employees->random()->id,
                'procedure_id'    => $procedures->get(2)?->id ?? $procedures->random()->id,
                'date'            => Carbon::today()->addDays(2)->toDateString(),
                'time'            => '10:00',
                'return'          => false,
                'obs'             => 'Confirmado via telefone',
                'status'          => 'confirmed',
                'duration'        => 90,
                'scheduled_at'    => $now->copy()->subDays(2),
                'confirmed_at'    => $now,
            ],
            [
                'patient_id'      => $pacientes->get(3)?->id ?? $pacientes->random()->id,
                'professional_id' => $employees->get(1)?->id ?? $employees->random()->id,
                'procedure_id'    => $procedures->get(3)?->id ?? $procedures->random()->id,
                'date'            => Carbon::today()->addDays(2)->toDateString(),
                'time'            => '14:30',
                'return'          => true,
                'obs'             => 'Retorno para avaliação do procedimento anterior',
                'status'          => 'confirmed',
                'duration'        => 30,
                'scheduled_at'    => $now->copy()->subDays(3),
                'confirmed_at'    => $now->copy()->subDay(),
            ],
            // Em atendimento (hoje)
            [
                'patient_id'      => $pacientes->get(4)?->id ?? $pacientes->random()->id,
                'professional_id' => $employees->get(2)?->id ?? $employees->random()->id,
                'procedure_id'    => $procedures->get(4)?->id ?? $procedures->random()->id,
                'date'            => Carbon::today()->toDateString(),
                'time'            => '08:00',
                'return'          => false,
                'obs'             => 'Procedimento em andamento',
                'status'          => 'in_progress',
                'duration'        => 60,
                'scheduled_at'    => $now->copy()->subDays(1),
                'confirmed_at'    => $now->copy()->subDays(1),
            ],
            // Concluídos (passados)
            [
                'patient_id'      => $pacientes->get(0)?->id ?? $pacientes->random()->id,
                'professional_id' => $employees->get(0)?->id ?? $employees->random()->id,
                'procedure_id'    => $procedures->get(5)?->id ?? $procedures->random()->id,
                'date'            => Carbon::today()->subDays(3)->toDateString(),
                'time'            => '11:00',
                'return'          => false,
                'obs'             => 'Procedimento concluído com sucesso',
                'status'          => 'completed',
                'duration'        => 60,
                'scheduled_at'    => $now->copy()->subDays(5),
                'confirmed_at'    => $now->copy()->subDays(4),
            ],
            [
                'patient_id'      => $pacientes->get(2)?->id ?? $pacientes->random()->id,
                'professional_id' => $employees->get(1)?->id ?? $employees->random()->id,
                'procedure_id'    => $procedures->get(0)?->id ?? $procedures->random()->id,
                'date'            => Carbon::today()->subDays(5)->toDateString(),
                'time'            => '15:00',
                'return'          => false,
                'obs'             => null,
                'status'          => 'completed',
                'duration'        => 45,
                'scheduled_at'    => $now->copy()->subDays(7),
                'confirmed_at'    => $now->copy()->subDays(6),
            ],
            // Cancelados
            [
                'patient_id'      => $pacientes->get(5)?->id ?? $pacientes->random()->id,
                'professional_id' => $employees->get(0)?->id ?? $employees->random()->id,
                'procedure_id'    => $procedures->get(2)?->id ?? $procedures->random()->id,
                'date'            => Carbon::today()->addDays(4)->toDateString(),
                'time'            => '16:00',
                'return'          => false,
                'obs'             => null,
                'status'          => 'canceled',
                'duration'        => 60,
                'scheduled_at'    => $now->copy()->subDays(2),
                'canceled_at'     => $now->copy()->subDay(),
                'cancellation_reason' => 'Paciente solicitou cancelamento por motivo pessoal',
            ],
            // Futuros agendados
            [
                'patient_id'      => $pacientes->get(6)?->id ?? $pacientes->random()->id,
                'professional_id' => $employees->get(3)?->id ?? $employees->random()->id,
                'procedure_id'    => $procedures->get(6)?->id ?? $procedures->random()->id,
                'date'            => Carbon::today()->addDays(7)->toDateString(),
                'time'            => '09:00',
                'return'          => false,
                'obs'             => 'Procedimento complexo - atenção especial',
                'status'          => 'scheduled',
                'duration'        => 120,
                'scheduled_at'    => $now,
            ],
            [
                'patient_id'      => $pacientes->get(7)?->id ?? $pacientes->random()->id,
                'professional_id' => $employees->get(4)?->id ?? $employees->random()->id,
                'procedure_id'    => $procedures->get(7)?->id ?? $procedures->random()->id,
                'date'            => Carbon::today()->addDays(10)->toDateString(),
                'time'            => '13:00',
                'return'          => true,
                'obs'             => 'Retorno pós-tratamento de canal',
                'status'          => 'scheduled',
                'duration'        => 30,
                'scheduled_at'    => $now,
            ],
        ];

        foreach ($schedulings as $schedulingData) {
            Scheduling::create($schedulingData);
        }

        $this->command->info('SchedulingSeeder: ' . count($schedulings) . ' agendamentos inseridos com sucesso.');
    }
}
