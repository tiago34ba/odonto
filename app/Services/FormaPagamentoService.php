<?php

namespace App\Services;

use App\Models\FormaPagamento;
use App\Models\Financeiro;
use Illuminate\Support\Facades\DB;

class FormaPagamentoService
{
    /**
     * Cadastra uma nova forma de pagamento com validações e regras de negócio.
     */
    public function cadastrar(array $dados)
    {
        // Validação de dados obrigatórios
        $required = ['nome', 'tipo'];
        foreach ($required as $field) {
            if (empty($dados[$field])) {
                throw new \InvalidArgumentException("Campo obrigatório: $field");
            }
        }

        // Verificação de duplicidade (nome)
        if (FormaPagamento::where('nome', $dados['nome'])->exists()) {
            throw new \Exception('Já existe uma forma de pagamento com este nome.');
        }

        // Criação da forma de pagamento
        return FormaPagamento::create([
            'nome' => $dados['nome'],
            'tipo' => $dados['tipo'], // Ex: 'dinheiro', 'cartao', 'boleto', etc.
            'ativo' => true,
            'descricao' => $dados['descricao'] ?? null,
        ]);
    }

    /**
     * Atualiza dados da forma de pagamento.
     */
    public function atualizar($formaId, array $dados)
    {
        $forma = FormaPagamento::findOrFail($formaId);

        // Validação de nome único
        if (isset($dados['nome']) && FormaPagamento::where('nome', $dados['nome'])->where('id', '!=', $formaId)->exists()) {
            throw new \Exception('Já existe uma forma de pagamento com este nome.');
        }

        $forma->fill($dados);
        $forma->save();
        return $forma;
    }

    /**
     * Inativa forma de pagamento, considerando vínculos (financeiro).
     */
    public function inativar($formaId)
    {
        $forma = FormaPagamento::findOrFail($formaId);

        // Verificar vínculos com lançamentos financeiros
        $vinculos = Financeiro::where('forma_pagamento_id', $formaId)->count();
        if ($vinculos > 0) {
            throw new \Exception('Não é possível inativar: forma de pagamento vinculada a lançamentos financeiros.');
        }

        $forma->ativo = false;
        $forma->save();
        return $forma;
    }
}
