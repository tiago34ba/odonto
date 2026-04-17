<?php

namespace App\Http\Controllers\Helpdesk;

use App\Models\Helpdesk\HelpdeskClass;

class HelpdeskClassController extends AbstractHelpdeskCrudController
{
    protected function modelClass(): string { return HelpdeskClass::class; }
    protected function validationRules(bool $isUpdate = false, ?int $id = null): array
    {
        $required = $isUpdate ? 'sometimes' : 'required';
        return [
            'nome' => [$required, 'string', 'max:180'],
            'codigo' => [$required, 'string', 'max:120', 'unique:helpdesk_classes,codigo' . ($id ? ',' . $id : '')],
            'descricao' => ['nullable', 'string'],
            'regra_fila' => ['nullable', 'array'],
            'status' => ['nullable', 'string', 'in:ativo,inativo'],
        ];
    }
}
