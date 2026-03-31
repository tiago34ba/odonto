<?php

namespace App\Services;

use App\Models\Usuario;
use App\Models\Agendamento;

class UsuarioService
{
    /**
     * Cadastra um novo usuário com validações e regras de negócio.
     */
    public function cadastrar(array $dados)
    {
        // Validação de dados obrigatórios
        $required = ['nome', 'email', 'cpf', 'grupo_acesso_id'];
        foreach ($required as $field) {
            if (empty($dados[$field])) {
                throw new \InvalidArgumentException("Campo obrigatório: $field");
            }
        }

        // Verificação de duplicidade (e-mail, CPF)
        if (Usuario::where('email', $dados['email'])->exists()) {
            throw new \Exception('Já existe um usuário com este e-mail.');
        }
        if (Usuario::where('cpf', $dados['cpf'])->exists()) {
            throw new \Exception('Já existe um usuário com este CPF.');
        }

        // Criação do usuário
        return Usuario::create([
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'cpf' => $dados['cpf'],
            'grupo_acesso_id' => $dados['grupo_acesso_id'],
            'telefone' => $dados['telefone'] ?? null,
            'ativo' => true,
            'observacao' => $dados['observacao'] ?? null,
        ]);
    }

    /**
     * Atualiza dados do usuário.
     */
    public function atualizar($usuarioId, array $dados)
    {
        $usuario = Usuario::findOrFail($usuarioId);

        // Validação de duplicidade
        if (isset($dados['email']) && Usuario::where('email', $dados['email'])->where('id', '!=', $usuarioId)->exists()) {
            throw new \Exception('Já existe um usuário com este e-mail.');
        }
        if (isset($dados['cpf']) && Usuario::where('cpf', $dados['cpf'])->where('id', '!=', $usuarioId)->exists()) {
            throw new \Exception('Já existe um usuário com este CPF.');
        }

        $usuario->fill($dados);
        $usuario->save();
        return $usuario;
    }

    /**
     * Inativa usuário, considerando permissões e vínculos (agendamentos futuros).
     */
    public function inativar($usuarioId)
    {
        $usuario = Usuario::findOrFail($usuarioId);

        // Verificar vínculos com agendamentos futuros
        $agendamentosFuturos = Agendamento::where('usuario_id', $usuarioId)
            ->where('data', '>=', now()->toDateString())
            ->count();

        if ($agendamentosFuturos > 0) {
            throw new \Exception('Não é possível inativar: usuário possui agendamentos futuros.');
        }

        $usuario->ativo = false;
        $usuario->save();
        return $usuario;
    }
}
