<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    protected $fillable = [
        'nome',
        'email',
        'cpf',
        'telefone',
        'cargo_id',
        'data_admissao',
        'data_demissao',
        'ativo'
    ];

    // Regras de negócio: nome, email e CPF obrigatórios e únicos, cargo válido
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->nome)) {
                throw new \InvalidArgumentException('O nome do funcionário é obrigatório.');
            }
            if (empty($model->email)) {
                throw new \InvalidArgumentException('O e-mail do funcionário é obrigatório.');
            }
            if (empty($model->cpf)) {
                throw new \InvalidArgumentException('O CPF do funcionário é obrigatório.');
            }
            if (Funcionario::where('email', $model->email)->exists()) {
                throw new \Exception('Já existe um funcionário com este e-mail.');
            }
            if (Funcionario::where('cpf', $model->cpf)->exists()) {
                throw new \Exception('Já existe um funcionário com este CPF.');
            }
            if (empty($model->cargo_id)) {
                throw new \InvalidArgumentException('O cargo do funcionário é obrigatório.');
            }
        });

        static::updating(function ($model) {
            if (isset($model->email) && Funcionario::where('email', $model->email)->where('id', '!=', $model->id)->exists()) {
                throw new \Exception('Já existe um funcionário com este e-mail.');
            }
            if (isset($model->cpf) && Funcionario::where('cpf', $model->cpf)->where('id', '!=', $model->id)->exists()) {
                throw new \Exception('Já existe um funcionário com este CPF.');
            }
        });
    }

    // Relacionamento com Cargo
    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }
}
