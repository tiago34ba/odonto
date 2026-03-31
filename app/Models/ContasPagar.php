<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ContasPagar extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'descricao',
        'supplier_id',
        'categoria',
        'valor_original',
        'valor_pago',
        'valor_pendente',
        'data_vencimento',
        'data_pagamento',
        'status',
        'prioridade',
        'forma_pagamento',
        'observacoes',
    ];

    protected $casts = [
        'valor_original' => 'decimal:2',
        'valor_pago' => 'decimal:2',
        'valor_pendente' => 'decimal:2',
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
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
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function fluxoCaixa()
    {
        return $this->hasMany(FluxoCaixa::class, 'conta_pagar_id');
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
        return 'CP' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }
}