<?php

namespace App\Http\Controllers\Chamados;

use App\Http\Controllers\Helpdesk\AbstractHelpdeskCrudController;
use App\Models\Chamados\ChamadosCategoria;

class ChamadosCategoriaController extends AbstractHelpdeskCrudController
{
    protected function modelClass(): string
    {
        return ChamadosCategoria::class;
    }

    protected function searchableFields(): array
    {
        return ['nome', 'codigo', 'descricao', 'equipe_responsavel'];
    }

    protected function filterableFields(): array
    {
        return ['status', 'prioridade_padrao'];
    }

    protected function validationRules(bool $isUpdate = false, ?int $id = null): array
    {
        $r = $isUpdate ? 'sometimes' : 'required';
        return [
            'nome'               => [$r, 'string', 'max:180'],
            'codigo'             => [$r, 'string', 'max:120', 'unique:chamados_categorias,codigo' . ($id ? ',' . $id : '')],
            'cor'                => ['nullable', 'string', 'max:30'],
            'icone'              => ['nullable', 'string', 'max:60'],
            'descricao'          => ['nullable', 'string'],
            'parent_id'          => ['nullable', 'integer'],
            'sla_horas'          => ['nullable', 'integer', 'min:1', 'max:720'],
            'equipe_responsavel' => ['nullable', 'string', 'max:180'],
            'auto_assign'        => ['nullable', 'boolean'],
            'notificar_gestor'   => ['nullable', 'boolean'],
            'prioridade_padrao'  => ['nullable', 'string', 'in:baixa,media,alta,critica'],
            'emails_notificacao' => ['nullable', 'array'],
            'status'             => ['nullable', 'string', 'in:ativo,inativo'],
        ];
    }
}
