<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Agreement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'clinic_commission',
    ];

    protected $casts = [
        'clinic_commission' => 'decimal:2',
    ];
}
