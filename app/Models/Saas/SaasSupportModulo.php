<?php

namespace App\Models\Saas;

use Illuminate\Database\Eloquent\Model;

class SaasSupportModulo extends Model
{
    protected $table = 'saas_support_modulos';

    protected $fillable = [
        'submodulo','nome','codigo','status','prioridade','owner_nome','owner_email','descricao',
        'configuracao','metricas','limites','integracoes','politicas','tags','ultimo_evento_em',
        'sla_horas','custo_mensal','receita_mensal','risco_nivel','conformidade_score','ativo','created_by','updated_by',
    ];

    protected $casts = [
        'configuracao' => 'array','metricas' => 'array','limites' => 'array','integracoes' => 'array',
        'politicas' => 'array','tags' => 'array','ultimo_evento_em' => 'datetime','custo_mensal' => 'float',
        'receita_mensal' => 'float','conformidade_score' => 'float','ativo' => 'boolean',
    ];
}
