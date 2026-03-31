<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuarios'; // ajuste conforme o nome da tabela
    // Adicione os campos fillable conforme necessário
    protected $fillable = [
        'nome',
        'email',
        'grupo_acesso_id',
        // outros campos...
    ];
}
