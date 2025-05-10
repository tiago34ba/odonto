<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'phone',
        'insurance',
        'age',
        'nascimento',
        'responsavel',
        'cpfResponsavel',
        'pessoa',
        'cpfCnpj',
        'email',
        'cep',
        'rua',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'tipoSanguineo',
        'sexo',
        'profissao',
        'estadoCivil',
        'telefone2',
        'observacoes',
    ];
}
