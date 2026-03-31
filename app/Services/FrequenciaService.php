<?php
namespace App\Services;

use App\Models\Frequencia;
use App\Models\Agendamento;
// Certifique-se de que o arquivo Tratamento.php existe em app/Models e contém:
//
// namespace App\Models;
//
// use Illuminate\Database\Eloquent\Model;
//
// class Tratamento extends Model
// {
//     protected $fillable = [
//         // adicione os campos necessários
//     ];
// }
use App\Models\Tratamento;

// Certifique-se de que o arquivo Frequencia.php existe em app/Models e contém:
//
// namespace App\Models;
//
// use Illuminate\Database\Eloquent\Model;
//
// class Frequencia extends Model
// {
//     protected $fillable = [
//         'nome', 'descricao', 'intervalo_dias', 'tipo_intervalo', 'icone', 'cor', 'ativo'
//     ];
// }

class FrequenciaService
{
    /**
     * Cadastra uma nova frequência (ex: recorrência de agendamento) com validações e regras de negócio.
     */
    public function cadastrar(array $dados)
    {
        // Validação dos campos obrigatórios
        $required = ['nome', 'intervalo_dias', 'tipo_intervalo'];
        foreach ($required as $field) {
            if (empty($dados[$field])) {
                throw new \InvalidArgumentException("Campo obrigatório: $field");
            }
        }

        // Validação de intervalo
        if (!is_numeric($dados['intervalo_dias']) || $dados['intervalo_dias'] <= 0) {
            throw new \InvalidArgumentException('Intervalo de dias deve ser um número positivo');
        }

        // Verificação de duplicidade
        $existe = Frequencia::where('nome', $dados['nome'])
            ->where('tipo_intervalo', $dados['tipo_intervalo'])
            ->first();
        if ($existe) {
            throw new \Exception('Já existe uma frequência com este nome e tipo');
        }

        // Criação da frequência
        return Frequencia::create([
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'] ?? null,
            'intervalo_dias' => $dados['intervalo_dias'],
            'tipo_intervalo' => $dados['tipo_intervalo'],
            'icone' => $dados['icone'] ?? null,
            'cor' => $dados['cor'] ?? null,
            'ativo' => true
        ]);
    }

    /**
     * Atualiza dados da frequência.
     */
    public function atualizar($frequenciaId, array $dados)
    {
        $frequencia = Frequencia::findOrFail($frequenciaId);

        // Validação dos campos
        if (isset($dados['intervalo_dias']) && (!is_numeric($dados['intervalo_dias']) || $dados['intervalo_dias'] <= 0)) {
            throw new \InvalidArgumentException('Intervalo de dias deve ser um número positivo');
        }

        // Verificação de duplicidade se nome/tipo mudarem
        if (isset($dados['nome']) || isset($dados['tipo_intervalo'])) {
            $nome = $dados['nome'] ?? $frequencia->nome;
            $tipo = $dados['tipo_intervalo'] ?? $frequencia->tipo_intervalo;
            $existe = Frequencia::where('nome', $nome)
                ->where('tipo_intervalo', $tipo)
                ->where('id', '!=', $frequenciaId)
                ->first();
            if ($existe) {
                throw new \Exception('Já existe uma frequência com este nome e tipo');
            }
        }

        $frequencia->fill($dados);
        $frequencia->save();
        return $frequencia;
    }

    /**
     * Inativa frequência, considerando vínculos (agendamentos).
     */
    public function inativar($frequenciaId)
    {
        $frequencia = Frequencia::findOrFail($frequenciaId);

        // Verificar vínculos com agendamentos ou tratamentos
        $vinculosAgendamento = Agendamento::where('frequencia_id', $frequenciaId)->count();
        $vinculosTratamento = Tratamento::where('frequencia_id', $frequenciaId)->count();
        if ($vinculosAgendamento > 0 || $vinculosTratamento > 0) {
            throw new \Exception('Não é possível inativar: frequência vinculada a agendamentos ou tratamentos');
        }

        $frequencia->ativo = false;
        $frequencia->save();
        return $frequencia;
    }
}
