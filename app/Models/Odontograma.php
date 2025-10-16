<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Odontograma extends Model
{
    use HasFactory;

    protected $fillable = [
        'paciente_id',
        'dentes',
        'observacoes_gerais',
    ];

    protected $casts = [
        'dentes' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento com Paciente
     */
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    /**
     * Accessor para dentes formatados
     */
    public function getDentesFormatadosAttribute()
    {
        if (!$this->dentes) {
            return [];
        }

        $dentesArray = is_string($this->dentes) ? json_decode($this->dentes, true) : $this->dentes;
        
        return collect($dentesArray)->map(function ($dente) {
            return [
                'numero' => $dente['numero'],
                'faces' => $dente['faces'] ?? [],
                'status' => $dente['status'] ?? 'normal',
                'observacoes' => $dente['observacoes'] ?? null,
                'tipo' => $this->getTipoDente($dente['numero']),
                'posicao' => $this->getPosicaoDente($dente['numero']),
            ];
        });
    }

    /**
     * Obter tipo do dente (permanente ou decíduo)
     */
    public function getTipoDente($numero)
    {
        // Dentes decíduos: 51-65 e 71-85
        if (($numero >= 51 && $numero <= 65) || ($numero >= 71 && $numero <= 85)) {
            return 'deciduo';
        }
        
        return 'permanente';
    }

    /**
     * Obter posição do dente no arco
     */
    public function getPosicaoDente($numero)
    {
        $primeiroDigito = intval(substr($numero, 0, 1));
        
        switch ($primeiroDigito) {
            case 1:
                return 'superior_direito';
            case 2:
                return 'superior_esquerdo';
            case 3:
                return 'inferior_esquerdo';
            case 4:
                return 'inferior_direito';
            case 5:
                return ($numero >= 51 && $numero <= 55) ? 'superior_direito_deciduo' : 'superior_esquerdo_deciduo';
            case 6:
                return 'superior_esquerdo_deciduo';
            case 7:
                return 'inferior_esquerdo_deciduo';
            case 8:
                return 'inferior_direito_deciduo';
            default:
                return 'indefinido';
        }
    }

    /**
     * Obter contagem de dentes por status
     */
    public function getContagemStatusAttribute()
    {
        if (!$this->dentes) {
            return [];
        }

        $dentesArray = is_string($this->dentes) ? json_decode($this->dentes, true) : $this->dentes;
        
        $contagem = [
            'normal' => 0,
            'cariado' => 0,
            'restaurado' => 0,
            'extraido' => 0,
            'ausente' => 0,
            'implante' => 0,
            'protese' => 0,
        ];

        foreach ($dentesArray as $dente) {
            $status = $dente['status'] ?? 'normal';
            if (isset($contagem[$status])) {
                $contagem[$status]++;
            }
        }

        return $contagem;
    }

    /**
     * Verificar se possui dentes problemáticos
     */
    public function hasProblemas()
    {
        $contagem = $this->contagem_status;
        return $contagem['cariado'] > 0 || $contagem['extraido'] > 0;
    }

    /**
     * Obter total de dentes presentes
     */
    public function getTotalDentesPresentesAttribute()
    {
        $contagem = $this->contagem_status;
        return $contagem['normal'] + $contagem['cariado'] + $contagem['restaurado'] + $contagem['implante'] + $contagem['protese'];
    }

    /**
     * Scope para buscar odontogramas com problemas
     */
    public function scopeComProblemas($query)
    {
        return $query->whereRaw("JSON_SEARCH(dentes, 'one', 'cariado', NULL, '$[*].status') IS NOT NULL")
                    ->orWhereRaw("JSON_SEARCH(dentes, 'one', 'extraido', NULL, '$[*].status') IS NOT NULL");
    }

    /**
     * Scope para buscar por status específico
     */
    public function scopeComStatus($query, $status)
    {
        return $query->whereRaw("JSON_SEARCH(dentes, 'one', ?, NULL, '$[*].status') IS NOT NULL", [$status]);
    }
}