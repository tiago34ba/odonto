<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreatmentPlan extends Model
{
    protected $fillable = [
        'numero',
        'paciente_id',
        'tipo',
        'valor',
        'status',
        'data',
        'dias_validade',
        'procedimentos',
        'desconto',
        'desconto_tipo',
        'forma_pagamento',
        'profissional',
        'odontograma',
        'observacoes',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data' => 'date',
        'dias_validade' => 'integer',
        'procedimentos' => 'array',
        'desconto' => 'decimal:2',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if (!$model->numero) {
                $ultimoId = self::query()->max('id') ?? 0;
                $model->numero = 'ORC-' . str_pad((string) ($ultimoId + 1), 6, '0', STR_PAD_LEFT);
            }
        });
    }
}
