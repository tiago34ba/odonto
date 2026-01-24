<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Convenio extends Model
{
    protected $table = 'convenios';

    protected $fillable = [
        'nome',         // Nome do convênio
        'cnpj',         // CNPJ do convênio
        'descricao',    // Descrição detalhada
        'ativo',        // Status do convênio
    ];

    // Relacionamento: convênio possui vários pacientes
    public function pacientes()
    {
        return $this->hasMany(Paciente::class, 'convenio_id');
    }

    // Relacionamento: convênio possui vários procedimentos
    public function procedimentos()
    {
        return $this->hasMany(Procedimento::class, 'convenio_id');
    }

    // Escopo para convênios ativos
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    // Regra de negócio: verifica se pode ser inativado
    public function podeInativar()
    {
        return $this->pacientes()->count() === 0 && $this->procedimentos()->count() === 0;
    }
}
