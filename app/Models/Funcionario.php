<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    use HasFactory;

    protected $table = 'funcionarios';

    protected $fillable = [
        'name',
        'telefone',
        'email',
        'cargo_id',
        'data_cadastro',
        'foto',
        'cep',
        'rua',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'cro',
        'intervalo',
        'comissao',
        'chave_pix',
        'status',
    ];

    protected $casts = [
        'data_cadastro' => 'date',
        'intervalo'     => 'integer',
        'comissao'      => 'decimal:2',
        'status'        => 'boolean',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    // Relacionamento com Cargo
    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }

    // Scopes
    public function scopeAtivos($query)
    {
        return $query->where('status', true);
    }

    public function scopeByName($query, $name)
    {
        return $query->where('name', 'like', "%{$name}%");
    }

    public function scopeByCargo($query, $cargoId)
    {
        return $query->where('cargo_id', $cargoId);
    }
}
