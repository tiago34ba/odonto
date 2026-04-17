<?php

namespace App\Http\Controllers\Helpdesk;

use App\Models\Helpdesk\HelpdeskSolution;

class HelpdeskSolutionController extends AbstractHelpdeskCrudController
{
    protected function modelClass(): string { return HelpdeskSolution::class; }

    protected function searchableFields(): array
    {
        return ['nome', 'codigo', 'descricao', 'passos'];
    }

    protected function validationRules(bool $isUpdate = false, ?int $id = null): array
    {
        $required = $isUpdate ? 'sometimes' : 'required';
        return [
            'problem_id' => ['nullable', 'integer', 'exists:helpdesk_problems,id'],
            'nome' => [$required, 'string', 'max:180'],
            'codigo' => [$required, 'string', 'max:120', 'unique:helpdesk_solutions,codigo' . ($id ? ',' . $id : '')],
            'descricao' => ['nullable', 'string'],
            'passos' => ['nullable', 'string'],
            'kb_article_url' => ['nullable', 'string', 'max:500'],
            'tempo_medio_min' => ['nullable', 'integer', 'min:0'],
            'efetividade_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['nullable', 'string', 'in:ativo,inativo'],
        ];
    }
}
