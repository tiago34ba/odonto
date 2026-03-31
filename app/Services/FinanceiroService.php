<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class FinanceiroService
{
    /**
     * Gera cobrança para consulta ou procedimento.
     */
    public function gerarCobranca($pacienteId, $valor, $descricao, $schedulingId = null, $procedureId = null)
    {
        Validator::make([
            'paciente_id' => $pacienteId,
            'valor' => $valor,
        ], [
            'paciente_id' => 'required|exists:pacientes,id',
            'valor' => 'required|numeric|min:0.01',
        ])->validate();

        return DB::transaction(function () use ($pacienteId, $valor, $descricao, $schedulingId, $procedureId) {
            $cobranca = \App\Models\ContasReceber::create([
                'paciente_id' => $pacienteId,
                'valor_original' => $valor,
                'valor_pendente' => $valor,
                'descricao' => $descricao,
                'scheduling_id' => $schedulingId,
                'procedure_id' => $procedureId,
                'status' => 'pendente',
                'data_vencimento' => now()->addDays(7),
            ]);
            return $cobranca;
        });
    }

    /**
     * Realiza baixa de pagamento, validando valores e datas.
     */
    public function registrarPagamento($cobrancaId, $valorPago, $dataPagamento)
    {
        $cobranca = \App\Models\ContasReceber::findOrFail($cobrancaId);
        Validator::make([
            'valorPago' => $valorPago,
            'dataPagamento' => $dataPagamento,
        ], [
            'valorPago' => 'required|numeric|min:0.01',
            'dataPagamento' => 'required|date',
        ])->validate();

        return DB::transaction(function () use ($cobranca, $valorPago, $dataPagamento) {
            $cobranca->valor_recebido += $valorPago;
            $cobranca->valor_pendente = max(0, $cobranca->valor_original - $cobranca->valor_recebido);
            $cobranca->data_recebimento = $dataPagamento;
            $cobranca->status = $cobranca->valor_pendente <= 0 ? 'pago' : 'parcial';
            $cobranca->save();

            // Lançar no fluxo de caixa
            \App\Models\FluxoCaixa::create([
                'tipo' => 'entrada',
                'descricao' => 'Recebimento: ' . $cobranca->descricao,
                'valor' => $valorPago,
                'data_movimento' => $dataPagamento,
                'conta_receber_id' => $cobranca->id,
                'paciente_id' => $cobranca->paciente_id,
            ]);

            return $cobranca;
        });
    }

    /**
     * Gera relatórios financeiros (recebidos, pendentes, inadimplentes).
     */
    public function gerarRelatorio(array $filtros)
    {
        $query = \App\Models\ContasReceber::query();
        if (!empty($filtros['status'])) {
            $query->where('status', $filtros['status']);
        }
        if (!empty($filtros['paciente_id'])) {
            $query->where('paciente_id', $filtros['paciente_id']);
        }
        if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
            $query->whereBetween('data_vencimento', [$filtros['data_inicio'], $filtros['data_fim']]);
        }
        return $query->get();
    }

    // Outras regras de negócio relevantes...
}
