<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Acesso extends Model
{
    protected $table = 'acessos';

    protected $fillable = [
        'nome',               // Nome da permissão/acesso
        'descricao',          // Descrição detalhada
        'grupo_acesso_id',    // Relacionamento com GrupoAcesso
        'ativo',              // Status do acesso
    ];

    // Relacionamento: cada acesso pertence a um grupo de acesso
    public function grupoAcesso()
    {
        return $this->belongsTo(GrupoAcesso::class);
    }

    // Regra de negócio: escopo para acessos ativos
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    // Regra de negócio: verifica se pode ser inativado (sem usuários vinculados)
    public function podeInativar()
    {
        return $this->usuarios()->count() === 0;
    }

    // Relacionamento: usuários vinculados ao acesso (ajuste conforme estrutura)
    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'acesso_id');
    }
}
