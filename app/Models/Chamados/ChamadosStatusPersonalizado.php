<?php

namespace App\Models\Chamados;

use Illuminate\Database\Eloquent\Model;

class ChamadosStatusPersonalizado extends Model
{
    protected $table = 'chamados_status_personalizados';

    protected $fillable = [
        'nome', 'codigo', 'cor', 'icone', 'tipo', 'ordem',
        'permite_reabertura', 'envia_notificacao', 'exige_resolucao',
        'sla_pausado', 'publico', 'proximo_status_sugerido',
        'acoes_automaticas', 'status',
    ];

    protected $casts = [
        'permite_reabertura' => 'boolean',
        'envia_notificacao'  => 'boolean',
        'exige_resolucao'    => 'boolean',
        'sla_pausado'        => 'boolean',
        'publico'            => 'boolean',
        'acoes_automaticas'  => 'array',
    ];
}
