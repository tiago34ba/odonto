<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fornecedor extends Model
{
    protected $table = 'fornecedores';

    protected $fillable = [
        'nome',         // Nome do fornecedor
        'cnpj',         // CNPJ do fornecedor
        'email',        // E-mail do fornecedor
        'telefone',     // Telefone de contato
        'endereco',     // Endereço completo
        'ativo',        // Status do fornecedor
        'observacao',   // Observações adicionais
    ];

    // Relacionamento: fornecedor possui várias compras
    public function compras()
    {
        return $this->hasMany(Compra::class, 'fornecedor_id');
    }

    // Relacionamento: fornecedor possui várias contas a pagar
    public function contasPagar()
    {
        return $this->hasMany(ContaPagar::class, 'fornecedor_id');
    }

    // Escopo para fornecedores ativos
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    // Regra de negócio: verifica se pode ser inativado
    public function podeInativar()
    {
        return $this->compras()->count() === 0 && $this->contasPagar()->count() === 0;
    }
}
