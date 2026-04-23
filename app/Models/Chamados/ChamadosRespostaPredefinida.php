<?php

namespace App\Models\Chamados;

use Illuminate\Database\Eloquent\Model;

class ChamadosRespostaPredefinida extends Model
{
    protected $table = 'chamados_respostas_predefinidas';

    protected $fillable = [
        'titulo', 'codigo', 'categoria_id', 'conteudo', 'tags',
        'uso_count', 'ultima_ativacao_at', 'visivel_para', 'atalho_teclado',
        'idioma', 'incluir_anexo', 'status',
    ];

    protected $casts = [
        'tags'               => 'array',
        'incluir_anexo'      => 'boolean',
        'ultima_ativacao_at' => 'datetime',
    ];
}
