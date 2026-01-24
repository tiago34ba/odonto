<?php

namespace App\Services;

use App\Models\Procedimento;
use App\Models\Agendamento;

class ProcedimentoService
{
    /**
     * Cadastra um novo procedimento com validações e regras de negócio.
     */
    public function cadastrar(array $dados)
    {
        // Validação de dados obrigatórios
        $required = ['nome', 'codigo', 'valor'];
        foreach ($required as $field) {
            if (empty($dados[$field])) {
                throw new \InvalidArgumentException("Campo obrigatório: $field");
            }
        }

        // Verificação de duplicidade (nome, código)
        if (Procedimento::where('nome', $dados['nome'])->exists()) {
            throw new \Exception('Já existe um procedimento com este nome.');
        }
        if (Procedimento::where('codigo', $dados['codigo'])->exists()) {
            throw new \Exception('Já existe um procedimento com este código.');
        }

        // Criação do procedimento
        return Procedimento::create([
            'nome' => $dados['nome'],
            'codigo' => $dados['codigo'],
            'valor' => $dados['valor'],
            'descricao' => $dados['descricao'] ?? null,
            'ativo' => true,
        ]);
    }

    /**
     * Atualiza dados do procedimento.
     */
    public function atualizar($procedimentoId, array $dados)
    {
        $procedimento = Procedimento::findOrFail($procedimentoId);

        // Validação de duplicidade
        if (isset($dados['nome']) && Procedimento::where('nome', $dados['nome'])->where('id', '!=', $procedimentoId)->exists()) {
            throw new \Exception('Já existe um procedimento com este nome.');
        }
        if (isset($dados['codigo']) && Procedimento::where('codigo', $dados['codigo'])->where('id', '!=', $procedimentoId)->exists()) {
            throw new \Exception('Já existe um procedimento com este código.');
        }

        $procedimento->fill($dados);
        $procedimento->save();
        return $procedimento;
    }

    /**
     * Inativa procedimento, considerando vínculos (agendamentos futuros).
     */
    public function inativar($procedimentoId)
    {
        $procedimento = Procedimento::findOrFail($procedimentoId);

        // Verificar vínculos com agendamentos futuros
        $agendamentosFuturos = Agendamento::where('procedimento_id', $procedimentoId)
            ->where('data', '>=', now()->toDateString())
            ->count();

        if ($agendamentosFuturos > 0) {
            throw new \Exception('Não é possível inativar: procedimento possui agendamentos futuros.');
        }

        $procedimento->ativo = false;
        $procedimento->save();
        return $procedimento;
    }
}
