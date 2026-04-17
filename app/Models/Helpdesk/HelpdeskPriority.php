<?php

namespace App\Models\Helpdesk;

use Illuminate\Database\Eloquent\Model;

class HelpdeskPriority extends Model
{
    protected $table = 'helpdesk_priorities';

    protected $fillable = ['nome','codigo','cor','ordem','tempo_resposta_horas','tempo_resolucao_horas','descricao','status'];
}
