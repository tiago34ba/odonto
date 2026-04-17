<?php

namespace App\Http\Controllers\Saas;

use App\Models\Saas\SaasSupportModulo;

class SaasSupportModuloController extends AbstractSaasCrudController
{
    protected function modelClass(): string
    {
        return SaasSupportModulo::class;
    }

    protected function validationRules(bool $isUpdate = false): array
    {
        $idRule = $isUpdate ? 'sometimes' : 'required';

        return [
            'submodulo' => [$idRule, 'string', 'in:chamados,base_conhecimento,comunicacao,onboarding'],
            'nome' => [$idRule, 'string', 'max:180'],
            'codigo' => [$idRule, 'string', 'max:120'],
            'status' => ['nullable', 'string', 'in:ativo,inativo,arquivado'],
            'prioridade' => ['nullable', 'string', 'in:baixa,media,alta,critica'],
            'owner_nome' => ['nullable', 'string', 'max:180'],
            'owner_email' => ['nullable', 'email', 'max:180'],
            'descricao' => ['nullable', 'string'],
            'configuracao' => ['nullable', 'array'],
            'metricas' => ['nullable', 'array'],
            'limites' => ['nullable', 'array'],
            'integracoes' => ['nullable', 'array'],
            'politicas' => ['nullable', 'array'],
            'tags' => ['nullable', 'array'],
            'ultimo_evento_em' => ['nullable', 'date'],
            'sla_horas' => ['nullable', 'integer', 'min:1', 'max:720'],
            'custo_mensal' => ['nullable', 'numeric', 'min:0'],
            'receita_mensal' => ['nullable', 'numeric', 'min:0'],
            'risco_nivel' => ['nullable', 'string', 'in:baixo,medio,alto,critico'],
            'conformidade_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'ativo' => ['nullable', 'boolean'],
        ];
    }
}
