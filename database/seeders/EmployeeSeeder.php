<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = [
            [
                'name' => 'Dr. João Silva',
                'phone' => '(11) 98765-4321',
                'email' => 'joao.silva@odonto.com',
                'role' => 'Dentista',
                'photo' => null,
            ],
            [
                'name' => 'Dra. Maria Santos',
                'phone' => '(11) 98765-4322',
                'email' => 'maria.santos@odonto.com',
                'role' => 'Ortodontista',
                'photo' => null,
            ],
            [
                'name' => 'Carlos Oliveira',
                'phone' => '(11) 98765-4323',
                'email' => 'carlos.oliveira@odonto.com',
                'role' => 'Recepcionista',
                'photo' => null,
            ],
            [
                'name' => 'Ana Costa',
                'phone' => '(11) 98765-4324',
                'email' => 'ana.costa@odonto.com',
                'role' => 'Auxiliar de Enfermagem',
                'photo' => null,
            ],
            [
                'name' => 'Dr. Pedro Ferreira',
                'phone' => '(11) 98765-4325',
                'email' => 'pedro.ferreira@odonto.com',
                'role' => 'Cirurgião Dentista',
                'photo' => null,
            ],
            [
                'name' => 'Lucia Mendes',
                'phone' => '(11) 98765-4326',
                'email' => 'lucia.mendes@odonto.com',
                'role' => 'Higienista Dental',
                'photo' => null,
            ]
        ];

        foreach ($employees as $employeeData) {
            Employee::create($employeeData);
        }
    }
}
