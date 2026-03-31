<?php

namespace App\Services;

use App\Models\Convenio;
use App\Models\Paciente;
use App\Models\Procedimento;

class ConvenioService
{
    /**
     * Cadastra um novo convênio com validações e regras de negócio.
     */
    public function cadastrar(array $dados)
    {
        // Validação de dados obrigatórios
        $required = ['nome', 'cnpj'];
        foreach ($required as $field) {
            if (empty($dados[$field])) {
                throw new \InvalidArgumentException("Campo obrigatório: $field");
            }
        }

        // Verificação de duplicidade (nome ou CNPJ)
        if (Convenio::where('nome', $dados['nome'])->orWhere('cnpj', $dados['cnpj'])->exists()) {
            throw new \Exception('Já existe um convênio com este nome ou CNPJ.');
        }

        // Criação do convênio
        return Convenio::create([
            'nome' => $dados['nome'],
            'cnpj' => $dados['cnpj'],
            'descricao' => $dados['descricao'] ?? null,
            'ativo' => true,
        ]);
    }

    /**
     * Atualiza dados do convênio.
     */
    public function atualizar($convenioId, array $dados)
    {
        $convenio = Convenio::findOrFail($convenioId);

        // Validação de nome e CNPJ únicos
        if (isset($dados['nome']) && Convenio::where('nome', $dados['nome'])->where('id', '!=', $convenioId)->exists()) {
            throw new \Exception('Já existe um convênio com este nome.');
        }
        if (isset($dados['cnpj']) && Convenio::where('cnpj', $dados['cnpj'])->where('id', '!=', $convenioId)->exists()) {
            throw new \Exception('Já existe um convênio com este CNPJ.');
        }

        $convenio->fill($dados);
        $convenio->save();
        return $convenio;
    }

    /**
     * Inativa convênio, considerando vínculos (pacientes, procedimentos).
     */
    public function inativar($convenioId)
    {
        $convenio = Convenio::findOrFail($convenioId);

        // Verificar vínculos com pacientes
        $pacientesVinculados = Paciente::where('convenio_id', $convenioId)->count();
        if ($pacientesVinculados > 0) {
            throw new \Exception('Não é possível inativar: convênio vinculado a pacientes.');
        }

        // Verificar vínculos com procedimentos
        $procedimentosVinculados = Procedimento::where('convenio_id', $convenioId)->count();
        if ($procedimentosVinculados > 0) {
            throw new \Exception('Não é possível inativar: convênio vinculado a procedimentos.');
        }

        $convenio->ativo = false;
        $convenio->save();
        return $convenio;
    }
}
