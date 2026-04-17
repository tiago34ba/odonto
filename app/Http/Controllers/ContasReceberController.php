<?php

namespace App\Http\Controllers;

use App\Models\ContasReceber;
use App\Models\FluxoCaixa;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class ContasReceberController extends Controller
{
    /**
     * Listar todas as contas a receber
     */
    public function index(Request $request): JsonResponse
    {
        $query = ContasReceber::with(['paciente', 'procedure', 'scheduling']);

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('data_inicio')) {
            $query->where('data_vencimento', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->where('data_vencimento', '<=', $request->data_fim);
        }

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        if ($request->filled('convenio')) {
            $query->where('convenio', $request->convenio);
        }

        if ($request->filled('paciente_id')) {
            $query->where('paciente_id', $request->paciente_id);
        }

        $contas = $query->orderBy('data_vencimento', 'asc')->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Contas a receber listadas com sucesso',
            'data' => $contas
        ]);
    }

    /**
     * Dashboard de contas a receber
     */
    public function dashboard(Request $request): JsonResponse
    {
        $hoje = Carbon::today();
        $amanha = Carbon::tomorrow();

        $totalizadores = [
            'vencidas' => ContasReceber::vencidas()->sum('valor_pendente'),
            'vencente_hoje' => ContasReceber::where('data_vencimento', $hoje)
                ->pendentes()->sum('valor_pendente'),
            'vencente_amanha' => ContasReceber::where('data_vencimento', $amanha)
                ->pendentes()->sum('valor_pendente'),
            'recebidas' => ContasReceber::where('status', 'Recebido')
                ->whereMonth('data_recebimento', $hoje->month)
                ->sum('valor_recebido'),
            'total_periodo' => ContasReceber::whereMonth('created_at', $hoje->month)
                ->sum('valor_original'),
            'todas_pendentes' => ContasReceber::pendentes()->sum('valor_pendente')
        ];

        $proximasVencimentos = ContasReceber::pendentes()
            ->orderBy('data_vencimento', 'asc')
            ->take(10)
            ->with(['paciente', 'procedure'])
            ->get();

        $contasPorStatus = ContasReceber::selectRaw('status, count(*) as quantidade, sum(valor_pendente) as valor_total')
            ->groupBy('status')
            ->get();

        $receitaPorCategoria = ContasReceber::selectRaw('categoria, sum(valor_recebido) as total_recebido')
            ->where('status', 'Recebido')
            ->whereMonth('data_recebimento', $hoje->month)
            ->groupBy('categoria')
            ->orderBy('total_recebido', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard de contas a receber',
            'data' => [
                'totalizadores' => $totalizadores,
                'proximos_vencimentos' => $proximasVencimentos,
                'contas_por_status' => $contasPorStatus,
                'receita_por_categoria' => $receitaPorCategoria
            ]
        ]);
    }

    /**
     * Criar nova conta a receber
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'procedure_id' => 'required|exists:procedures,id',
            'scheduling_id' => 'nullable|exists:schedulings,id',
            'categoria' => 'required|in:Consulta,Limpeza,Restauração,Endodontia,Ortodontia,Cirurgia,Prótese,Implante,Clareamento,Outros',
            'valor_original' => 'required|numeric|min:0',
            'data_vencimento' => 'required|date',
            'prioridade' => 'required|in:Baixa,Média,Alta,Crítica',
            'convenio' => 'nullable|string|max:100',
            'observacoes' => 'nullable|string'
        ]);

        $conta = ContasReceber::create([
            'codigo' => ContasReceber::gerarCodigo(),
            'paciente_id' => $request->paciente_id,
            'procedure_id' => $request->procedure_id,
            'scheduling_id' => $request->scheduling_id,
            'categoria' => $request->categoria,
            'valor_original' => $request->valor_original,
            'valor_recebido' => 0,
            'valor_pendente' => $request->valor_original,
            'data_vencimento' => $request->data_vencimento,
            'prioridade' => $request->prioridade,
            'convenio' => $request->convenio,
            'observacoes' => $request->observacoes
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Conta a receber criada com sucesso',
            'data' => $conta->load(['paciente', 'procedure'])
        ], 201);
    }

    /**
     * Mostrar conta específica
     */
    public function show(ContasReceber $contasReceber): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Conta a receber encontrada',
            'data' => $contasReceber->load(['paciente', 'procedure', 'scheduling', 'fluxoCaixa'])
        ]);
    }

    /**
     * Atualizar conta a receber
     */
    public function update(Request $request, $id): JsonResponse
    {
        $contasReceber = ContasReceber::findOrFail($id);

        $validated = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'procedure_id' => 'required|exists:procedures,id',
            'categoria' => 'required|in:Consulta,Limpeza,Restauração,Endodontia,Ortodontia,Cirurgia,Prótese,Implante,Clareamento,Outros',
            'valor_original' => 'required|numeric|min:0',
            'data_vencimento' => 'required|date',
            'prioridade' => 'required|in:Baixa,Média,Alta,Crítica',
            'convenio' => 'nullable|string|max:100',
            'observacoes' => 'nullable|string'
        ]);

        $contasReceber->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Conta a receber atualizada com sucesso',
            'data' => $contasReceber->load(['paciente', 'procedure'])
        ]);
    }

    /**
     * Receber pagamento
     */
    public function receber(Request $request, ContasReceber $contasReceber): JsonResponse
    {
        $request->validate([
            'valor_recebimento' => 'required|numeric|min:0',
            'data_recebimento' => 'required|date',
            'forma_pagamento' => 'required|string|max:100',
            'observacoes' => 'nullable|string'
        ]);

        $valorRecebimento = $request->valor_recebimento;
        $novoValorRecebido = $contasReceber->valor_recebido + $valorRecebimento;
        $novoValorPendente = $contasReceber->valor_original - $novoValorRecebido;

        // Determinar novo status
        if ($novoValorPendente <= 0) {
            $status = 'Recebido';
            $novoValorPendente = 0;
        } elseif ($novoValorRecebido > 0) {
            $status = 'Parcial';
        } else {
            $status = 'Pendente';
        }

        $contasReceber->update([
            'valor_recebido' => $novoValorRecebido,
            'valor_pendente' => $novoValorPendente,
            'data_recebimento' => $request->data_recebimento,
            'forma_pagamento' => $request->forma_pagamento,
            'status' => $status
        ]);

        // Registrar no fluxo de caixa
        FluxoCaixa::create([
            'tipo' => 'Entrada',
            'descricao' => 'Recebimento de conta: ' . $contasReceber->procedure->name,
            'categoria' => $contasReceber->categoria,
            'valor' => $valorRecebimento,
            'data_movimento' => $request->data_recebimento,
            'forma_pagamento' => $request->forma_pagamento,
            'conta_receber_id' => $contasReceber->id,
            'paciente_id' => $contasReceber->paciente_id,
            'observacoes' => $request->observacoes,
            'created_by' => 'Sistema'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Recebimento registrado com sucesso',
            'data' => $contasReceber->fresh(['paciente', 'procedure'])
        ]);
    }

    /**
     * Deletar conta a receber
     */
    public function destroy($id): JsonResponse
    {
        $contasReceber = ContasReceber::findOrFail($id);

        if ($contasReceber->valor_recebido > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível excluir conta com recebimentos já realizados'
            ], 422);
        }

        $contasReceber->delete();

        return response()->json([
            'success' => true,
            'message' => 'Conta a receber excluída com sucesso'
        ]);
    }

    /**
     * Listar contas vencidas
     */
    public function vencidas(): JsonResponse
    {
        try {
            $contasVencidas = ContasReceber::with(['paciente', 'procedure'])
                ->where('data_vencimento', '<', now()->toDateString())
                ->whereIn('status', ['Pendente', 'Vencido'])
                ->orderBy('data_vencimento', 'asc')
                ->get();

            $total = $contasVencidas->sum('valor_pendente');

            return response()->json([
                'success' => true,
                'data' => [
                    'contas' => $contasVencidas,
                    'total' => $total,
                    'count' => $contasVencidas->count()
                ],
                'message' => 'Contas vencidas listadas com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar contas vencidas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar contas vencendo
     */
    public function vencendo(Request $request): JsonResponse
    {
        try {
            $dias = intval($request->get('dias', 7));

            $contasVencendo = ContasReceber::with(['paciente', 'procedure'])
                ->whereRaw('data_vencimento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)', [$dias])
                ->where('status', 'Pendente')
                ->orderBy('data_vencimento', 'asc')
                ->get();

            $total = $contasVencendo->sum('valor_pendente');

            return response()->json([
                'success' => true,
                'data' => [
                    'contas' => $contasVencendo,
                    'total' => $total,
                    'count' => $contasVencendo->count()
                ],
                'message' => 'Contas vencendo listadas com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar contas vencendo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar contas recebidas
     */
    public function recebidas(Request $request): JsonResponse
    {
        try {
            $query = ContasReceber::with(['paciente', 'procedure'])
                ->where('status', 'Recebido')
                ->orderBy('data_recebimento', 'desc');

            if ($request->filled('data_inicio')) {
                $query->where('data_recebimento', '>=', $request->data_inicio);
            }
            if ($request->filled('data_fim')) {
                $query->where('data_recebimento', '<=', $request->data_fim);
            }

            $contasRecebidas = $query->get();
            $total = $contasRecebidas->sum('valor_recebido');

            return response()->json([
                'success' => true,
                'data' => [
                    'contas' => $contasRecebidas,
                    'total' => $total,
                    'count' => $contasRecebidas->count()
                ],
                'message' => 'Contas recebidas listadas com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar contas recebidas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar contas pendentes
     */
    public function pendentes(): JsonResponse
    {
        try {
            $contasPendentes = ContasReceber::with(['paciente', 'procedure'])
                ->whereIn('status', ['Pendente'])
                ->orderBy('data_vencimento', 'asc')
                ->get();

            $total = $contasPendentes->sum('valor_pendente');

            return response()->json([
                'success' => true,
                'data' => [
                    'contas' => $contasPendentes,
                    'total' => $total,
                    'count' => $contasPendentes->count()
                ],
                'message' => 'Contas pendentes listadas com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar contas pendentes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar contas por paciente
     */
    public function porPaciente($pacienteId): JsonResponse
    {
        try {
            $contas = ContasReceber::with(['paciente', 'procedure'])
                ->where('paciente_id', $pacienteId)
                ->orderBy('data_vencimento', 'desc')
                ->get();

            $totais = [
                'total_original' => $contas->sum('valor_original'),
                'total_recebido' => $contas->sum('valor_recebido'),
                'total_pendente' => $contas->sum('valor_pendente')
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'contas' => $contas,
                    'totais' => $totais,
                    'count' => $contas->count()
                ],
                'message' => 'Contas do paciente listadas com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar contas do paciente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar contas por categoria
     */
    public function porCategoria($categoria): JsonResponse
    {
        try {
            $contas = ContasReceber::with(['paciente', 'procedure'])
                ->where('categoria', $categoria)
                ->orderBy('data_vencimento', 'desc')
                ->get();

            $totais = [
                'total_original' => $contas->sum('valor_original'),
                'total_recebido' => $contas->sum('valor_recebido'),
                'total_pendente' => $contas->sum('valor_pendente')
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'contas' => $contas,
                    'totais' => $totais,
                    'count' => $contas->count()
                ],
                'message' => 'Contas da categoria listadas com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar contas da categoria: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Relatório por período
     */
    public function relatorioPorPeriodo(Request $request): JsonResponse
    {
        try {
            $dataInicio = $request->get('data_inicio', now()->startOfMonth()->toDateString());
            $dataFim = $request->get('data_fim', now()->endOfMonth()->toDateString());

            $contas = ContasReceber::with(['paciente', 'procedure'])
                ->whereBetween('data_vencimento', [$dataInicio, $dataFim])
                ->orderBy('data_vencimento', 'asc')
                ->get();

            $resumo = [
                'pendentes' => $contas->whereIn('status', ['Pendente'])->sum('valor_pendente'),
                'recebidas' => $contas->where('status', 'Recebido')->sum('valor_recebido'),
                'vencidas' => $contas->where('status', 'Vencido')->sum('valor_pendente'),
                'total' => $contas->sum('valor_original')
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'periodo' => ['inicio' => $dataInicio, 'fim' => $dataFim],
                    'contas' => $contas,
                    'resumo' => $resumo
                ],
                'message' => 'Relatório de contas a receber gerado com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar recebimento
     */
    public function registrarRecebimento(Request $request, $id): JsonResponse
    {
        try {
            $conta = ContasReceber::findOrFail($id);

            $request->validate([
                'valor_recebido' => 'required|numeric|min:0.01|max:' . $conta->valor_pendente,
                'data_recebimento' => 'required|date',
                'forma_pagamento' => 'required|string|max:100',
                'observacoes' => 'nullable|string|max:1000'
            ]);

            $valorRecebido = $request->valor_recebido;
            $conta->valor_recebido += $valorRecebido;
            $conta->valor_pendente -= $valorRecebido;
            $conta->data_recebimento = $request->data_recebimento;
            $conta->forma_pagamento = $request->forma_pagamento;

            if ($request->filled('observacoes')) {
                $conta->observacoes = $request->observacoes;
            }

            // Atualizar status
            if ($conta->valor_pendente <= 0) {
                $conta->status = 'Recebido';
            } else {
                $conta->status = 'Parcial';
            }

            $conta->save();

            return response()->json([
                'success' => true,
                'data' => $conta,
                'message' => 'Recebimento registrado com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao registrar recebimento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar recebimento
     */
    public function cancelarRecebimento($id): JsonResponse
    {
        try {
            $conta = ContasReceber::findOrFail($id);

            if ($conta->status !== 'Recebido' && $conta->status !== 'Parcial') {
                return response()->json([
                    'success' => false,
                    'message' => 'Não há recebimentos para cancelar nesta conta'
                ], 422);
            }

            $conta->valor_pendente = $conta->valor_original;
            $conta->valor_recebido = 0;
            $conta->data_recebimento = null;
            $conta->forma_pagamento = null;
            $conta->status = 'Pendente';
            $conta->save();

            return response()->json([
                'success' => true,
                'data' => $conta,
                'message' => 'Recebimento cancelado com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cancelar recebimento: ' . $e->getMessage()
            ], 500);
        }
    }
}
