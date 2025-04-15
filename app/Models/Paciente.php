<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'convenio',
        'telefone',
        'idade',
        'data_nascimento',
        'responsavel',
        'cpf_responsavel',
        'celular',
        'estado',
        'sexo',
        'profissao',
        'estado_civil',
        'tipo_sanguineo',
        'pessoa',
        'cpf_cnpj',
        'email',
        'cep',
        'rua',
        'numero',
        'complemento',
        'bairro',
        'observacoes',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
    ];
}
