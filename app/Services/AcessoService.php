<?php

namespace App\Services;

use App\Models\Acesso;
use App\Models\GrupoAcesso;
use App\Models\Usuario;

class AcessoService
{
    /**
     * Cadastra um novo acesso (permissão) com validações e regras de negócio.
     */
    public function cadastrar(array $dados)
    {
        // Validação de dados obrigatórios
        $required = ['nome', 'grupo_acesso_id'];
        foreach ($required as $field) {
            if (empty($dados[$field])) {
                throw new \InvalidArgumentException("Campo obrigatório: $field");
            }
        }

        // Verificação de duplicidade (nome + grupo)
        if (Acesso::where('nome', $dados['nome'])
            ->where('grupo_acesso_id', $dados['grupo_acesso_id'])
            ->exists()) {
            throw new \Exception('Já existe uma permissão com este nome para o grupo informado.');
        }

        // Criação do acesso
        return Acesso::create([
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'] ?? null,
            'grupo_acesso_id' => $dados['grupo_acesso_id'],
            'ativo' => true,
        ]);
    }

    /**
     * Atualiza dados do acesso.
     */
    public function atualizar($acessoId, array $dados)
    {
        $acesso = Acesso::findOrFail($acessoId);

        // Validação de nome único por grupo
        if (isset($dados['nome']) && isset($dados['grupo_acesso_id'])) {
            if (Acesso::where('nome', $dados['nome'])
                ->where('grupo_acesso_id', $dados['grupo_acesso_id'])
                ->where('id', '!=', $acessoId)
                ->exists()) {
                throw new \Exception('Já existe uma permissão com este nome para o grupo informado.');
            }
        }

        $acesso->fill($dados);
        $acesso->save();
        return $acesso;
    }

    /**
     * Inativa acesso, considerando vínculos (usuários, grupos).
     */
    public function inativar($acessoId)
    {
        $acesso = Acesso::findOrFail($acessoId);

        // Verificar vínculos com usuários
        $usuariosVinculados = Usuario::where('acesso_id', $acessoId)->count();
        if ($usuariosVinculados > 0) {
            throw new \Exception('Não é possível inativar: permissão vinculada a usuários.');
        }

        $acesso->ativo = false;
        $acesso->save();
        return $acesso;
    }
}
