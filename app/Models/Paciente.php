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
        'cidade',
        'observacoes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'data_nascimento' => 'date',
        'idade' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope para buscar pacientes por nome
     */
    public function scopeByName($query, $name)
    {
        return $query->where('name', 'like', "%{$name}%");
    }

    /**
     * Scope para buscar pacientes por convênio
     */
    public function scopeByConvenio($query, $convenio)
    {
        return $query->where('convenio', $convenio);
    }

    /**
     * Scope para buscar pacientes por idade
     */
    public function scopeByAgeRange($query, $minAge, $maxAge)
    {
        return $query->whereBetween('idade', [$minAge, $maxAge]);
    }

    /**
     * Accessor para idade calculada baseada na data de nascimento
     */
    public function getCalculatedAgeAttribute()
    {
        if ($this->data_nascimento) {
            return now()->diffInYears($this->data_nascimento);
        }
        return $this->idade;
    }

    /**
     * Accessor para nome completo com tratamento
     */
    public function getFullNameAttribute()
    {
        return ucwords(strtolower($this->name));
    }

    /**
     * Accessor para endereço completo
     */
    public function getFullAddressAttribute()
    {
        $address = $this->rua;
        
        if ($this->numero) {
            $address .= ', ' . $this->numero;
        }
        
        if ($this->complemento) {
            $address .= ', ' . $this->complemento;
        }
        
        if ($this->bairro) {
            $address .= ' - ' . $this->bairro;
        }
        
        if ($this->cidade) {
            $address .= ', ' . $this->cidade;
        }
        
        if ($this->estado) {
            $address .= ' - ' . $this->estado;
        }
        
        if ($this->cep) {
            $address .= ' CEP: ' . $this->cep;
        }
        
        return $address;
    }

    /**
     * Relacionamentos
     */

    /**
     * Agendamentos do paciente
     */
    public function agendamentos()
    {
        return $this->hasMany(Scheduling::class, 'patient_id');
    }

    /**
     * Anamneses do paciente
     */
    public function anamneses()
    {
        return $this->hasMany(Anamnese::class, 'patient_id');
    }

    /**
     * Procedimentos realizados pelo paciente
     */
    public function procedimentos()
    {
        return $this->belongsToMany(Procedure::class, 'patient_procedures')
                    ->withPivot('scheduling_id', 'date', 'status', 'value')
                    ->withTimestamps();
    }

    /**
     * Último agendamento do paciente
     */
    public function ultimoAgendamento()
    {
        return $this->hasOne(Scheduling::class, 'patient_id')->latest('date');
    }

    /**
     * Próximo agendamento do paciente
     */
    public function proximoAgendamento()
    {
        return $this->hasOne(Scheduling::class, 'patient_id')
                    ->where('date', '>=', now())
                    ->oldest('date');
    }
}
