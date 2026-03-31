<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ContasPagar;
use App\Models\Supplier;
use Carbon\Carbon;

class ContasPagarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar fornecedores existentes
        $suppliers = Supplier::all();
        
        if ($suppliers->isEmpty()) {
            echo "ContasPagarSeeder: Nenhum fornecedor encontrado. Execute SupplierSeeder primeiro.\n";
            return;
        }

        $contasPagar = [
            // Contas pagas (histórico)
            [
                'supplier_id' => $suppliers->random()->id,
                'descricao' => 'Compra de materiais odontológicos - Anestésicos',
                'categoria' => 'Materiais',
                'valor_original' => 450.00,
                'valor_pago' => 450.00,
                'valor_pendente' => 0.00,
                'data_vencimento' => Carbon::now()->subDays(45),
                'data_pagamento' => Carbon::now()->subDays(43),
                'status' => 'Pago',
                'forma_pagamento' => 'PIX',
                'observacoes' => 'Pagamento realizado com 2 dias de antecedência'
            ],
            [
                'supplier_id' => $suppliers->random()->id,
                'descricao' => 'Aluguel do consultório - Outubro/2024',
                'categoria' => 'Infraestrutura',
                'valor_original' => 2500.00,
                'valor_pago' => 2500.00,
                'valor_pendente' => 0.00,
                'data_vencimento' => Carbon::now()->subDays(40),
                'data_pagamento' => Carbon::now()->subDays(40),
                'status' => 'Pago',
                'forma_pagamento' => 'Transferência Bancária',
                'observacoes' => 'Pagamento pontual do aluguel mensal'
            ],
            [
                'supplier_id' => $suppliers->random()->id,
                'descricao' => 'Conta de energia elétrica - Outubro/2024',
                'categoria' => 'Utilidades',
                'valor_original' => 380.75,
                'valor_pago' => 380.75,
                'valor_pendente' => 0.00,
                'data_vencimento' => Carbon::now()->subDays(35),
                'data_pagamento' => Carbon::now()->subDays(32),
                'status' => 'Pago',
                'forma_pagamento' => 'Débito Automático',
                'observacoes' => 'Débito automático processado com sucesso'
            ],

            // Contas pendentes (vencimento futuro)
            [
                'supplier_id' => $suppliers->random()->id,
                'descricao' => 'Aluguel do consultório - Novembro/2024',
                'categoria' => 'Infraestrutura',
                'valor_original' => 2500.00,
                'valor_pendente' => 2500.00,
                'data_vencimento' => Carbon::now()->addDays(5),
                'status' => 'Pendente',
                'observacoes' => 'Aluguel mensal a vencer'
            ],
            [
                'supplier_id' => $suppliers->random()->id,
                'descricao' => 'Reposição de estoque - Resinas compostas',
                'categoria' => 'Materiais',
                'valor_original' => 650.00,
                'valor_pendente' => 650.00,
                'data_vencimento' => Carbon::now()->addDays(10),
                'status' => 'Pendente',
                'observacoes' => 'Reposição de resinas cores A1, A2 e A3'
            ],

            // Contas vencidas
            [
                'supplier_id' => $suppliers->random()->id,
                'descricao' => 'Taxa de licença do software',
                'categoria' => 'Software',
                'valor_original' => 180.00,
                'valor_pendente' => 180.00,
                'data_vencimento' => Carbon::now()->subDays(5),
                'status' => 'Vencido',
                'observacoes' => 'Licença anual do software de gestão'
            ],
            [
                'supplier_id' => $suppliers->random()->id,
                'descricao' => 'Material descartável - Luvas e máscaras',
                'categoria' => 'Materiais',
                'valor_original' => 320.75,
                'valor_pendente' => 320.75,
                'data_vencimento' => Carbon::now()->subDays(3),
                'status' => 'Vencido',
                'observacoes' => 'Estoque de EPIs para o mês'
            ]
        ];

        foreach ($contasPagar as $conta) {
            ContasPagar::create($conta);
        }

        echo "ContasPagarSeeder: " . count($contasPagar) . " contas a pagar criadas.\n";
    }
}
