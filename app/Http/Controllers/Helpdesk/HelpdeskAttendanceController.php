<?php

namespace App\Http\Controllers\Helpdesk;

use App\Models\Helpdesk\HelpdeskAttendance;

class HelpdeskAttendanceController extends AbstractHelpdeskCrudController
{
    protected function modelClass(): string { return HelpdeskAttendance::class; }

    protected function searchableFields(): array
    {
        return ['nome', 'codigo', 'descricao', 'tipo_atendimento'];
    }

    protected function filterableFields(): array
    {
        return ['status', 'ticket_id'];
    }

    protected function validationRules(bool $isUpdate = false, ?int $id = null): array
    {
        $required = $isUpdate ? 'sometimes' : 'required';
        return [
            'ticket_id' => [$required, 'integer', 'exists:helpdesk_tickets,id'],
            'tecnico_id' => ['nullable', 'integer', 'exists:helpdesk_users,id'],
            'solution_id' => ['nullable', 'integer', 'exists:helpdesk_solutions,id'],
            'nome' => [$required, 'string', 'max:180'],
            'codigo' => [$required, 'string', 'max:120', 'unique:helpdesk_attendances,codigo' . ($id ? ',' . $id : '')],
            'tipo_atendimento' => ['nullable', 'string', 'max:120'],
            'descricao' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:registrado,em_execucao,concluido,cancelado'],
            'iniciado_em' => ['nullable', 'date'],
            'finalizado_em' => ['nullable', 'date'],
            'horas_trabalhadas' => ['nullable', 'numeric', 'min:0'],
            'custo' => ['nullable', 'numeric', 'min:0'],
            'anexos' => ['nullable', 'array'],
        ];
    }
}
