<?php

namespace App\Http\Controllers\Helpdesk;

use App\Models\Helpdesk\HelpdeskProblem;

class HelpdeskProblemController extends AbstractHelpdeskCrudController
{
    protected function modelClass(): string { return HelpdeskProblem::class; }

    protected function searchableFields(): array
    {
        return ['nome', 'codigo', 'descricao', 'sintomas', 'impacto'];
    }

    protected function validationRules(bool $isUpdate = false, ?int $id = null): array
    {
        $required = $isUpdate ? 'sometimes' : 'required';
        return [
            'area_id' => ['nullable', 'integer', 'exists:helpdesk_areas,id'],
            'problem_type_id' => ['nullable', 'integer', 'exists:helpdesk_problem_types,id'],
            'nome' => [$required, 'string', 'max:180'],
            'codigo' => [$required, 'string', 'max:120', 'unique:helpdesk_problems,codigo' . ($id ? ',' . $id : '')],
            'descricao' => ['nullable', 'string'],
            'sintomas' => ['nullable', 'string'],
            'impacto' => ['nullable', 'string', 'max:120'],
            'workaround' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:ativo,inativo'],
        ];
    }
}
