<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrupoAcesso extends Model
{
    protected $table = 'grupo_acessos';

    protected $fillable =
    [
        'nome',
        'descricao',
        'cor',
        'permissoes',
        'ativo'

    ];

    protected $casts = [
        'permissoes' => 'array',
        'ativo' => 'boolean',
    ];

    public function acessos()
    {
        return $this->hasMany(Acesso::class);
    }
}
