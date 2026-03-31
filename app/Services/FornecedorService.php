<?php

namespace App\Services;

use App\Models\Fornecedor;
use App\Models\Compra;
use App\Models\ContaPagar;

class FornecedorService
{
    /**
     * Cadastra um novo fornecedor com validações e regras de negócio.
     */
    public function cadastrar(array $dados)
    {
        // Validação de dados obrigatórios
        $required = ['nome', 'cnpj', 'email'];
        foreach ($required as $field) {
            if (empty($dados[$field])) {
                throw new \InvalidArgumentException("Campo obrigatório: $field");
            }
        }

        // Verificação de duplicidade (CNPJ, e-mail)
        if (Fornecedor::where('cnpj', $dados['cnpj'])->exists()) {
            throw new \Exception('Já existe um fornecedor com este CNPJ.');
        }
        if (Fornecedor::where('email', $dados['email'])->exists()) {
            throw new \Exception('Já existe um fornecedor com este e-mail.');
        }

        // Criação do fornecedor
        return Fornecedor::create([
            'nome' => $dados['nome'],
            'cnpj' => $dados['cnpj'],
            'email' => $dados['email'],
            'telefone' => $dados['telefone'] ?? null,
            'endereco' => $dados['endereco'] ?? null,
            'ativo' => true,
            'observacao' => $dados['observacao'] ?? null,
        ]);
    }

    /**
     * Atualiza dados do fornecedor.
     */
    public function atualizar($fornecedorId, array $dados)
    {
        $fornecedor = Fornecedor::findOrFail($fornecedorId);

        // Validação de CNPJ e e-mail únicos
        if (isset($dados['cnpj']) && Fornecedor::where('cnpj', $dados['cnpj'])->where('id', '!=', $fornecedorId)->exists()) {
            throw new \Exception('Já existe um fornecedor com este CNPJ.');
        }
        if (isset($dados['email']) && Fornecedor::where('email', $dados['email'])->where('id', '!=', $fornecedorId)->exists()) {
            throw new \Exception('Já existe um fornecedor com este e-mail.');
        }

        $fornecedor->fill($dados);
        $fornecedor->save();
        return $fornecedor;
    }

    /**
     * Inativa fornecedor, considerando vínculos (compras, contas a pagar).
     */
    public function inativar($fornecedorId)
    {
        $fornecedor = Fornecedor::findOrFail($fornecedorId);

        // Verificar vínculos com compras
        $comprasVinculadas = Compra::where('fornecedor_id', $fornecedorId)->count();
        if ($comprasVinculadas > 0) {
            throw new \Exception('Não é possível inativar: fornecedor vinculado a compras.');
        }

        // Verificar vínculos com contas a pagar
        $contasVinculadas = ContaPagar::where('fornecedor_id', $fornecedorId)->count();
        if ($contasVinculadas > 0) {
            throw new \Exception('Não é possível inativar: fornecedor vinculado a contas a pagar.');
        }

        $fornecedor->ativo = false;
        $fornecedor->save();
        return $fornecedor;
    }
}
