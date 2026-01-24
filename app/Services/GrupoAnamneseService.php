<?php

namespace App\Services;

use App\Models\GrupoAnamnese;
use App\Models\ItemAnamnese;
use App\Models\Consulta;

class GrupoAnamneseService
{
    /**
     * Cadastra um novo grupo de anamnese com validações e regras de negócio.
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
        if (GrupoAnamnese::where('nome', $dados['nome'])->exists()) {
            throw new \Exception('Já existe um grupo de anamnese com este nome.');
        }

        // Criação do grupo
        return GrupoAnamnese::create([
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'] ?? null,
            'ativo' => true,
        ]);
    }

    /**
     * Atualiza dados do grupo de anamnese.
     */
    public function atualizar($grupoId, array $dados)
    {
        $grupo = GrupoAnamnese::findOrFail($grupoId);

        // Validação de nome único
        if (isset($dados['nome']) && GrupoAnamnese::where('nome', $dados['nome'])->where('id', '!=', $grupoId)->exists()) {
            throw new \Exception('Já existe um grupo de anamnese com este nome.');
        }

        $grupo->fill($dados);
        $grupo->save();
        return $grupo;
    }

    /**
     * Inativa grupo de anamnese, considerando vínculos (itens, consultas).
     */
    public function inativar($grupoId)
    {
        $grupo = GrupoAnamnese::findOrFail($grupoId);

        // Verificar vínculos com itens de anamnese
        $itensVinculados = ItemAnamnese::where('grupo_anamnese_id', $grupoId)->count();
        if ($itensVinculados > 0) {
            throw new \Exception('Não é possível inativar: grupo vinculado a itens de anamnese.');
        }

        // Verificar vínculos com consultas
        $consultasVinculadas = Consulta::where('grupo_anamnese_id', $grupoId)->count();
        if ($consultasVinculadas > 0) {
            throw new \Exception('Não é possível inativar: grupo vinculado a consultas.');
        }

        $grupo->ativo = false;
        $grupo->save();
        return $grupo;
    }
}
