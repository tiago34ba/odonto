<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profissional extends Model
{
    protected $table = 'profissionais';

    protected $fillable = [
        'nome',
        'cpf',
        'cro',
        'especialidade',
        'email',
        'telefone',
        'data_nascimento',
        'ativo',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'ativo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Regras de negócio

    // Verifica se o profissional está ativo
    public function isAtivo()
    {
        return $this->ativo;
    }

    // Retorna o nome formatado
    public function nomeFormatado()
    {
        return ucwords(strtolower($this->nome));
    }

    // Retorna a especialidade do profissional
    public function especialidade()
    {
        return $this->especialidade;
    }

    // Relacionamento: Profissional possui muitas consultas
    public function consultas()
    {
        return $this->hasMany(Consulta::class, 'profissional_id');
    }

    // Validação simples do CPF (apenas tamanho)
    public function isCpfValido()
    {
        return strlen(preg_replace('/\D/', '', $this->cpf)) === 11;
    }
}
