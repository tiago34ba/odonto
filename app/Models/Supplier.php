<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'razao_social',
        'email',
        'phone',
        'cnpj',
        'tipo',
        'categoria',
        'contato',
        'status',
        'avaliacao',
        'pix',
        'pix_key_type',
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'cep',
    ];

    protected $casts = [
        'status' => 'integer',
        'avaliacao' => 'float',
    ];
}
