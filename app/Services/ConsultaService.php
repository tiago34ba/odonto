<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ConsultaService
{
    /**
     * Realiza o registro de uma consulta, com anamnese e procedimentos.
     */
    public function registrarConsulta($agendamentoId, array $dados)
    {
        $agendamento = \App\Models\Agendamento::findOrFail($agendamentoId);
        if ($agendamento->status !== \App\Models\Agendamento::STATUS_CONFIRMADO) {
            throw new \Exception('A consulta só pode ser registrada para agendamentos confirmados.');
        }

        return DB::transaction(function () use ($agendamento, $dados) {
            // Registrar anamnese
            if (!empty($dados['anamnese'])) {
                $anamnese = new \App\Models\Anamnese($dados['anamnese']);
                $anamnese->paciente_id = $agendamento->paciente_id;
                $anamnese->save();
            }
            // Registrar procedimentos realizados
            if (!empty($dados['procedimentos'])) {
                foreach ($dados['procedimentos'] as $procedimentoId) {
                    $agendamento->procedimentos()->attach($procedimentoId, [
                        'status' => 'realizado',
                        'date' => now(),
                    ]);
                }
            }
            // Registrar prescrições (se houver)
            // ...
            $agendamento->status = \App\Models\Agendamento::STATUS_EM_ANDAMENTO;
            $agendamento->save();
            return $agendamento;
        });
    }

    /**
     * Atualiza informações da consulta (evolução, anexos, etc).
     */
    public function atualizarConsulta($consultaId, array $dados)
    {
        $agendamento = \App\Models\Agendamento::findOrFail($consultaId);
        // Atualizar evolução, anexos, prescrições
        return DB::transaction(function () use ($agendamento, $dados) {
            $agendamento->update($dados);
            // Atualizar outros registros relacionados se necessário
            return $agendamento;
        });
    }

    /**
     * Finaliza consulta, liberando para faturamento.
     */
    public function finalizarConsulta($consultaId)
    {
        $agendamento = \App\Models\Agendamento::findOrFail($consultaId);
        if ($agendamento->status !== \App\Models\Agendamento::STATUS_EM_ANDAMENTO) {
            throw new \Exception('A consulta só pode ser finalizada se estiver em andamento.');
        }
        return DB::transaction(function () use ($agendamento) {
            $agendamento->status = \App\Models\Agendamento::STATUS_FINALIZADO;
            $agendamento->save();
            // Liberar para financeiro: gerar cobrança se necessário
            // ...
            return $agendamento;
        });
    }

    // Outras regras de negócio relevantes...
}
