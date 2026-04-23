<?php

namespace App\Http\Controllers\Chamados;

use App\Http\Controllers\Helpdesk\AbstractHelpdeskCrudController;
use App\Models\Chamados\ChamadosCampoPersonalizado;

class ChamadosCampoPersonalizadoController extends AbstractHelpdeskCrudController
{
    protected function modelClass(): string
    {
        return ChamadosCampoPersonalizado::class;
    }

    protected function searchableFields(): array
    {
        return ['nome', 'codigo', 'label', 'grupo'];
    }

    protected function filterableFields(): array
    {
        return ['status', 'tipo', 'aplicado_em'];
    }

    protected function validationRules(bool $isUpdate = false, ?int $id = null): array
    {
        $r = $isUpdate ? 'sometimes' : 'required';
        return [
            'nome'            => [$r, 'string', 'max:180'],
            'codigo'          => [$r, 'string', 'max:120', 'unique:chamados_campos_personalizados,codigo' . ($id ? ',' . $id : '')],
            'label'           => [$r, 'string', 'max:180'],
            'tipo'            => ['nullable', 'string', 'in:text,select,multi_select,date,datetime,number,url,email,phone,checkbox,textarea'],
            'placeholder'     => ['nullable', 'string', 'max:255'],
            'obrigatorio'     => ['nullable', 'boolean'],
            'visivel_cliente' => ['nullable', 'boolean'],
            'visivel_tecnico' => ['nullable', 'boolean'],
            'posicao'         => ['nullable', 'integer', 'min:0'],
            'opcoes'          => ['nullable', 'array'],
            'validacao_regex' => ['nullable', 'string', 'max:255'],
            'valor_padrao'    => ['nullable', 'string', 'max:500'],
            'aplicado_em'     => ['nullable', 'string', 'in:chamados,atendimentos,pre_cadastro,todos'],
            'grupo'           => ['nullable', 'string', 'max:120'],
            'status'          => ['nullable', 'string', 'in:ativo,inativo'],
        ];
    }
}
