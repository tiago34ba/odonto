<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FluxoCaixa;
use App\Models\ContasPagar;
use App\Models\ContasReceber;
use Carbon\Carbon;

class FluxoCaixaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Movimentações manuais de entrada
        $entradasManuais = [
            [
                'tipo' => 'Entrada',
                'categoria' => 'Receitas de Consultas',
                'descricao' => 'Consulta de Emergência - Pagamento em Dinheiro',
                'valor' => 150.00,
                'data_movimento' => Carbon::now()->subDays(45),
                'forma_pagamento' => 'Dinheiro',
                'observacoes' => 'Consulta de emergência realizada fora do horário normal'
            ],
            [
                'tipo' => 'Entrada',
                'categoria' => 'Receitas de Procedimentos',
                'descricao' => 'Limpeza Dental - Pagamento à Vista',
                'valor' => 80.00,
                'data_movimento' => Carbon::now()->subDays(40),
                'forma_pagamento' => 'PIX',
                'observacoes' => 'Procedimento de limpeza com desconto à vista'
            ],
            [
                'tipo' => 'Entrada',
                'categoria' => 'Receitas Diversas',
                'descricao' => 'Venda de Produtos de Higiene Bucal',
                'valor' => 45.50,
                'data_movimento' => Carbon::now()->subDays(35),
                'forma_pagamento' => 'Dinheiro',
                'observacoes' => 'Venda de escova e creme dental especiais'
            ],
            [
                'tipo' => 'Entrada',
                'categoria' => 'Receitas de Consultas',
                'descricao' => 'Avaliação Ortodôntica - Dinheiro',
                'valor' => 120.00,
                'data_movimento' => Carbon::now()->subDays(30),
                'forma_pagamento' => 'Dinheiro',
                'observacoes' => 'Primeira consulta para avaliação de aparelho ortodôntico'
            ],
            [
                'tipo' => 'Entrada',
                'categoria' => 'Receitas de Procedimentos',
                'descricao' => 'Restauração Dental - PIX',
                'valor' => 200.00,
                'data_movimento' => Carbon::now()->subDays(25),
                'forma_pagamento' => 'PIX',
                'observacoes' => 'Restauração em resina composta, dente 16'
            ]
        ];

        // Movimentações manuais de saída
        $saidasManuais = [
            [
                'tipo' => 'Saída',
                'categoria' => 'Materiais Odontológicos',
                'descricao' => 'Compra de Anestésicos e Agulhas',
                'valor' => 280.50,
                'data_movimento' => Carbon::now()->subDays(42),
                'forma_pagamento' => 'PIX',
                'observacoes' => 'Estoque de anestésicos lidocaína e mepivacaína'
            ],
            [
                'tipo' => 'Saída',
                'categoria' => 'Equipamentos',
                'descricao' => 'Manutenção do Compressor',
                'valor' => 150.00,
                'data_movimento' => Carbon::now()->subDays(38),
                'forma_pagamento' => 'Cartão de Crédito',
                'observacoes' => 'Revisão preventiva e troca de filtros'
            ],
            [
                'tipo' => 'Saída',
                'categoria' => 'Despesas Operacionais',
                'descricao' => 'Conta de Energia Elétrica',
                'valor' => 320.75,
                'data_movimento' => Carbon::now()->subDays(33),
                'forma_pagamento' => 'Débito Automático',
                'observacoes' => 'Consumo mensal do consultório'
            ],
            [
                'tipo' => 'Saída',
                'categoria' => 'Materiais de Consumo',
                'descricao' => 'Luvas, Máscaras e Aventais',
                'valor' => 125.30,
                'data_movimento' => Carbon::now()->subDays(28),
                'forma_pagamento' => 'Cartão de Crédito',
                'observacoes' => 'EPIs para equipe e proteção de pacientes'
            ]
        ];

        // Criar movimentações manuais
        foreach ($entradasManuais as $entrada) {
            FluxoCaixa::create($entrada);
        }

        foreach ($saidasManuais as $saida) {
            FluxoCaixa::create($saida);
        }

        // Criar movimentações automáticas baseadas em contas pagas
        $contasPagas = ContasPagar::where('status', 'Pago')->get();
        foreach ($contasPagas as $conta) {
            FluxoCaixa::create([
                'conta_pagar_id' => $conta->id,
                'tipo' => 'Saída',
                'categoria' => 'Contas a Pagar',
                'descricao' => 'Pagamento: ' . $conta->descricao,
                'valor' => $conta->valor_pago,
                'data_movimento' => $conta->data_pagamento,
                'forma_pagamento' => $conta->forma_pagamento ?? 'Não informado',
                'observacoes' => 'Movimentação automática - Conta ID: ' . $conta->codigo
            ]);
        }

        // Criar movimentações automáticas baseadas em contas recebidas
        $contasRecebidas = ContasReceber::where('status', 'Recebido')->get();
        foreach ($contasRecebidas as $conta) {
            FluxoCaixa::create([
                'conta_receber_id' => $conta->id,
                'tipo' => 'Entrada',
                'categoria' => 'Contas a Receber',
                'descricao' => 'Recebimento: ' . $conta->categoria,
                'valor' => $conta->valor_recebido,
                'data_movimento' => $conta->data_recebimento,
                'forma_pagamento' => $conta->forma_pagamento ?? 'Não informado',
                'observacoes' => 'Movimentação automática - Conta ID: ' . $conta->codigo
            ]);
        }

        // Movimentações futuras (simulação para próximos dias)
        /*$movimentacoesFuturas = [
            [
                'tipo' => 'entrada',
                'categoria' => 'Receitas de Consultas',
                'descricao' => 'Consulta Agendada - Dr. Silva',
                'valor' => 90.00,
                'data_movimentacao' => Carbon::now()->addDays(2),
                'observacoes' => 'Consulta de rotina agendada'
            ],
            [
                'tipo' => 'saida',
                'categoria' => 'Despesas Operacionais',
                'descricao' => 'Aluguel do Consultório',
                'valor' => 1200.00,
                'data_movimentacao' => Carbon::now()->addDays(5),
                'observacoes' => 'Aluguel mensal do espaço'
            ],
            [
                'tipo' => 'entrada',
                'categoria' => 'Receitas de Procedimentos',
                'descricao' => 'Clareamento Dental Programado',
                'valor' => 380.00,
                'data_movimentacao' => Carbon::now()->addDays(7),
                'observacoes' => 'Sessão de clareamento dental a laser'
            ],
            [
                'tipo' => 'saida',
                'categoria' => 'Impostos',
                'descricao' => 'ISS sobre Serviços',
                'valor' => 125.40,
                'data_movimentacao' => Carbon::now()->addDays(10),
                'observacoes' => 'Imposto sobre serviços do mês anterior'
            ]
        ];

        foreach ($movimentacoesFuturas as $futura) {
            FluxoCaixa::create($futura);
        }*/

        echo "FluxoCaixaSeeder: " . (count($entradasManuais) + count($saidasManuais) + $contasPagas->count() + $contasRecebidas->count()) . " movimentações criadas.\n";
    }
}
