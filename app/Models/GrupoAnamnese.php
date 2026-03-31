<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrupoAnamnese extends Model
{
    protected $table = 'grupos_anamnese';

    protected $fillable = [
        'nome',         // Nome do grupo de anamnese
        'descricao',    // Descrição detalhada
        'ativo',        // Status do grupo
    ];

    // Relacionamento: grupo possui vários itens de anamnese
    public function itens()
    {
        return $this->hasMany(ItemAnamnese::class, 'grupo_anamnese_id');
    }

    // Relacionamento: grupo pode estar vinculado a consultas
    public function consultas()
    {
        return $this->hasMany(Consulta::class, 'grupo_anamnese_id');
    }

    // Escopo para grupos ativos
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    // Regra de negócio: verifica se pode ser inativado
    public function podeInativar()
    {
        return $this->itens()->count() === 0 && $this->consultas()->count() === 0;
    }
}
