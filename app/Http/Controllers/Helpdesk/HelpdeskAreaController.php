<?php

namespace App\Http\Controllers\Helpdesk;

use App\Models\Helpdesk\HelpdeskArea;

class HelpdeskAreaController extends AbstractHelpdeskCrudController
{
    protected function modelClass(): string { return HelpdeskArea::class; }
    protected function validationRules(bool $isUpdate = false, ?int $id = null): array
    {
        $required = $isUpdate ? 'sometimes' : 'required';
        return [
            'nome' => [$required, 'string', 'max:180'],
            'codigo' => [$required, 'string', 'max:120', 'unique:helpdesk_areas,codigo' . ($id ? ',' . $id : '')],
            'descricao' => ['nullable', 'string'],
            'gestor_nome' => ['nullable', 'string', 'max:180'],
            'gestor_email' => ['nullable', 'email', 'max:180'],
            'sla_padrao_horas' => ['nullable', 'integer', 'min:1', 'max:720'],
            'escalation_rules' => ['nullable', 'array'],
            'status' => ['nullable', 'string', 'in:ativo,inativo'],
        ];
    }
}
