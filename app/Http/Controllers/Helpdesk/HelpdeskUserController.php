<?php

namespace App\Http\Controllers\Helpdesk;

use App\Models\Helpdesk\HelpdeskUser;

class HelpdeskUserController extends AbstractHelpdeskCrudController
{
    protected function modelClass(): string { return HelpdeskUser::class; }

    protected function searchableFields(): array
    {
        return ['nome', 'codigo', 'email', 'empresa', 'departamento', 'cargo'];
    }

    protected function filterableFields(): array
    {
        return ['status', 'tipo'];
    }

    protected function validationRules(bool $isUpdate = false, ?int $id = null): array
    {
        $required = $isUpdate ? 'sometimes' : 'required';
        return [
            'nome' => [$required, 'string', 'max:180'],
            'codigo' => [$required, 'string', 'max:120', 'unique:helpdesk_users,codigo' . ($id ? ',' . $id : '')],
            'tipo' => [$required, 'string', 'in:tecnico,cliente,gestor'],
            'email' => [$required, 'email', 'max:180', 'unique:helpdesk_users,email' . ($id ? ',' . $id : '')],
            'telefone' => ['nullable', 'string', 'max:30'],
            'empresa' => ['nullable', 'string', 'max:180'],
            'departamento' => ['nullable', 'string', 'max:180'],
            'cargo' => ['nullable', 'string', 'max:180'],
            'status' => ['nullable', 'string', 'in:ativo,inativo,bloqueado'],
            'photo_url' => ['nullable', 'string', 'max:500'],
            'password_reset_required' => ['nullable', 'boolean'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
