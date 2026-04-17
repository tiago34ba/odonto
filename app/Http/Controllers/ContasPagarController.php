<?php

namespace App\Http\Controllers;

use App\Models\ContasPagar;
use App\Models\FluxoCaixa;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class ContasPagarController extends Controller
{
    /**
     * Listar todas as contas a pagar
     */
    public function index(Request $request): JsonResponse
    {
        $query = ContasPagar::with(['supplier']);

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

        if ($request->filled('prioridade')) {
            $query->where('prioridade', $request->prioridade);
        }

        $contas = $query->orderBy('data_vencimento', 'asc')->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Contas a pagar listadas com sucesso',
            'data' => $contas
        ]);
    }

    /**
     * Dashboard de contas a pagar
     */
    public function dashboard(Request $request): JsonResponse
    {
        $hoje = Carbon::today();
        $amanha = Carbon::tomorrow();

        $totalizadores = [
            'vencidas' => ContasPagar::vencidas()->sum('valor_pendente'),
            'vencente_hoje' => ContasPagar::where('data_vencimento', $hoje)
                ->pendentes()->sum('valor_pendente'),
            'vencente_amanha' => ContasPagar::where('data_vencimento', $amanha)
                ->pendentes()->sum('valor_pendente'),
            'pagas' => ContasPagar::where('status', 'Pago')
                ->whereMonth('data_pagamento', $hoje->month)
                ->sum('valor_pago'),
            'total_periodo' => ContasPagar::whereMonth('created_at', $hoje->month)
                ->sum('valor_original'),
            'todas_pendentes' => ContasPagar::pendentes()->sum('valor_pendente')
        ];

        $proximasVencimentos = ContasPagar::pendentes()
            ->orderBy('data_vencimento', 'asc')
            ->take(10)
            ->with('supplier')
            ->get();

        $contasPorStatus = ContasPagar::selectRaw('status, count(*) as quantidade, sum(valor_pendente) as valor_total')
            ->groupBy('status')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard de contas a pagar',
            'data' => [
                'totalizadores' => $totalizadores,
                'proximos_vencimentos' => $proximasVencimentos,
                'contas_por_status' => $contasPorStatus
            ]
        ]);
    }

    /**
     * Criar nova conta a pagar
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'descricao' => 'required|string|max:255',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'categoria' => 'required|string|max:100',
            'valor_original' => 'required|numeric|min:0',
            'data_vencimento' => 'required|date',
            'prioridade' => 'required|in:Baixa,Média,Alta,Crítica',
            'observacoes' => 'nullable|string'
        ]);

        $conta = ContasPagar::create([
            'codigo' => ContasPagar::gerarCodigo(),
            'descricao' => $request->descricao,
            'supplier_id' => $request->supplier_id,
            'categoria' => $request->categoria,
            'valor_original' => $request->valor_original,
            'valor_pago' => 0,
            'valor_pendente' => $request->valor_original,
            'data_vencimento' => $request->data_vencimento,
            'prioridade' => $request->prioridade,
            'observacoes' => $request->observacoes
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Conta a pagar criada com sucesso',
            'data' => $conta->load('supplier')
        ], 201);
    }

    /**
     * Mostrar conta específica
     */
    public function show(ContasPagar $contasPagar): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Conta a pagar encontrada',
            'data' => $contasPagar->load(['supplier', 'fluxoCaixa'])
        ]);
    }

    /**
     * Atualizar conta a pagar
     */
    public function update(Request $request, $id): JsonResponse
    {
        $contasPagar = ContasPagar::findOrFail($id);

        $validated = $request->validate([
            'descricao' => 'required|string|max:255',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'categoria' => 'required|string|max:100',
            'valor_original' => 'required|numeric|min:0',
            'valor_pago' => 'nullable|numeric|min:0',
            'data_vencimento' => 'required|date',
            'data_pagamento' => 'nullable|date',
            'status' => 'nullable|in:Pendente,Vencido,Pago,Parcial',
            'prioridade' => 'required|in:Baixa,Média,Alta,Crítica',
            'forma_pagamento' => 'nullable|string|max:100',
            'observacoes' => 'nullable|string'
        ]);

        $valorOriginal = (float) ($validated['valor_original'] ?? $contasPagar->valor_original);
        $valorPago = array_key_exists('valor_pago', $validated)
            ? (float) $validated['valor_pago']
            : (float) $contasPagar->valor_pago;

        $valorPago = max(0, min($valorPago, $valorOriginal));
        $status = $validated['status'] ?? null;

        if ($status === 'Pago') {
            $valorPago = $valorOriginal;
            $validated['data_pagamento'] = $validated['data_pagamento'] ?? now()->toDateString();
        } elseif ($status === 'Pendente' || $status === 'Vencido') {
            $valorPago = 0;
            $validated['data_pagamento'] = null;
        }

        $valorPendente = max(0, $valorOriginal - $valorPago);

        if (!$status) {
            if ($valorPendente <= 0) {
                $status = 'Pago';
            } elseif ($valorPago > 0) {
                $status = 'Parcial';
            } elseif (Carbon::parse($validated['data_vencimento'])->lt(Carbon::today())) {
                $status = 'Vencido';
            } else {
                $status = 'Pendente';
            }
        } elseif ($status === 'Parcial' && $valorPago <= 0) {
            $status = Carbon::parse($validated['data_vencimento'])->lt(Carbon::today()) ? 'Vencido' : 'Pendente';
        }

        $validated['valor_pago'] = $valorPago;
        $validated['valor_pendente'] = $valorPendente;
        $validated['status'] = $status;

        $contasPagar->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Conta a pagar atualizada com sucesso',
            'data' => $contasPagar->load('supplier')
        ]);
    }

    /**
     * Pagar conta
     */
    public function pagar(Request $request, ContasPagar $contasPagar): JsonResponse
    {
        $request->validate([
            'valor_pagamento' => 'required|numeric|min:0',
            'data_pagamento' => 'required|date',
            'forma_pagamento' => 'required|string|max:100',
            'observacoes' => 'nullable|string'
        ]);

        $valorPagamento = $request->valor_pagamento;
        $novoValorPago = $contasPagar->valor_pago + $valorPagamento;
        $novoValorPendente = $contasPagar->valor_original - $novoValorPago;

        // Determinar novo status
        if ($novoValorPendente <= 0) {
            $status = 'Pago';
            $novoValorPendente = 0;
        } elseif ($novoValorPago > 0) {
            $status = 'Parcial';
        } else {
            $status = 'Pendente';
        }

        $contasPagar->update([
            'valor_pago' => $novoValorPago,
            'valor_pendente' => $novoValorPendente,
            'data_pagamento' => $request->data_pagamento,
            'forma_pagamento' => $request->forma_pagamento,
            'status' => $status
        ]);

        // Registrar no fluxo de caixa
        FluxoCaixa::create([
            'tipo' => 'Saída',
            'descricao' => 'Pagamento de conta: ' . $contasPagar->descricao,
            'categoria' => $contasPagar->categoria,
            'valor' => $valorPagamento,
            'data_movimento' => $request->data_pagamento,
            'forma_pagamento' => $request->forma_pagamento,
            'conta_pagar_id' => $contasPagar->id,
            'supplier_id' => $contasPagar->supplier_id,
            'observacoes' => $request->observacoes,
            'created_by' => 'Sistema'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pagamento registrado com sucesso',
            'data' => $contasPagar->fresh(['supplier'])
        ]);
    }

    /**
     * Deletar conta a pagar
     */
    public function destroy($id): JsonResponse
    {
        $contasPagar = ContasPagar::findOrFail($id);

        if ($contasPagar->valor_pago > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível excluir conta com pagamentos já realizados'
            ], 422);
        }

        $contasPagar->delete();

        return response()->json([
            'success' => true,
            'message' => 'Conta a pagar excluída com sucesso'
        ]);
    }

    /**
     * Listar contas vencidas
     */
    public function vencidas(): JsonResponse
    {
        try {
            $contasVencidas = ContasPagar::with(['supplier'])
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
            $dias = $request->get('dias', 7); // próximos 7 dias por padrão

            $contasVencendo = ContasPagar::with(['supplier'])
                ->whereBetween('data_vencimento', [
                    now()->toDateString(),
                    now()->addDays($dias)->toDateString()
                ])
                ->whereIn('status', ['Pendente'])
                ->orderBy('data_vencimento', 'asc')
                ->get();

            $total = $contasVencendo->sum('valor_pendente');

            return response()->json([
                'success' => true,
                'data' => [
                    'contas' => $contasVencendo,
                    'total' => $total,
                    'count' => $contasVencendo->count(),
                    'periodo' => $dias . ' dias'
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
     * Listar contas pagas
     */
    public function pagas(Request $request): JsonResponse
    {
        try {
            $query = ContasPagar::with(['supplier'])
                ->where('status', 'Pago')
                ->orderBy('data_pagamento', 'desc');

            // Filtro por período
            if ($request->filled('data_inicio')) {
                $query->where('data_pagamento', '>=', $request->data_inicio);
            }
            if ($request->filled('data_fim')) {
                $query->where('data_pagamento', '<=', $request->data_fim);
            }

            $contasPagas = $query->get();
            $total = $contasPagas->sum('valor_pago');

            return response()->json([
                'success' => true,
                'data' => [
                    'contas' => $contasPagas,
                    'total' => $total,
                    'count' => $contasPagas->count()
                ],
                'message' => 'Contas pagas listadas com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar contas pagas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar contas pendentes
     */
    public function pendentes(): JsonResponse
    {
        try {
            $contasPendentes = ContasPagar::with(['supplier'])
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
     * Listar contas por fornecedor
     */
    public function porFornecedor($fornecedorId): JsonResponse
    {
        try {
            $contas = ContasPagar::with(['supplier'])
                ->where('supplier_id', $fornecedorId)
                ->orderBy('data_vencimento', 'desc')
                ->get();

            $totais = [
                'total_original' => $contas->sum('valor_original'),
                'total_pago' => $contas->sum('valor_pago'),
                'total_pendente' => $contas->sum('valor_pendente')
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'contas' => $contas,
                    'totais' => $totais,
                    'count' => $contas->count()
                ],
                'message' => 'Contas do fornecedor listadas com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar contas do fornecedor: ' . $e->getMessage()
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

            $contas = ContasPagar::with(['supplier'])
                ->whereBetween('data_vencimento', [$dataInicio, $dataFim])
                ->orderBy('data_vencimento', 'asc')
                ->get();

            $resumo = [
                'pendentes' => $contas->whereIn('status', ['Pendente'])->sum('valor_pendente'),
                'pagas' => $contas->where('status', 'Pago')->sum('valor_pago'),
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
                'message' => 'Relatório de contas a pagar gerado com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar pagamento
     */
    public function registrarPagamento(Request $request, $id): JsonResponse
    {
        try {
            $conta = ContasPagar::findOrFail($id);

            $request->validate([
                'valor_pago' => 'required|numeric|min:0.01|max:' . $conta->valor_pendente,
                'data_pagamento' => 'required|date',
                'forma_pagamento' => 'required|string|max:100',
                'observacoes' => 'nullable|string|max:1000'
            ]);

            $valorPago = $request->valor_pago;
            $conta->valor_pago += $valorPago;
            $conta->valor_pendente -= $valorPago;
            $conta->data_pagamento = $request->data_pagamento;
            $conta->forma_pagamento = $request->forma_pagamento;

            if ($request->filled('observacoes')) {
                $conta->observacoes = $request->observacoes;
            }

            // Atualizar status
            if ($conta->valor_pendente <= 0) {
                $conta->status = 'Pago';
            } else {
                $conta->status = 'Parcial';
            }

            $conta->save();

            return response()->json([
                'success' => true,
                'data' => $conta,
                'message' => 'Pagamento registrado com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao registrar pagamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar pagamento
     */
    public function cancelarPagamento($id): JsonResponse
    {
        try {
            $conta = ContasPagar::findOrFail($id);

            if ($conta->status !== 'Pago' && $conta->status !== 'Parcial') {
                return response()->json([
                    'success' => false,
                    'message' => 'Não há pagamentos para cancelar nesta conta'
                ], 422);
            }

            $conta->valor_pendente = $conta->valor_original;
            $conta->valor_pago = 0;
            $conta->data_pagamento = null;
            $conta->forma_pagamento = null;
            $conta->status = 'Pendente';
            $conta->save();

            return response()->json([
                'success' => true,
                'data' => $conta,
                'message' => 'Pagamento cancelado com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cancelar pagamento: ' . $e->getMessage()
            ], 500);
        }
    }
}
