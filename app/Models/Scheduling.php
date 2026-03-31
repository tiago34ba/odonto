<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class Scheduling extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'professional_id',
        'procedure_id',
        'date',
        'time',
        'return',
        'obs',
        'status',
        'scheduled_at',
        'duration',
        'confirmed_at',
        'canceled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'date' => 'date',
        'scheduled_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'canceled_at' => 'datetime',
        'return' => 'boolean',
        'duration' => 'integer',
    ];

    /**
     * Relacionamentos
     */

    /**
     * Paciente do agendamento
     */
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'patient_id');
    }

    /**
     * Profissional do agendamento
     */
    public function profissional()
    {
        return $this->belongsTo(Employee::class, 'professional_id');
    }

    /**
     * Procedimento do agendamento
     */
    public function procedimento()
    {
        return $this->belongsTo(Procedure::class, 'procedure_id');
    }

    /**
     * Scopes
     */

    /**
     * Agendamentos de hoje
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    /**
     * Agendamentos da semana
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Agendamentos por status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Agendamentos confirmados
     */
    public function scopeConfirmed($query)
    {
        return $query->whereNotNull('confirmed_at');
    }

    /**
     * Agendamentos cancelados
     */
    public function scopeCanceled($query)
    {
        return $query->whereNotNull('canceled_at');
    }

    /**
     * Accessors
     */

    /**
     * LGPD: retorna observacoes descriptografadas quando aplicavel.
     */
    public function getObsAttribute($value)
    {
        if ($value === null || $value === '') {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            // Compatibilidade com registros legados em texto plano.
            return $value;
        }
    }

    /**
     * LGPD: persiste observacoes criptografadas em repouso.
     */
    public function setObsAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['obs'] = $value;
            return;
        }

        $this->attributes['obs'] = Crypt::encryptString((string) $value);
    }

    /**
     * Data e hora completa do agendamento
     */
    public function getDateTimeAttribute()
    {
        if ($this->date && $this->time) {
            return Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->time);
        }
        return null;
    }

    /**
     * Status formatado
     */
    public function getStatusFormattedAttribute()
    {
        $statuses = [
            'scheduled' => 'Agendado',
            'confirmed' => 'Confirmado',
            'in_progress' => 'Em Andamento',
            'completed' => 'Concluído',
            'canceled' => 'Cancelado',
            'no_show' => 'Não Compareceu'
        ];

        return $statuses[$this->status] ?? 'Não Definido';
    }

    /**
     * Mutators
     */

    /**
     * Confirmar agendamento
     */
    public function confirm()
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now()
        ]);
    }

    /**
     * Cancelar agendamento
     */
    public function cancel($reason = null)
    {
        $this->update([
            'status' => 'canceled',
            'canceled_at' => now(),
            'cancellation_reason' => $reason
        ]);
    }

    /**
     * Marcar como concluído
     */
    public function complete()
    {
        $this->update([
            'status' => 'completed'
        ]);
    }
}
