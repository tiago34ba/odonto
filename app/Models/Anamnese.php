<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anamnese extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'name',
        'group',
        'description',
        'tipo_resposta',
        'obrigatorio',
        'opcoes_resposta',
        'ativo',
    ];

    protected $casts = [
        'obrigatorio' => 'boolean',
        'opcoes_resposta' => 'array',
        'ativo' => 'boolean',
    ];
}
