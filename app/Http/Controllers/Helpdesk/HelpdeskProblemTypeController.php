<?php

namespace App\Http\Controllers\Helpdesk;

use App\Models\Helpdesk\HelpdeskProblemType;

class HelpdeskProblemTypeController extends AbstractHelpdeskCrudController
{
    protected function modelClass(): string { return HelpdeskProblemType::class; }
    protected function validationRules(bool $isUpdate = false, ?int $id = null): array
    {
        $required = $isUpdate ? 'sometimes' : 'required';
        return [
            'nome' => [$required, 'string', 'max:180'],
            'codigo' => [$required, 'string', 'max:120', 'unique:helpdesk_problem_types,codigo' . ($id ? ',' . $id : '')],
            'categoria' => ['nullable', 'string', 'max:120'],
            'descricao' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:ativo,inativo'],
        ];
    }
}
