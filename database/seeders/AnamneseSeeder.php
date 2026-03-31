<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Anamnese;

class AnamneseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $anamneses = [
            [
                'name' => 'Histórico de Hipertensão',
                'group' => 'Cardiovascular',
                'description' => 'Paciente possui histórico de pressão alta',
            ],
            [
                'name' => 'Diabetes',
                'group' => 'Endócrino',
                'description' => 'Paciente possui diabetes mellitus',
            ],
            [
                'name' => 'Alergia a Medicamentos',
                'group' => 'Alergias',
                'description' => 'Paciente possui alergia a determinados medicamentos',
            ],
            [
                'name' => 'Gravidez',
                'group' => 'Ginecológico',
                'description' => 'Paciente está grávida ou suspeita de gravidez',
            ],
            [
                'name' => 'Uso de Anticoagulantes',
                'group' => 'Medicamentos',
                'description' => 'Paciente faz uso de medicamentos anticoagulantes',
            ],
            [
                'name' => 'Histórico de Convulsões',
                'group' => 'Neurológico',
                'description' => 'Paciente possui histórico de convulsões ou epilepsia',
            ],
            [
                'name' => 'Problemas Cardíacos',
                'group' => 'Cardiovascular',
                'description' => 'Paciente possui problemas cardíacos diversos',
            ],
            [
                'name' => 'Alergia a Anestésicos',
                'group' => 'Alergias',
                'description' => 'Paciente possui alergia a anestésicos locais',
            ],
            [
                'name' => 'Sangramento Excessivo',
                'group' => 'Hematológico',
                'description' => 'Paciente tem tendência a sangramento excessivo',
            ],
            [
                'name' => 'Uso de Marca-passo',
                'group' => 'Cardiovascular',
                'description' => 'Paciente possui marca-passo implantado',
            ],
        ];

        foreach ($anamneses as $anamneseData) {
            Anamnese::create($anamneseData);
        }
    }
}
