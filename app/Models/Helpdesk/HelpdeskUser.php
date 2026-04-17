<?php

namespace App\Models\Helpdesk;

use Illuminate\Database\Eloquent\Model;

class HelpdeskUser extends Model
{
    protected $table = 'helpdesk_users';

    protected $fillable = ['nome','codigo','tipo','email','telefone','empresa','departamento','cargo','status','photo_url','senha_temporaria','senha_enviada_em','password_reset_required','metadata'];

    protected $casts = ['senha_enviada_em' => 'datetime', 'password_reset_required' => 'boolean', 'metadata' => 'array'];
}
