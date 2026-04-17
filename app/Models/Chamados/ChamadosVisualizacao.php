<?php

namespace App\Models\Chamados;

use Illuminate\Database\Eloquent\Model;

class ChamadosVisualizacao extends Model
{
    protected $table = 'chamados_visualizacoes';

    protected $fillable = [
        'nome', 'codigo', 'tipo', 'filtros', 'colunas', 'ordenacao',
        'agrupamento', 'compartilhado', 'usuario_criador', 'equipe_id',
        'uso_count', 'icone', 'status',
    ];

    protected $casts = [
        'filtros'      => 'array',
        'colunas'      => 'array',
        'ordenacao'    => 'array',
        'compartilhado' => 'boolean',
    ];
}
