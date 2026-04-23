<?php

namespace App\Models\Chamados;

use Illuminate\Database\Eloquent\Model;

class ChamadosCategoria extends Model
{
    protected $table = 'chamados_categorias';

    protected $fillable = [
        'nome', 'codigo', 'cor', 'icone', 'descricao', 'parent_id',
        'sla_horas', 'equipe_responsavel', 'auto_assign', 'notificar_gestor',
        'prioridade_padrao', 'emails_notificacao', 'status',
    ];

    protected $casts = [
        'auto_assign'        => 'boolean',
        'notificar_gestor'   => 'boolean',
        'emails_notificacao' => 'array',
    ];
}
