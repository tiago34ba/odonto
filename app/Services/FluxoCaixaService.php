<?php


namespace App\Services;

use App\Models\FluxoCaixa;

class FluxoCaixaService
{
    public function cadastrar(array $dados)
    {
        // Validação de campos obrigatórios
        $required = ['tipo', 'valor', 'data_movimento'];
        foreach ($required as $field) {
            if (empty($dados[$field])) {
                throw new \InvalidArgumentException("Campo obrigatório: $field");
            }
        }

        // Regra: valor não pode ser negativo
        if ($dados['valor'] < 0) {
            throw new \Exception('O valor da movimentação não pode ser negativo.');
        }

        // Regra: tipo deve ser 'entrada' ou 'saida'
        if (!in_array($dados['tipo'], ['entrada', 'saida'])) {
            throw new \Exception('Tipo de movimentação inválido.');
        }

        // Criação da movimentação
        return FluxoCaixa::create($dados);
    }
}
