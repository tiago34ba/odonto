<?php

namespace App\Services;

use App\Models\Funcionario;
use App\Models\Agendamento;

class FuncionarioService
{
    /**
     * Cadastra um novo funcionário com validações e regras de negócio.
     */
    public function cadastrar(array $dados)
    {
        // Validação de dados obrigatórios
        $required = ['nome', 'cpf', 'email', 'registro_profissional'];
        foreach ($required as $field) {
            if (empty($dados[$field])) {
                throw new \InvalidArgumentException("Campo obrigatório: $field");
            }
        }

        // Verificação de duplicidade (CPF, e-mail, registro profissional)
        if (Funcionario::where('cpf', $dados['cpf'])->exists()) {
            throw new \Exception('Já existe um funcionário com este CPF.');
        }
        if (Funcionario::where('email', $dados['email'])->exists()) {
            throw new \Exception('Já existe um funcionário com este e-mail.');
        }
        if (Funcionario::where('registro_profissional', $dados['registro_profissional'])->exists()) {
            throw new \Exception('Já existe um funcionário com este registro profissional.');
        }

        // Criação do funcionário
        return Funcionario::create([
            'nome' => $dados['nome'],
            'cpf' => $dados['cpf'],
            'email' => $dados['email'],
            'registro_profissional' => $dados['registro_profissional'],
            'telefone' => $dados['telefone'] ?? null,
            'cargo' => $dados['cargo'] ?? null,
            'ativo' => true,
            'observacao' => $dados['observacao'] ?? null,
        ]);
    }

    /**
     * Atualiza dados do funcionário.
     */
    public function atualizar($funcionarioId, array $dados)
    {
        $funcionario = Funcionario::findOrFail($funcionarioId);

        // Validação de duplicidade
        if (isset($dados['cpf']) && Funcionario::where('cpf', $dados['cpf'])->where('id', '!=', $funcionarioId)->exists()) {
            throw new \Exception('Já existe um funcionário com este CPF.');
        }
        if (isset($dados['email']) && Funcionario::where('email', $dados['email'])->where('id', '!=', $funcionarioId)->exists()) {
            throw new \Exception('Já existe um funcionário com este e-mail.');
        }
        if (isset($dados['registro_profissional']) && Funcionario::where('registro_profissional', $dados['registro_profissional'])->where('id', '!=', $funcionarioId)->exists()) {
            throw new \Exception('Já existe um funcionário com este registro profissional.');
        }

        $funcionario->fill($dados);
        $funcionario->save();
        return $funcionario;
    }

    /**
     * Inativa funcionário, considerando vínculos (agendamentos futuros).
     */
    public function inativar($funcionarioId)
    {
        $funcionario = Funcionario::findOrFail($funcionarioId);

        // Verificar vínculos com agendamentos futuros
        $agendamentosFuturos = Agendamento::where('funcionario_id', $funcionarioId)
            ->where('data', '>=', now()->toDateString())
            ->count();

        if ($agendamentosFuturos > 0) {
            throw new \Exception('Não é possível inativar: funcionário possui agendamentos futuros.');
        }

        $funcionario->ativo = false;
        $funcionario->save();
        return $funcionario;
    }
}
