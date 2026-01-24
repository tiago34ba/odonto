<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GroupAnamnese extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'descricao',
    ];
}
