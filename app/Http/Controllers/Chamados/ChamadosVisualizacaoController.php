<?php

namespace App\Http\Controllers\Chamados;

use App\Http\Controllers\Helpdesk\AbstractHelpdeskCrudController;
use App\Models\Chamados\ChamadosVisualizacao;

class ChamadosVisualizacaoController extends AbstractHelpdeskCrudController
{
    protected function modelClass(): string
    {
        return ChamadosVisualizacao::class;
    }

    protected function searchableFields(): array
    {
        return ['nome', 'codigo', 'usuario_criador'];
    }

    protected function filterableFields(): array
    {
        return ['status', 'tipo'];
    }

    protected function validationRules(bool $isUpdate = false, ?int $id = null): array
    {
        $r = $isUpdate ? 'sometimes' : 'required';
        return [
            'nome'            => [$r, 'string', 'max:180'],
            'codigo'          => [$r, 'string', 'max:120', 'unique:chamados_visualizacoes,codigo' . ($id ? ',' . $id : '')],
            'tipo'            => ['nullable', 'string', 'in:pessoal,equipe,global'],
            'filtros'         => ['nullable', 'array'],
            'colunas'         => ['nullable', 'array'],
            'ordenacao'       => ['nullable', 'array'],
            'agrupamento'     => ['nullable', 'string', 'max:60'],
            'compartilhado'   => ['nullable', 'boolean'],
            'usuario_criador' => ['nullable', 'string', 'max:180'],
            'equipe_id'       => ['nullable', 'string', 'max:60'],
            'icone'           => ['nullable', 'string', 'max:60'],
            'status'          => ['nullable', 'string', 'in:ativo,inativo'],
        ];
    }
}
