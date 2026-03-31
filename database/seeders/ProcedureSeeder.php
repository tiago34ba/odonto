<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Procedure;

class ProcedureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $procedures = [
            [
                'name' => 'Limpeza Dental (Profilaxia)',
                'value' => 80.00,
                'time' => '45 minutos',
                'accepts_agreement' => true,
                'preparation' => 'Paciente deve estar em jejum de 2h',
            ],
            [
                'name' => 'Restauração com Resina',
                'value' => 150.00,
                'time' => '60 minutos',
                'accepts_agreement' => true,
                'preparation' => 'Nenhuma preparação especial',
            ],
            [
                'name' => 'Extração Dental Simples',
                'value' => 120.00,
                'time' => '30 minutos',
                'accepts_agreement' => true,
                'preparation' => 'Jejum de 4h, medicação pré-cirúrgica',
            ],
            [
                'name' => 'Canal Radicular (Endodontia)',
                'value' => 350.00,
                'time' => '120 minutos',
                'accepts_agreement' => true,
                'preparation' => 'Radiografia prévia obrigatória',
            ],
            [
                'name' => 'Implante Dentário',
                'value' => 1200.00,
                'time' => '180 minutos',
                'accepts_agreement' => false,
                'preparation' => 'Exames médicos completos, jejum de 8h',
            ],
            [
                'name' => 'Aparelho Ortodôntico',
                'value' => 2500.00,
                'time' => '90 minutos',
                'accepts_agreement' => true,
                'preparation' => 'Moldagem prévia dos dentes',
            ],
            [
                'name' => 'Clareamento Dental',
                'value' => 300.00,
                'time' => '60 minutos',
                'accepts_agreement' => false,
                'preparation' => 'Limpeza prévia obrigatória',
            ],
            [
                'name' => 'Prótese Total',
                'value' => 800.00,
                'time' => '90 minutos',
                'accepts_agreement' => true,
                'preparation' => 'Moldagem e planejamento prévios',
            ],
        ];

        foreach ($procedures as $procedureData) {
            Procedure::create($procedureData);
        }
    }
}
