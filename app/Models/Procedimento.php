<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Procedimento extends Model
{
    protected $table = 'procedimentos';

    protected $fillable = [
        'nome',
        'descricao',
        'valor',
        'duracao_minutos',
        'ativo',
        'codigo',
        'categoria',
        'observacoes'
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'ativo' => 'boolean',
        'duracao_minutos' => 'integer'
    ];

    const CATEGORIAS = [
        'preventiva' => 'Odontologia Preventiva',
        'restauradora' => 'Odontologia Restauradora',
        'endodontia' => 'Endodontia',
        'periodontia' => 'Periodontia',
        'cirurgia' => 'Cirurgia Oral',
        'ortodontia' => 'Ortodontia',
        'protese' => 'Prótese',
        'implante' => 'Implantodontia',
        'estetica' => 'Odontologia Estética',
        'pediatrica' => 'Odontopediatria',
        'urgencia' => 'Urgência/Emergência',
        'outros' => 'Outros'
    ];

    /**
     * Relacionamento com Agendamentos
     */
    public function agendamentos(): HasMany
    {
        return $this->hasMany(Agendamento::class, 'procedimento_id');
    }

    /**
     * Scope para procedimentos ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para busca por categoria
     */
    public function scopeByCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    /**
     * Scope para busca por nome
     */
    public function scopeByNome($query, $nome)
    {
        return $query->where('nome', 'like', '%' . $nome . '%');
    }

    /**
     * Obtém o nome da categoria
     */
    public function getNomeCategoriaAttribute(): string
    {
        return self::CATEGORIAS[$this->categoria] ?? 'Não definida';
    }

    /**
     * Formata a duração em horas e minutos
     */
    public function getDuracaoFormatadaAttribute(): string
    {
        $horas = intdiv($this->duracao_minutos, 60);
        $minutos = $this->duracao_minutos % 60;

        if ($horas > 0) {
            return $horas . 'h' . ($minutos > 0 ? ' ' . $minutos . 'min' : '');
        }

        return $minutos . 'min';
    }

    /**
     * Formata o valor em reais
     */
    public function getValorFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->valor, 2, ',', '.');
    }
}
