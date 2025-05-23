<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'cnpj',
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
}
