<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ContasReceber;
use App\Models\Paciente;
use App\Models\Procedure;
use App\Models\Scheduling;
use Carbon\Carbon;

class ContasReceberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar dados relacionados
        $pacientes = Paciente::all();
        $procedures = Procedure::all();
        $schedulings = Scheduling::all();
        
        if ($pacientes->isEmpty()) {
            echo "ContasReceberSeeder: Nenhum paciente encontrado. Execute PacienteSeeder primeiro.\n";
            return;
        }

        $contasReceber = [
            // Contas recebidas (histórico)
            [
                'paciente_id' => $pacientes->random()->id,
                'procedure_id' => $procedures->isNotEmpty() ? $procedures->random()->id : null,
                'scheduling_id' => $schedulings->isNotEmpty() ? $schedulings->random()->id : null,
                'categoria' => 'Consulta',
                'valor_original' => 120.00,
                'valor_recebido' => 120.00,
                'valor_pendente' => 0.00,
                'data_vencimento' => Carbon::now()->subDays(50),
                'data_recebimento' => Carbon::now()->subDays(50),
                'status' => 'Recebido',
                'forma_pagamento' => 'Dinheiro',
                'observacoes' => 'Consulta de rotina - Dr. João Silva - Pagamento realizado na data da consulta'
            ],
            [
                'paciente_id' => $pacientes->random()->id,
                'procedure_id' => $procedures->isNotEmpty() ? $procedures->random()->id : null,
                'categoria' => 'Limpeza',
                'valor_original' => 80.00,
                'valor_recebido' => 76.00,
                'valor_pendente' => 0.00,
                'data_vencimento' => Carbon::now()->subDays(45),
                'data_recebimento' => Carbon::now()->subDays(45),
                'status' => 'Recebido',
                'forma_pagamento' => 'PIX',
                'observacoes' => 'Limpeza dental completa - Desconto de 5% para pagamento à vista'
            ],
            [
                'paciente_id' => $pacientes->random()->id,
                'procedure_id' => $procedures->isNotEmpty() ? $procedures->random()->id : null,
                'categoria' => 'Restauração',
                'valor_original' => 250.00,
                'valor_recebido' => 250.00,
                'valor_pendente' => 0.00,
                'data_vencimento' => Carbon::now()->subDays(40),
                'data_recebimento' => Carbon::now()->subDays(38),
                'status' => 'Recebido',
                'forma_pagamento' => 'Cartão de Crédito',
                'observacoes' => 'Restauração dental - Dente 16 - Restauração em resina composta'
            ],
            [
                'paciente_id' => $pacientes->random()->id,
                'procedure_id' => $procedures->isNotEmpty() ? $procedures->random()->id : null,
                'categoria' => 'Cirurgia',
                'valor_original' => 400.00,
                'valor_recebido' => 400.00,
                'valor_pendente' => 0.00,
                'data_vencimento' => Carbon::now()->subDays(35),
                'data_recebimento' => Carbon::now()->subDays(33),
                'status' => 'Recebido',
                'forma_pagamento' => 'Transferência Bancária',
                'observacoes' => 'Extração de siso - Cirurgia de terceiro molar inferior esquerdo'
            ],
            [
                'paciente_id' => $pacientes->random()->id,
                'procedure_id' => $procedures->isNotEmpty() ? $procedures->random()->id : null,
                'categoria' => 'Clareamento',
                'valor_original' => 350.00,
                'valor_recebido' => 350.00,
                'valor_pendente' => 0.00,
                'data_vencimento' => Carbon::now()->subDays(30),
                'data_recebimento' => Carbon::now()->subDays(30),
                'status' => 'Recebido',
                'forma_pagamento' => 'PIX',
                'observacoes' => 'Clareamento dental a laser - Sessão de clareamento profissional'
            ],
            [
                'paciente_id' => $pacientes->random()->id,
                'procedure_id' => $procedures->isNotEmpty() ? $procedures->random()->id : null,
                'categoria' => 'Consulta',
                'valor_original' => 150.00,
                'valor_recebido' => 150.00,
                'valor_pendente' => 0.00,
                'data_vencimento' => Carbon::now()->subDays(25),
                'data_recebimento' => Carbon::now()->subDays(25),
                'status' => 'Recebido',
                'forma_pagamento' => 'Cartão de Débito',
                'observacoes' => 'Consulta ortodôntica - Avaliação para colocação de aparelho ortodôntico'
            ],

            // Contas pendentes (a vencer)
            [
                'paciente_id' => $pacientes->random()->id,
                'procedure_id' => $procedures->isNotEmpty() ? $procedures->random()->id : null,
                'scheduling_id' => $schedulings->isNotEmpty() ? $schedulings->random()->id : null,
                'categoria' => 'Endodontia',
                'valor_original' => 450.00,
                'valor_pendente' => 450.00,
                'data_vencimento' => Carbon::now()->addDays(5),
                'status' => 'Pendente',
                'observacoes' => 'Tratamento de canal - Sessão 2/3 - Segunda sessão do tratamento endodôntico'
            ],
            [
                'paciente_id' => $pacientes->random()->id,
                'procedure_id' => $procedures->isNotEmpty() ? $procedures->random()->id : null,
                'categoria' => 'Prótese',
                'valor_original' => 800.00,
                'valor_pendente' => 800.00,
                'data_vencimento' => Carbon::now()->addDays(10),
                'status' => 'Pendente',
                'observacoes' => 'Prótese dentária - Moldagem - Primeira etapa da confecção de prótese total'
            ],
            [
                'paciente_id' => $pacientes->random()->id,
                'procedure_id' => $procedures->isNotEmpty() ? $procedures->random()->id : null,
                'categoria' => 'Implante',
                'valor_original' => 1200.00,
                'valor_pendente' => 1200.00,
                'data_vencimento' => Carbon::now()->addDays(15),
                'status' => 'Pendente',
                'observacoes' => 'Implante dentário - Implante unitário com coroa cerâmica'
            ],
            [
                'paciente_id' => $pacientes->random()->id,
                'procedure_id' => $procedures->isNotEmpty() ? $procedures->random()->id : null,
                'categoria' => 'Ortodontia',
                'valor_original' => 180.00,
                'valor_pendente' => 180.00,
                'data_vencimento' => Carbon::now()->addDays(20),
                'status' => 'Pendente',
                'observacoes' => 'Manutenção de aparelho ortodôntico - Ajuste mensal do aparelho fixo'
            ],

            // Contas vencidas
            [
                'paciente_id' => $pacientes->random()->id,
                'procedure_id' => $procedures->isNotEmpty() ? $procedures->random()->id : null,
                'categoria' => 'Consulta',
                'valor_original' => 180.00,
                'valor_pendente' => 180.00,
                'data_vencimento' => Carbon::now()->subDays(5),
                'status' => 'Vencido',
                'observacoes' => 'Consulta de emergência - Atendimento de emergência fora do horário'
            ],
            [
                'paciente_id' => $pacientes->random()->id,
                'procedure_id' => $procedures->isNotEmpty() ? $procedures->random()->id : null,
                'categoria' => 'Outros',
                'valor_original' => 60.00,
                'valor_pendente' => 60.00,
                'data_vencimento' => Carbon::now()->subDays(8),
                'status' => 'Vencido',
                'observacoes' => 'Radiografia panorâmica - Exame radiográfico para diagnóstico'
            ],
            [
                'paciente_id' => $pacientes->random()->id,
                'procedure_id' => $procedures->isNotEmpty() ? $procedures->random()->id : null,
                'categoria' => 'Limpeza',
                'valor_original' => 40.00,
                'valor_pendente' => 40.00,
                'data_vencimento' => Carbon::now()->subDays(12),
                'status' => 'Vencido',
                'observacoes' => 'Aplicação de flúor - Aplicação tópica de flúor'
            ],

            // Tratamentos parcelados
            [
                'paciente_id' => $pacientes->random()->id,
                'procedure_id' => $procedures->isNotEmpty() ? $procedures->random()->id : null,
                'categoria' => 'Ortodontia',
                'valor_original' => 320.00,
                'valor_pendente' => 320.00,
                'data_vencimento' => Carbon::now()->addDays(3),
                'status' => 'Pendente',
                'observacoes' => 'Tratamento ortodôntico - Parcela 1/24 - Primeira parcela do tratamento ortodôntico completo'
            ],
            [
                'paciente_id' => $pacientes->random()->id,
                'procedure_id' => $procedures->isNotEmpty() ? $procedures->random()->id : null,
                'categoria' => 'Ortodontia',
                'valor_original' => 320.00,
                'valor_pendente' => 320.00,
                'data_vencimento' => Carbon::now()->addDays(33),
                'status' => 'Pendente',
                'observacoes' => 'Tratamento ortodôntico - Parcela 2/24 - Segunda parcela do tratamento ortodôntico completo'
            ],
            [
                'paciente_id' => $pacientes->random()->id,
                'procedure_id' => $procedures->isNotEmpty() ? $procedures->random()->id : null,
                'categoria' => 'Prótese',
                'valor_original' => 600.00,
                'valor_pendente' => 600.00,
                'data_vencimento' => Carbon::now()->addDays(7),
                'status' => 'Pendente',
                'observacoes' => 'Prótese total - Parcela 1/4 - Primeira parcela da prótese total superior'
            ],
            [
                'paciente_id' => $pacientes->random()->id,
                'procedure_id' => $procedures->isNotEmpty() ? $procedures->random()->id : null,
                'categoria' => 'Prótese',
                'valor_original' => 600.00,
                'valor_pendente' => 600.00,
                'data_vencimento' => Carbon::now()->addDays(37),
                'status' => 'Pendente',
                'observacoes' => 'Prótese total - Parcela 2/4 - Segunda parcela da prótese total superior'
            ]
        ];

        foreach ($contasReceber as $conta) {
            ContasReceber::create($conta);
        }

        echo "ContasReceberSeeder: " . count($contasReceber) . " contas a receber criadas.\n";
    }
}
