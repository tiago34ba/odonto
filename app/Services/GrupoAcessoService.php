<?php

namespace App\Services;

use App\Models\GrupoAcesso;
use App\Models\Usuario; // Certifique-se de que o arquivo app/Models/Usuario.php existe e define a classe Usuario corretamente

class GrupoAcessoService
{
    /**
     * Cadastra um novo grupo de acesso com validações e regras de negócio.
     */
    public function cadastrar(array $dados)
    {
        // Validação de dados obrigatórios
        $required = ['nome'];
        foreach ($required as $field) {
            if (empty($dados[$field])) {
                throw new \InvalidArgumentException("Campo obrigatório: $field");
            }
        }

        // Verificação de duplicidade (nome)
        if (GrupoAcesso::where('nome', $dados['nome'])->exists()) {
            throw new \Exception('Já existe um grupo de acesso com este nome.');
        }

        // Criação do grupo de acesso
        return GrupoAcesso::create([
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'] ?? null,
            'ativo' => true
        ]);
    }

    /**
     * Atualiza dados do grupo de acesso.
     */
    public function atualizar($grupoId, array $dados)
    {
        $grupo = GrupoAcesso::findOrFail($grupoId);

        // Validação de nome único
        if (isset($dados['nome']) && GrupoAcesso::where('nome', $dados['nome'])->where('id', '!=', $grupoId)->exists()) {
            throw new \Exception('Já existe um grupo de acesso com este nome.');
        }

        $grupo->fill($dados);
        $grupo->save();
        return $grupo;
    }

    /**
     * Inativa grupo de acesso, considerando vínculos (usuários).
     */
    public function inativar($grupoId)
    {
        $grupo = GrupoAcesso::findOrFail($grupoId);

        // Verificar vínculos com usuários
        $vinculos = Usuario::where('grupo_acesso_id', $grupoId)->count();
        if ($vinculos > 0) {
            throw new \Exception('Não é possível inativar: grupo de acesso vinculado a usuários.');
        }

        $grupo->ativo = false;
        $grupo->save();
        return $grupo;
    }
}
