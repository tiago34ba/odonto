<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContaPagar extends Model
{
    protected $table = 'contas_pagar';

    protected $fillable = [
        'fornecedor_id',     // Relacionamento com Fornecedor
        'descricao',         // Descrição da conta
        'valor',             // Valor a pagar
        'data_vencimento',   // Data de vencimento
        'data_pagamento',    // Data do pagamento
        'status',            // Status: 'pendente', 'paga', 'cancelada'
        'observacao',        // Observações adicionais
    ];

    // Relacionamento: conta a pagar pertence a um fornecedor
    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }

    // Escopo para contas pendentes
    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    // Regra de negócio: verifica se está atrasada
    public function getAtrasadaAttribute()
    {
        return $this->status === 'pendente' && $this->data_vencimento < now()->toDateString();
    }
}
