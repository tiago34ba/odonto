<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrupoAcesso extends Model
{
    protected $table = 'grupo_acessos'; // Ajuste conforme o nome da tabela
    protected $fillable =
    [
        'nome',
        'descricao',
        'ativo'

    ];
}
