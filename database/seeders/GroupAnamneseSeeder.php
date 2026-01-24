<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\GroupAnamnese;

class GroupAnamneseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            [
                'nome' => 'Cardiovascular',
                'descricao' => 'Doenças e condições relacionadas ao sistema cardiovascular',
            ],
            [
                'nome' => 'Endócrino',
                'descricao' => 'Doenças endócrinas como diabetes, problemas da tireoide, etc.',
            ],
            [
                'nome' => 'Alergias',
                'descricao' => 'Alergias a medicamentos, alimentos, materiais odontológicos',
            ],
            [
                'nome' => 'Neurológico',
                'descricao' => 'Condições neurológicas como epilepsia, convulsões, etc.',
            ],
            [
                'nome' => 'Hematológico',
                'descricao' => 'Problemas relacionados ao sangue e coagulação',
            ],
            [
                'nome' => 'Medicamentos',
                'descricao' => 'Uso de medicamentos que podem interferir no tratamento',
            ],
            [
                'nome' => 'Ginecológico',
                'descricao' => 'Condições específicas do sistema reprodutor feminino',
            ],
            [
                'nome' => 'Respiratório',
                'descricao' => 'Problemas respiratórios como asma, bronquite, etc.',
            ],
            [
                'nome' => 'Hábitos',
                'descricao' => 'Hábitos que podem afetar a saúde bucal',
            ],
            [
                'nome' => 'Outras Condições',
                'descricao' => 'Outras condições médicas relevantes',
            ],
        ];

        foreach ($groups as $groupData) {
            GroupAnamnese::create($groupData);
        }
    }
}
