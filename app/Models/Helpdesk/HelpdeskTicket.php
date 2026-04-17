<?php

namespace App\Models\Helpdesk;

use Illuminate\Database\Eloquent\Model;

class HelpdeskTicket extends Model
{
    protected $table = 'helpdesk_tickets';

    protected $fillable = ['numero','cliente_id','tecnico_id','area_id','priority_id','problem_id','problem_type_id','class_id','codigo','assunto','descricao','status','canal_origem','aberto_em','prazo_em','fechado_em','anexos','gestor_email','confirmado_cliente','confirmado_alteracao','copiado_de_ticket_id','export_metadata'];

    protected $casts = ['aberto_em' => 'datetime','prazo_em' => 'datetime','fechado_em' => 'datetime','anexos' => 'array','confirmado_cliente' => 'boolean','confirmado_alteracao' => 'boolean','export_metadata' => 'array'];
}
