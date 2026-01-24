<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    protected $fillable = [
        'nome',
        'descricao',
        'nivel_acesso', // Ex: administrativo, clínico, financeiro, etc.
        'ativo'
    ];

    // Regras de negócio: nome obrigatório e único, nível de acesso válido
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->nome)) {
                throw new \InvalidArgumentException('O nome do cargo é obrigatório.');
            }
            if (Cargo::where('nome', $model->nome)->exists()) {
                throw new \Exception('Já existe um cargo com este nome.');
            }
            $niveisValidos = ['administrativo', 'clinico', 'financeiro', 'gerencial'];
            if (!in_array($model->nivel_acesso, $niveisValidos)) {
                throw new \InvalidArgumentException('Nível de acesso inválido.');
            }
        });

        static::updating(function ($model) {
            if (isset($model->nome) && Cargo::where('nome', $model->nome)->where('id', '!=', $model->id)->exists()) {
                throw new \Exception('Já existe um cargo com este nome.');
            }
            $niveisValidos = ['administrativo', 'clinico', 'financeiro', 'gerencial'];
            if (isset($model->nivel_acesso) && !in_array($model->nivel_acesso, $niveisValidos)) {
                throw new \InvalidArgumentException('Nível de acesso inválido.');
            }
        });
    }
}
