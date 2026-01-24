<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Profissional;

class Consulta extends Model
{
    protected $table = 'consultas';

    protected $fillable = [
        'item_anamnese_id',
        'paciente_id',
        'data',
        'hora',
        'observacoes',
        'status',
        'valor',
        'profissional_id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'data' => 'date',
        'hora' => 'string',
        'valor' => 'float',
        'status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Consulta pertence a um item de anamnese
    public function itemAnamnese()
    {
        return $this->belongsTo(ItemAnamnese::class, 'item_anamnese_id');
    }

    // Consulta pertence a um paciente
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    // Consulta pertence a um profissional
    public function profissional()
    {
        return $this->belongsTo(Profissional::class, 'profissional_id');
    }

    // Regra de negócio: verifica se a consulta está confirmada
    public function isConfirmada()
    {
        return $this->status === 'confirmada';
    }

    // Regra de negócio: verifica se a consulta está cancelada
    public function isCancelada()
    {
        return $this->status === 'cancelada';
    }

    // Regra de negócio: retorna o valor formatado
    public function valorFormatado()
    {
        return 'R$ ' . number_format($this->valor, 2, ',', '.');
    }
}
