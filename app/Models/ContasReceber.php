<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ContasReceber extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'paciente_id',
        'procedure_id',
        'scheduling_id',
        'categoria',
        'valor_original',
        'valor_recebido',
        'valor_pendente',
        'data_vencimento',
        'data_recebimento',
        'status',
        'prioridade',
        'forma_pagamento',
        'convenio',
        'observacoes',
    ];

    protected $casts = [
        'valor_original' => 'decimal:2',
        'valor_recebido' => 'decimal:2',
        'valor_pendente' => 'decimal:2',
        'data_vencimento' => 'date',
        'data_recebimento' => 'date',
    ];

    /**
     * Boot method para gerar código automaticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->codigo) {
                $model->codigo = self::gerarCodigo();
            }
        });
    }

    /**
     * Relacionamentos
     */
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function procedure()
    {
        return $this->belongsTo(Procedure::class);
    }

    public function scheduling()
    {
        return $this->belongsTo(Scheduling::class);
    }

    public function fluxoCaixa()
    {
        return $this->hasMany(FluxoCaixa::class, 'conta_receber_id');
    }

    /**
     * Scopes
     */
    public function scopePendentes($query)
    {
        return $query->whereIn('status', ['Pendente', 'Parcial']);
    }

    public function scopeVencidas($query)
    {
        return $query->where('data_vencimento', '<', now()->toDateString())
                    ->whereIn('status', ['Pendente', 'Parcial']);
    }

    public function scopeVencendoEm($query, $dias)
    {
        return $query->whereBetween('data_vencimento', [
            now()->toDateString(),
            now()->addDays($dias)->toDateString()
        ])->whereIn('status', ['Pendente', 'Parcial']);
    }

    public function scopeRecebidas($query)
    {
        return $query->where('status', 'Recebido');
    }

    /**
     * Métodos auxiliares
     */
    public function isVencida()
    {
        return $this->data_vencimento < now()->toDateString() && 
               in_array($this->status, ['Pendente', 'Parcial']);
    }

    public function diasAteVencimento()
    {
        return now()->diffInDays($this->data_vencimento, false);
    }

    public static function gerarCodigo()
    {
        $ultimoCodigo = self::latest('id')->first();
        $numero = $ultimoCodigo ? $ultimoCodigo->id + 1 : 1;
        return 'CR' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }
}