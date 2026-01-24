<?php

namespace App\Services;

use App\Models\Cargo;
use App\Models\Funcionario;

class CargoService
{
    /**
     * Cadastra um novo cargo com validações e regras de negócio.
     */
    public function cadastrar(array $dados)
    {
        // Validação de dados obrigatórios
        $required = ['nome', 'nivel_acesso'];
        foreach ($required as $field) {
            if (empty($dados[$field])) {
                throw new \InvalidArgumentException("Campo obrigatório: $field");
            }
        }

        // Níveis de acesso permitidos
        $niveisValidos = ['administrativo', 'clinico', 'financeiro', 'gerencial'];
        if (!in_array($dados['nivel_acesso'], $niveisValidos)) {
            throw new \InvalidArgumentException('Nível de acesso inválido.');
        }

        // Verificação de duplicidade (nome)
        if (Cargo::where('nome', $dados['nome'])->exists()) {
            throw new \Exception('Já existe um cargo com este nome.');
        }

        // Criação do cargo
        return Cargo::create([
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'] ?? null,
            'nivel_acesso' => $dados['nivel_acesso'],
            'ativo' => true
        ]);
    }

    /**
     * Atualiza dados do cargo.
     */
    public function atualizar($cargoId, array $dados)
    {
        $cargo = Cargo::findOrFail($cargoId);

        // Validação de nome único
        if (isset($dados['nome']) && Cargo::where('nome', $dados['nome'])->where('id', '!=', $cargoId)->exists()) {
            throw new \Exception('Já existe um cargo com este nome.');
        }

        // Validação de nível de acesso
        $niveisValidos = ['administrativo', 'clinico', 'financeiro', 'gerencial'];
        if (isset($dados['nivel_acesso']) && !in_array($dados['nivel_acesso'], $niveisValidos)) {
            throw new \InvalidArgumentException('Nível de acesso inválido.');
        }

        $cargo->fill($dados);
        $cargo->save();
        return $cargo;
    }

    /**
     * Inativa cargo, considerando vínculos (funcionários).
     */
    public function inativar($cargoId)
    {
        $cargo = Cargo::findOrFail($cargoId);

        // Verificar vínculos com funcionários
        $vinculos = Funcionario::where('cargo_id', $cargoId)->count();
        if ($vinculos > 0) {
            throw new \Exception('Não é possível inativar: cargo vinculado a funcionários.');
        }

        $cargo->ativo = false;
        $cargo->save();
        return $cargo;
    }
}
