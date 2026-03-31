<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Procedure extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'value',
        'time',
        'accepts_agreement',
        'preparation',
        'description',
        'category',
        'active',
        'requires_anesthesia',
        'complexity_level',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'time' => 'integer',
        'accepts_agreement' => 'boolean',
        'active' => 'boolean',
        'requires_anesthesia' => 'boolean',
    ];

    /**
     * Relacionamentos
     */

    /**
     * Agendamentos deste procedimento
     */
    public function agendamentos()
    {
        return $this->hasMany(Scheduling::class, 'procedure_id');
    }

    /**
     * Pacientes que fizeram este procedimento
     */
    public function pacientes()
    {
        return $this->belongsToMany(Paciente::class, 'patient_procedures')
                    ->withPivot('scheduling_id', 'date', 'status', 'value')
                    ->withTimestamps();
    }

    /**
     * Scopes
     */

    /**
     * Procedimentos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Procedimentos por categoria
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Procedimentos que aceitam convênio
     */
    public function scopeAcceptsAgreement($query)
    {
        return $query->where('accepts_agreement', true);
    }

    /**
     * Accessors
     */

    /**
     * Valor formatado em moeda
     */
    public function getFormattedValueAttribute()
    {
        return 'R$ ' . number_format($this->value, 2, ',', '.');
    }

    /**
     * Tempo formatado em horas e minutos
     */
    public function getFormattedTimeAttribute()
    {
        $hours = intval($this->time / 60);
        $minutes = $this->time % 60;
        
        if ($hours > 0) {
            return $hours . 'h' . ($minutes > 0 ? ' ' . $minutes . 'min' : '');
        }
        
        return $minutes . 'min';
    }

    /**
     * Nível de complexidade formatado
     */
    public function getComplexityLevelFormattedAttribute()
    {
        $levels = [
            1 => 'Baixa',
            2 => 'Média',
            3 => 'Alta',
            4 => 'Muito Alta'
        ];

        return $levels[$this->complexity_level] ?? 'Não Definido';
    }

    /**
     * Métodos auxiliares
     */

    /**
     * Verifica se o procedimento está ativo
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Verifica se aceita convênio
     */
    public function acceptsAgreement()
    {
        return $this->accepts_agreement;
    }

    /**
     * Verifica se requer anestesia
     */
    public function requiresAnesthesia()
    {
        return $this->requires_anesthesia;
    }
}
