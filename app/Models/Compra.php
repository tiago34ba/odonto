<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $table = 'compras';

    protected $fillable = [
        'fornecedor_id',    // Relacionamento com Fornecedor
        'data_compra',      // Data da compra
        'valor_total',      // Valor total da compra
        'status',           // Status: 'pendente', 'paga', 'cancelada'
        'observacao',       // Observações adicionais
    ];

    // Relacionamento: compra pertence a um fornecedor
    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }

    // Escopo para compras pendentes
    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    // Regra de negócio: verifica se a compra está atrasada
    public function getAtrasadaAttribute()
    {
        // Exemplo: considera atrasada se status pendente e data_compra anterior a hoje
        return $this->status === 'pendente' && $this->data_compra < now()->toDateString();
    }
}
