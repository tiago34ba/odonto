<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaasMensalidade extends Model
{
    use HasFactory;

    protected $table = 'saas_mensalidades';

    protected $fillable = [
        'clinica',
        'plano_id',
        'plano_nome',
        'valor_mensal',
        'total_usuarios',
        'usuarios_ativos',
        'proximo_vencimento',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'valor_mensal' => 'float',
            'total_usuarios' => 'integer',
            'usuarios_ativos' => 'integer',
            'proximo_vencimento' => 'date',
        ];
    }
}
