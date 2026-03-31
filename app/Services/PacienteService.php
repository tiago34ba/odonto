<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PacienteService
{
    /**
     * Cadastra um novo paciente com validações de dados e duplicidade.
     */
    public function cadastrar(array $dados)
    {
        Validator::make($dados, [
            'name' => 'required|string|max:255',
            'cpf_cnpj' => 'required|string|unique:pacientes,cpf_cnpj',
            'email' => 'nullable|email|unique:pacientes,email',
            'data_nascimento' => 'nullable|date',
        ])->validate();

        return DB::transaction(function () use ($dados) {
            return \App\Models\Paciente::create($dados);
        });
    }

    /**
     * Atualiza dados do paciente com regras de integridade.
     */
    public function atualizar($pacienteId, array $dados)
    {
        $paciente = \App\Models\Paciente::findOrFail($pacienteId);

        Validator::make($dados, [
            'name' => 'sometimes|required|string|max:255',
            'cpf_cnpj' => 'sometimes|required|string|unique:pacientes,cpf_cnpj,' . $pacienteId,
            'email' => 'nullable|email|unique:pacientes,email,' . $pacienteId,
            'data_nascimento' => 'nullable|date',
        ])->validate();

        return DB::transaction(function () use ($paciente, $dados) {
            $paciente->update($dados);
            return $paciente;
        });
    }

    /**
     * Inativa ou exclui paciente, considerando vínculos (consultas, financeiro).
     */
    public function inativar($pacienteId)
    {
        $paciente = \App\Models\Paciente::findOrFail($pacienteId);

        // Verifica vínculos ativos (consultas futuras, financeiro pendente)
        $temAgendamentos = $paciente->agendamentos()->where('date', '>=', now())->exists();
        $temFinanceiro = \App\Models\ContasReceber::where('paciente_id', $pacienteId)
            ->whereNull('data_recebimento')->exists();
        if ($temAgendamentos || $temFinanceiro) {
            throw new \Exception('Paciente possui agendamentos futuros ou pendências financeiras.');
        }

        $paciente->ativo = false;
        $paciente->save();
        return $paciente;
    }

    // Outras regras de negócio relevantes...
}
