<?php

namespace App\Http\Controllers\Helpdesk;

use App\Models\Helpdesk\HelpdeskAuditTrail;

class HelpdeskAuditTrailController extends AbstractHelpdeskCrudController
{
    protected function modelClass(): string { return HelpdeskAuditTrail::class; }

    protected function searchableFields(): array
    {
        return ['entity_type', 'acao', 'codigo', 'nome', 'actor_nome', 'actor_email', 'gestor_email', 'descricao'];
    }

    protected function validationRules(bool $isUpdate = false, ?int $id = null): array
    {
        $required = $isUpdate ? 'sometimes' : 'required';
        return [
            'entity_type' => [$required, 'string', 'max:120'],
            'entity_id' => ['nullable', 'integer'],
            'acao' => [$required, 'string', 'max:120'],
            'codigo' => [$required, 'string', 'max:120', 'unique:helpdesk_audit_trails,codigo' . ($id ? ',' . $id : '')],
            'nome' => [$required, 'string', 'max:180'],
            'actor_nome' => ['nullable', 'string', 'max:180'],
            'actor_email' => ['nullable', 'email', 'max:180'],
            'gestor_email' => ['nullable', 'email', 'max:180'],
            'ip_address' => ['nullable', 'string', 'max:50'],
            'user_agent' => ['nullable', 'string'],
            'payload_before' => ['nullable', 'array'],
            'payload_after' => ['nullable', 'array'],
            'descricao' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:registrado,processado,erro'],
        ];
    }
}
