<?php

namespace App\Models\Helpdesk;

use Illuminate\Database\Eloquent\Model;

class HelpdeskArea extends Model
{
    protected $table = 'helpdesk_areas';

    protected $fillable = ['nome','codigo','descricao','gestor_nome','gestor_email','sla_padrao_horas','escalation_rules','status'];

    protected $casts = ['escalation_rules' => 'array'];
}
