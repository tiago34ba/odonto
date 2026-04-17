<?php

namespace App\Models\Chamados;

use Illuminate\Database\Eloquent\Model;

class ChamadosCampoPersonalizado extends Model
{
    protected $table = 'chamados_campos_personalizados';

    protected $fillable = [
        'nome', 'codigo', 'label', 'tipo', 'placeholder', 'obrigatorio',
        'visivel_cliente', 'visivel_tecnico', 'posicao', 'opcoes',
        'validacao_regex', 'valor_padrao', 'aplicado_em', 'grupo', 'status',
    ];

    protected $casts = [
        'obrigatorio'     => 'boolean',
        'visivel_cliente' => 'boolean',
        'visivel_tecnico' => 'boolean',
        'opcoes'          => 'array',
    ];
}
