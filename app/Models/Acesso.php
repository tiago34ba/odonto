<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Acesso extends Model
{
    protected $table = 'acessos';

    protected $fillable = [
        'nome',
        'codigo',
        'descricao',
        'categoria',
        'nivel_risco',
        'sistema_interno',
        'ativo',
        'grupo_acesso_id',
    ];

    protected $casts = [
        'sistema_interno' => 'boolean',
        'ativo' => 'boolean',
    ];

    public function grupoAcesso()
    {
        return $this->belongsTo(GrupoAcesso::class);
    }
}
