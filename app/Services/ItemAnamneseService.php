<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

// Certifique-se de que o arquivo do modelo existe em app/Models/ItemAnamnese.php
use App\Models\ItemAnamnese;

class ItemAnamneseService
{
    /**
     * Cadastra um novo item de anamnese com validações e regras de negócio.
     */
    public function cadastrar(array $dados)
    {
        Validator::make($dados, [
            'nome' => 'required|string|max:255',
            'grupo_id' => 'required|exists:group_anamneses,id',
        ])->validate();

        $duplicado = ItemAnamnese::where('nome', $dados['nome'])
            ->where('grupo_id', $dados['grupo_id'])
            ->exists();
        if ($duplicado) {
            throw new \Exception('Já existe um item de anamnese com este nome neste grupo.');
        }

        return DB::transaction(function () use ($dados) {
            return ItemAnamnese::create($dados);
        });
    }

    /**
     * Atualiza dados do item de anamnese.
     */
    public function atualizar($itemId, array $dados)
    {
        $item = ItemAnamnese::findOrFail($itemId);
        Validator::make($dados, [
            'nome' => 'sometimes|required|string|max:255',
            'grupo_id' => 'sometimes|required|exists:group_anamneses,id',
        ])->validate();

        $duplicado = ItemAnamnese::where('nome', $dados['nome'] ?? $item->nome)
            ->where('grupo_id', $dados['grupo_id'] ?? $item->grupo_id)
            ->where('id', '!=', $itemId)
            ->exists();
        if ($duplicado) {
            throw new \Exception('Já existe um item de anamnese com este nome neste grupo.');
        }

        return DB::transaction(function () use ($item, $dados) {
            $item->update($dados);
            return $item;
        });
    }

    /**
     * Inativa item de anamnese, considerando vínculos (consultas).
     */
    public function inativar($itemId)
    {
        $item = ItemAnamnese::findOrFail($itemId);
        // Verifica se o item está vinculado a consultas
        $vinculado = $item->consultas()->exists();
        if ($vinculado) {
            throw new \Exception('Item de anamnese vinculado a consultas não pode ser inativado.');
        }
        $item->ativo = false;
        $item->save();
        return $item;
    }
}
