<?php

namespace App\Models\Helpdesk;

use Illuminate\Database\Eloquent\Model;

class HelpdeskAttendance extends Model
{
    protected $table = 'helpdesk_attendances';

    protected $fillable = ['ticket_id','tecnico_id','solution_id','nome','codigo','tipo_atendimento','descricao','status','iniciado_em','finalizado_em','horas_trabalhadas','custo','anexos'];

    protected $casts = ['iniciado_em' => 'datetime','finalizado_em' => 'datetime','anexos' => 'array'];
}
