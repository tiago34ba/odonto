<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FormaPagamento;

class FormaPagamentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $formasPagamento = [
            [
                'nome' => 'Dinheiro',
                'taxa' => 0.00,
            ],
            [
                'nome' => 'PIX',
                'taxa' => 0.00,
            ],
            [
                'nome' => 'Cartão de Débito',
                'taxa' => 1.50,
            ],
            [
                'nome' => 'Cartão de Crédito à Vista',
                'taxa' => 3.49,
            ],
            [
                'nome' => 'Cartão de Crédito 2x',
                'taxa' => 4.99,
            ],
            [
                'nome' => 'Cartão de Crédito 3x',
                'taxa' => 6.49,
            ],
            [
                'nome' => 'Cartão de Crédito 4x',
                'taxa' => 7.99,
            ],
            [
                'nome' => 'Cartão de Crédito 5x',
                'taxa' => 9.49,
            ],
            [
                'nome' => 'Cartão de Crédito 6x',
                'taxa' => 10.99,
            ],
            [
                'nome' => 'Convênio/Plano de Saúde',
                'taxa' => 5.00,
            ],
            [
                'nome' => 'Boleto Bancário',
                'taxa' => 2.50,
            ],
            [
                'nome' => 'Transferência Bancária',
                'taxa' => 0.50,
            ],
        ];

        foreach ($formasPagamento as $formaPagamento) {
            FormaPagamento::create($formaPagamento);
        }
    }
}
