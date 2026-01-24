<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Financeiro extends Model
{
    protected $table = 'financeiros';

    protected $fillable = [
        'paciente_id',           // Relacionamento com paciente
        'forma_pagamento_id',    // Relacionamento com FormaPagamento
        'valor',                 // Valor do lançamento
        'data_vencimento',       // Data de vencimento
        'data_pagamento',        // Data do pagamento
        'descricao',             // Descrição do lançamento
        'tipo',                  // 'receita' ou 'despesa'
        'status',                // 'pendente', 'pago', 'cancelado'
        'observacao',            // Observações adicionais
    ];

    // Regras de negócio: exemplo de escopo para lançamentos pendentes
    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    // Relacionamento com paciente
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    // Relacionamento com forma de pagamento
    public function formaPagamento()
    {
        return $this->belongsTo(FormaPagamento::class);
    }

    // Regra de negócio: verificar se está atrasado
    public function getAtrasadoAttribute()
    {
        return $this->status === 'pendente' && $this->data_vencimento < now();
    }
}
