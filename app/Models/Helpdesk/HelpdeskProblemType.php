<?php

namespace App\Models\Helpdesk;

use Illuminate\Database\Eloquent\Model;

class HelpdeskProblemType extends Model
{
    protected $table = 'helpdesk_problem_types';

    protected $fillable = ['nome','codigo','categoria','descricao','status'];
}
