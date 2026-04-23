<?php

namespace App\Models\Helpdesk;

use Illuminate\Database\Eloquent\Model;

class HelpdeskPreRegistration extends Model
{
    protected $table = 'helpdesk_pre_registrations';

    protected $fillable = ['area_id','nome','codigo','tipo','email','telefone','empresa','status','senha_temporaria','senha_enviada_em','observacoes'];

    protected $casts = ['senha_enviada_em' => 'datetime'];
}
