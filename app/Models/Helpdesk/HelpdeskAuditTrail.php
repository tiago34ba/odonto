<?php

namespace App\Models\Helpdesk;

use Illuminate\Database\Eloquent\Model;

class HelpdeskAuditTrail extends Model
{
    protected $table = 'helpdesk_audit_trails';

    protected $fillable = ['entity_type','entity_id','acao','codigo','nome','actor_nome','actor_email','gestor_email','ip_address','user_agent','payload_before','payload_after','descricao','status'];

    protected $casts = ['payload_before' => 'array', 'payload_after' => 'array'];
}
