<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
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
        // Buscar alguns pacientes, funcionários e procedimentos
        $pacientes = Paciente::limit(10)->get();
        $employees = Employee::limit(3)->get();
        $procedures = Procedure::limit(5)->get();

        if ($pacientes->count() > 0 && $employees->count() > 0 && $procedures->count() > 0) {
            $schedulings = [
                [
                    'patient_id' => $pacientes->random()->id,
                    'professional_id' => $employees->random()->id,
                    'procedure_id' => $procedures->random()->id,
                    'date' => Carbon::today()->addDays(1),
                    'time' => '09:00',
                    'return' => false,
                    'obs' => 'Primeira consulta do paciente',
                    'status' => 'agendado',
                    'duration' => 60,
                    'scheduled_at' => now(),
                ],
                [
                    'patient_id' => $pacientes->random()->id,
                    'professional_id' => $employees->random()->id,
                    'procedure_id' => $procedures->random()->id,
                    'date' => Carbon::today()->addDays(2),
                    'time' => '14:30',
                    'return' => false,
                    'obs' => 'Paciente com histórico de alergia',
                    'status' => 'confirmado',
                    'duration' => 45,
                    'scheduled_at' => now(),
                    'confirmed_at' => now(),
                ],
                [
                    'patient_id' => $pacientes->random()->id,
                    'professional_id' => $employees->random()->id,
                    'procedure_id' => $procedures->random()->id,
                    'date' => Carbon::today()->addDays(3),
                    'time' => '10:15',
                    'return' => true,
                    'obs' => 'Retorno para avaliação do procedimento',
                    'status' => 'agendado',
                    'duration' => 30,
                    'scheduled_at' => now(),
                ],
                [
                    'patient_id' => $pacientes->random()->id,
                    'professional_id' => $employees->random()->id,
                    'procedure_id' => $procedures->random()->id,
                    'date' => Carbon::yesterday(),
                    'time' => '16:00',
                    'return' => false,
                    'obs' => 'Procedimento urgente',
                    'status' => 'realizado',
                    'duration' => 90,
                    'scheduled_at' => Carbon::yesterday()->subDay(),
                ],
                [
                    'patient_id' => $pacientes->random()->id,
                    'professional_id' => $employees->random()->id,
                    'procedure_id' => $procedures->random()->id,
                    'date' => Carbon::today()->addWeek(),
                    'time' => '08:30',
                    'return' => false,
                    'obs' => 'Procedimento complexo - atenção especial',
                    'status' => 'agendado',
                    'duration' => 120,
                    'scheduled_at' => now(),
                ],
            ];

            foreach ($schedulings as $schedulingData) {
                Scheduling::create($schedulingData);
            }
        }
    }
}
