<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scheduling extends Model
{
    protected $fillable = [
        'patient_id',
        'professional_id',
        'procedure_id',
        'date',
        'time',
        'return',
        'obs',
    ];
}
