<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormaPagamento extends Model
{
    protected $table = 'forma_pagamentos';

    protected $fillable = [
        'codigo',
        'nome',
        'tipo',
        'cor',
        'icone',
        'taxa',
        'taxa_juros',
        'parcelas_max',
        'dias_vencimento',
        'ativo',
        'aceita_parcelamento',
    ];

    protected $casts = [
        'taxa' => 'decimal:2',
        'taxa_juros' => 'decimal:2',
        'parcelas_max' => 'integer',
        'dias_vencimento' => 'integer',
        'ativo' => 'boolean',
        'aceita_parcelamento' => 'boolean',
    ];
}
