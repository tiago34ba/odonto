<?php

namespace App\Http\Controllers\Chamados;

use App\Http\Controllers\Helpdesk\AbstractHelpdeskCrudController;
use App\Models\Chamados\ChamadosStatusPersonalizado;

class ChamadosStatusPersonalizadoController extends AbstractHelpdeskCrudController
{
    protected function modelClass(): string
    {
        return ChamadosStatusPersonalizado::class;
    }

    protected function searchableFields(): array
    {
        return ['nome', 'codigo'];
    }

    protected function filterableFields(): array
    {
        return ['status', 'tipo'];
    }

    protected function validationRules(bool $isUpdate = false, ?int $id = null): array
    {
        $r = $isUpdate ? 'sometimes' : 'required';
        return [
            'nome'                    => [$r, 'string', 'max:180'],
            'codigo'                  => [$r, 'string', 'max:120', 'unique:chamados_status_personalizados,codigo' . ($id ? ',' . $id : '')],
            'cor'                     => ['nullable', 'string', 'max:30'],
            'icone'                   => ['nullable', 'string', 'max:60'],
            'tipo'                    => ['nullable', 'string', 'in:aberto,em_andamento,aguardando,resolvido,fechado,arquivado'],
            'ordem'                   => ['nullable', 'integer', 'min:0'],
            'permite_reabertura'      => ['nullable', 'boolean'],
            'envia_notificacao'       => ['nullable', 'boolean'],
            'exige_resolucao'         => ['nullable', 'boolean'],
            'sla_pausado'             => ['nullable', 'boolean'],
            'publico'                 => ['nullable', 'boolean'],
            'proximo_status_sugerido' => ['nullable', 'string', 'max:120'],
            'acoes_automaticas'       => ['nullable', 'array'],
            'status'                  => ['nullable', 'string', 'in:ativo,inativo'],
        ];
    }
}
