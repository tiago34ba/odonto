<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaasSolicitacao extends Model
{
    use HasFactory;

    protected $table = 'saas_solicitacoes';

    protected $fillable = [
        'nome_clinica',
        'responsavel',
        'email',
        'telefone',
        'cnpj',
        'cidade',
        'estado',
        'plano_id',
        'status',
        'pagamento_confirmado',
        'data_pagamento',
        'comprovante_pagamento',
        'observacoes',
        'motivo_rejeicao',
        'aprovado_por',
        'aprovado_em',
        'rejeitado_por',
        'rejeitado_em',
    ];

    protected $casts = [
        'pagamento_confirmado' => 'boolean',
        'data_pagamento'       => 'datetime',
        'aprovado_em'          => 'datetime',
        'rejeitado_em'         => 'datetime',
    ];

    // ── Regras de negócio ─────────────────────────────────────

    /** Plano com label e valor mensal oficial do Odonto */
    public static function planosDisponiveis(): array
    {
        return [
            'basico'       => ['label' => 'Basico',       'valor' => 70.00],
            'profissional' => ['label' => 'Profissional', 'valor' => 90.00],
            'premium'      => ['label' => 'Premium',      'valor' => 160.00],
        ];
    }

    public function planoLabel(): string
    {
        return static::planosDisponiveis()[$this->plano_id]['label'] ?? ucfirst($this->plano_id);
    }

    public function valorMensal(): float
    {
        return static::planosDisponiveis()[$this->plano_id]['valor'] ?? 0;
    }

    public function isPendente(): bool
    {
        return $this->status === 'pendente';
    }

    public function isAguardandoPagamento(): bool
    {
        return $this->status === 'aguardando_pagamento';
    }

    public function isAprovada(): bool
    {
        return $this->status === 'aprovada';
    }

    public function isRejeitada(): bool
    {
        return $this->status === 'rejeitada';
    }
}
