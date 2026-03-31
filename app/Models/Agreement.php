<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Agreement extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'name',
        'tipo',
        'cnpj',
        'phone',
        'email',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'uf',
        'cep',
        'contato_responsavel',
        'clinic_commission',
        'desconto_percentual',
        'ativo',
        'observacoes',
    ];

    protected $casts = [
        'clinic_commission' => 'decimal:2',
        'desconto_percentual' => 'decimal:2',
        'ativo' => 'boolean',
    ];
}
