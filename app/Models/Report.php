<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'data',
        'period_start',
        'period_end',
        'generated_by',
        'is_scheduled',
        'schedule_frequency',
        'last_generated_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_scheduled' => 'boolean',
        'last_generated_at' => 'datetime',
    ];

    /**
     * Tipos de relatórios disponíveis
     */
    public static function getReportTypes()
    {
        return [
            'system' => 'Relatório Geral do Sistema',
            'patients' => 'Relatório de Pacientes',
            'appointments' => 'Relatório de Agendamentos',
            'financial' => 'Relatório Financeiro',
            'staff' => 'Relatório de Profissionais',
            'productivity' => 'Relatório de Produtividade'
        ];
    }

    /**
     * Frequências de agendamento
     */
    public static function getScheduleFrequencies()
    {
        return [
            'daily' => 'Diário',
            'weekly' => 'Semanal',
            'monthly' => 'Mensal',
            'quarterly' => 'Trimestral'
        ];
    }

    /**
     * Scope para relatórios agendados
     */
    public function scopeScheduled($query)
    {
        return $query->where('is_scheduled', true);
    }

    /**
     * Scope por tipo
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}