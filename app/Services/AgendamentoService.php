<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AgendamentoService
{
    /**
     * Agenda uma nova consulta, validando conflitos de horário e disponibilidade.
     */
    public function agendar(array $dados)
    {
        Validator::make($dados, [
            'paciente_id' => 'required|exists:pacientes,id',
            'funcionario_id' => 'required|exists:users,id',
            'procedimento_id' => 'required|exists:procedimentos,id',
            'data_agendamento' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fim' => 'required',
        ])->validate();

        // Verifica conflitos de horário para o profissional
        $conflito = \App\Models\Agendamento::where('funcionario_id', $dados['funcionario_id'])
            ->where('data_agendamento', $dados['data_agendamento'])
            ->where(function($q) use ($dados) {
                $q->whereBetween('hora_inicio', [$dados['hora_inicio'], $dados['hora_fim']])
                  ->orWhereBetween('hora_fim', [$dados['hora_inicio'], $dados['hora_fim']]);
            })->exists();
        if ($conflito) {
            throw new \Exception('Conflito de horário para o profissional.');
        }

        return DB::transaction(function () use ($dados) {
            return \App\Models\Agendamento::create($dados);
        });
    }

    /**
     * Reagendar consulta, mantendo histórico.
     */
    public function reagendar($agendamentoId, array $dados)
    {
        $agendamento = \App\Models\Agendamento::findOrFail($agendamentoId);

        Validator::make($dados, [
            'data_agendamento' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fim' => 'required',
        ])->validate();

        // Verifica conflitos de horário para o profissional
        $conflito = \App\Models\Agendamento::where('funcionario_id', $agendamento->funcionario_id)
            ->where('data_agendamento', $dados['data_agendamento'])
            ->where('id', '!=', $agendamentoId)
            ->where(function($q) use ($dados) {
                $q->whereBetween('hora_inicio', [$dados['hora_inicio'], $dados['hora_fim']])
                  ->orWhereBetween('hora_fim', [$dados['hora_inicio'], $dados['hora_fim']]);
            })->exists();
        if ($conflito) {
            throw new \Exception('Conflito de horário para o profissional.');
        }

        return DB::transaction(function () use ($agendamento, $dados) {
            $agendamento->update($dados);
            // Aqui pode-se registrar histórico de reagendamento
            return $agendamento;
        });
    }

    /**
     * Cancelar agendamento, com regras de aviso prévio e possíveis cobranças.
     */
    public function cancelar($agendamentoId)
    {
        $agendamento = \App\Models\Agendamento::findOrFail($agendamentoId);
        if (!$agendamento->podeSerCancelado()) {
            throw new \Exception('Agendamento não pode ser cancelado neste status.');
        }
        return DB::transaction(function () use ($agendamento) {
            $agendamento->status = \App\Models\Agendamento::STATUS_CANCELADO;
            $agendamento->save();
            // Aqui pode-se registrar histórico de cancelamento
            return $agendamento;
        });
    }

    // Outras regras de negócio relevantes...
}
