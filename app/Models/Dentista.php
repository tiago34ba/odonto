<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dentista extends Model
{
    use HasFactory;

    protected $table = 'dentistas';

    protected $fillable = [
        'name',
        'email',
        'telefone',
        'celular',
        'cpf',
        'cro',
        'cro_uf',
        'especialidade',
        'data_nascimento',
        'data_cadastro',
        'intervalo_consulta',
        'horarios_atendimento',
        'chave_pix',
        'cep',
        'rua',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'status',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'data_cadastro' => 'date',
        'intervalo_consulta' => 'integer',
        'horarios_atendimento' => 'array',
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeAtivos($query)
    {
        return $query->where('status', true);
    }

    public function scopeByName($query, string $name)
    {
        return $query->where('name', 'like', "%{$name}%");
    }

    public function scopeByEspecialidade($query, string $especialidade)
    {
        return $query->where('especialidade', $especialidade);
    }
}
