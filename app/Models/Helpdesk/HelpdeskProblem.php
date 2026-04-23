<?php

namespace App\Models\Helpdesk;

use Illuminate\Database\Eloquent\Model;

class HelpdeskProblem extends Model
{
    protected $table = 'helpdesk_problems';

    protected $fillable = ['area_id','problem_type_id','nome','codigo','descricao','sintomas','impacto','workaround','status'];
}
