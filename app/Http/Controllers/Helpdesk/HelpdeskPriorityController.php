<?php

namespace App\Http\Controllers\Helpdesk;

use App\Models\Helpdesk\HelpdeskPriority;

class HelpdeskPriorityController extends AbstractHelpdeskCrudController
{
    protected function modelClass(): string { return HelpdeskPriority::class; }
    protected function validationRules(bool $isUpdate = false, ?int $id = null): array
    {
        $required = $isUpdate ? 'sometimes' : 'required';
        return [
            'nome' => [$required, 'string', 'max:180'],
            'codigo' => [$required, 'string', 'max:120', 'unique:helpdesk_priorities,codigo' . ($id ? ',' . $id : '')],
            'cor' => ['nullable', 'string', 'max:20'],
            'ordem' => ['nullable', 'integer', 'min:1'],
            'tempo_resposta_horas' => ['nullable', 'integer', 'min:1'],
            'tempo_resolucao_horas' => ['nullable', 'integer', 'min:1'],
            'descricao' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:ativo,inativo'],
        ];
    }
}
