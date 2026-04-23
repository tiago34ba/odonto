<?php

namespace App\Models\Helpdesk;

use Illuminate\Database\Eloquent\Model;

class HelpdeskSolution extends Model
{
    protected $table = 'helpdesk_solutions';

    protected $fillable = ['problem_id','nome','codigo','descricao','passos','kb_article_url','tempo_medio_min','efetividade_score','status'];
}
