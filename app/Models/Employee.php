<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'role',
        'photo',
        'cro',
        'specialty',
        'active',
        'hire_date',
        'birth_date',
        'address',
        'salary',
        'commission_rate',
    ];

    protected $casts = [
        'active' => 'boolean',
        'hire_date' => 'date',
        'birth_date' => 'date',
        'salary' => 'decimal:2',
        'commission_rate' => 'decimal:2',
    ];

    /**
     * Relacionamentos
     */

    /**
     * Agendamentos do profissional
     */
    public function agendamentos()
    {
        return $this->hasMany(Scheduling::class, 'professional_id');
    }

    /**
     * Usuário associado ao funcionário
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */

    /**
     * Funcionários ativos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Funcionários por cargo
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Dentistas
     */
    public function scopeDentists($query)
    {
        return $query->where('role', 'dentist');
    }

    /**
     * Accessors
     */

    /**
     * Nome formatado
     */
    public function getFormattedNameAttribute()
    {
        return ucwords(strtolower($this->name));
    }

    /**
     * Cargo formatado
     */
    public function getRoleFormattedAttribute()
    {
        $roles = [
            'dentist' => 'Dentista',
            'hygienist' => 'Higienista',
            'assistant' => 'Auxiliar',
            'receptionist' => 'Recepcionista',
            'admin' => 'Administrador',
            'secretary' => 'Secretário(a)'
        ];

        return $roles[$this->role] ?? ucfirst($this->role);
    }

    /**
     * Especialidade formatada
     */
    public function getSpecialtyFormattedAttribute()
    {
        $specialties = [
            'general' => 'Clínica Geral',
            'orthodontics' => 'Ortodontia',
            'endodontics' => 'Endodontia',
            'periodontics' => 'Periodontia',
            'oral_surgery' => 'Cirurgia Oral',
            'prosthodontics' => 'Prótese',
            'pediatric' => 'Odontopediatria',
            'implants' => 'Implantodontia'
        ];

        return $specialties[$this->specialty] ?? ucfirst($this->specialty);
    }

    /**
     * Métodos auxiliares
     */

    /**
     * Verifica se é dentista
     */
    public function isDentist()
    {
        return $this->role === 'dentist';
    }

    /**
     * Verifica se está ativo
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Agendamentos de hoje
     */
    public function agendamentosHoje()
    {
        return $this->agendamentos()->whereDate('date', today());
    }

    /**
     * Próximo agendamento
     */
    public function proximoAgendamento()
    {
        return $this->agendamentos()
                    ->where('date', '>=', now())
                    ->oldest('date')
                    ->first();
    }
}
