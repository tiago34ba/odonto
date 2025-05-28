<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormaPagamento extends Model
{
    protected $table = 'forma_pagamentos';

    protected $fillable = [
        'nome',
        'taxa',
    ];
}
