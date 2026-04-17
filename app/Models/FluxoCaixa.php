<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FluxoCaixa extends Model
{
    protected $table = 'fluxo_caixas';

    protected $fillable = [
        'tipo',             // 'entrada' ou 'saida'
        'categoria',        // Categoria da movimentação (ex: consulta, material, salário)
        'descricao',        // Descrição detalhada
        'valor',            // Valor da movimentação
        'data_movimento',   // Data da movimentação
        'observacoes',      // Observações adicionais
        'conta_pagar_id',   // FK para contas a pagar (nullable)
        'conta_receber_id', // FK para contas a receber (nullable)
    ];

    // Relacionamento: pode estar vinculado a uma conta a pagar
    public function contaPagar()
    {
        return $this->belongsTo(ContasPagar::class, 'conta_pagar_id');
    }

    // Relacionamento: pode estar vinculado a uma conta a receber
    public function contaReceber()
    {
        return $this->belongsTo(ContasReceber::class, 'conta_receber_id');
    }

    // Escopo para entradas
    public function scopeEntradas($query)
    {
        return $query->where('tipo', 'entrada');
    }

    // Escopo para saídas
    public function scopeSaidas($query)
    {
        return $query->where('tipo', 'saida');
    }
}
