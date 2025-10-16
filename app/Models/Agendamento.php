<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agendamento extends Model
{
    protected $table = 'agendamentos';

    protected $fillable = [
        'paciente_id',
        'funcionario_id',
        'procedimento_id',
        'data_agendamento',
        'hora_inicio',
        'hora_fim',
        'status',
        'observacoes',
        'valor',
        'confirmado',
        'lembrete_enviado',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'data_agendamento' => 'date',
        'hora_inicio' => 'datetime:H:i',
        'hora_fim' => 'datetime:H:i',
        'confirmado' => 'boolean',
        'lembrete_enviado' => 'boolean',
        'valor' => 'decimal:2'
    ];

    const STATUS_AGENDADO = 'agendado';
    const STATUS_CONFIRMADO = 'confirmado';
    const STATUS_EM_ANDAMENTO = 'em_andamento';
    const STATUS_FINALIZADO = 'finalizado';
    const STATUS_CANCELADO = 'cancelado';
    const STATUS_FALTOU = 'faltou';

    public static function getStatusOptions()
    {
        return [
            self::STATUS_AGENDADO => 'Agendado',
            self::STATUS_CONFIRMADO => 'Confirmado',
            self::STATUS_EM_ANDAMENTO => 'Em Andamento',
            self::STATUS_FINALIZADO => 'Finalizado',
            self::STATUS_CANCELADO => 'Cancelado',
            self::STATUS_FALTOU => 'Faltou'
        ];
    }

    /**
     * Relacionamento com Paciente
     */
    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    /**
     * Relacionamento com Funcionário (Dentista)
     */
    public function funcionario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'funcionario_id');
    }

    /**
     * Relacionamento com Procedimento
     */
    public function procedimento(): BelongsTo
    {
        return $this->belongsTo(Procedimento::class, 'procedimento_id');
    }

    /**
     * Usuário que criou o agendamento
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuário que atualizou o agendamento
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scopes para filtros
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('data_agendamento', $date);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('data_agendamento', [$startDate, $endDate]);
    }

    public function scopeByPaciente($query, $pacienteId)
    {
        return $query->where('paciente_id', $pacienteId);
    }

    public function scopeByFuncionario($query, $funcionarioId)
    {
        return $query->where('funcionario_id', $funcionarioId);
    }

    /**
     * Verifica se o agendamento pode ser cancelado
     */
    public function podeSerCancelado(): bool
    {
        return in_array($this->status, [
            self::STATUS_AGENDADO,
            self::STATUS_CONFIRMADO
        ]);
    }

    /**
     * Verifica se o agendamento pode ser confirmado
     */
    public function podeSerConfirmado(): bool
    {
        return $this->status === self::STATUS_AGENDADO;
    }

    /**
     * Obtém a cor para exibição no calendário baseada no status
     */
    public function getCorStatus(): string
    {
        return match($this->status) {
            self::STATUS_AGENDADO => '#FFA500',     // Laranja
            self::STATUS_CONFIRMADO => '#007BFF',   // Azul
            self::STATUS_EM_ANDAMENTO => '#28A745', // Verde
            self::STATUS_FINALIZADO => '#6C757D',   // Cinza
            self::STATUS_CANCELADO => '#DC3545',    // Vermelho
            self::STATUS_FALTOU => '#FF6B6B',       // Vermelho claro
            default => '#6C757D'
        };
    }
}
