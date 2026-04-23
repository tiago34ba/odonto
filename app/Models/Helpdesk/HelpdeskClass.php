<?php

namespace App\Models\Helpdesk;

use Illuminate\Database\Eloquent\Model;

class HelpdeskClass extends Model
{
    protected $table = 'helpdesk_classes';

    protected $fillable = ['nome','codigo','descricao','regra_fila','status'];

    protected $casts = ['regra_fila' => 'array'];
}
