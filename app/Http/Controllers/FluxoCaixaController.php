<?php

namespace App\Http\Controllers;

use App\Models\FluxoCaixa;
use App\Models\ContasPagar;
use App\Models\ContasReceber;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FluxoCaixaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
        $query = FluxoCaixa::with(['contaPagar', 'contaReceber'])
            ->orderBy('data_movimento', 'desc');

        // Filtros
        if ($request->filled('data_inicio')) {
            $query->where('data_movimento', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->where('data_movimento', '<=', $request->data_fim);
        }            if ($request->filled('tipo')) {
                $query->where('tipo', $request->tipo);
            }

            if ($request->filled('categoria')) {
                $query->where('categoria', $request->categoria);
            }

            $perPage = $request->get('per_page', 15);
            $movimentacoes = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $movimentacoes,
                'message' => 'Movimentações listadas com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar movimentações: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'tipo' => 'required|in:entrada,saida',
                'categoria' => 'required|string|max:100',
                'descricao' => 'required|string|max:500',
                'valor' => 'required|numeric|min:0.01',
                'data_movimento' => 'required|date',
                'observacoes' => 'nullable|string|max:1000'
            ]);

            $movimentacao = FluxoCaixa::create($validated);

            return response()->json([
                'success' => true,
                'data' => $movimentacao,
                'message' => 'Movimentação criada com sucesso'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar movimentação: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $movimentacao = FluxoCaixa::with(['contaPagar', 'contaReceber'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $movimentacao,
                'message' => 'Movimentação encontrada'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Movimentação não encontrada'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $movimentacao = FluxoCaixa::findOrFail($id);

            $validated = $request->validate([
                'tipo' => 'sometimes|in:entrada,saida',
                'categoria' => 'sometimes|string|max:100',
                'descricao' => 'sometimes|string|max:500',
                'valor' => 'sometimes|numeric|min:0.01',
                'data_movimento' => 'sometimes|date',
                'observacoes' => 'nullable|string|max:1000'
            ]);

            $movimentacao->update($validated);

            return response()->json([
                'success' => true,
                'data' => $movimentacao,
                'message' => 'Movimentação atualizada com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar movimentação: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $movimentacao = FluxoCaixa::findOrFail($id);
            $movimentacao->delete();

            return response()->json([
                'success' => true,
                'message' => 'Movimentação deletada com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar movimentação: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dashboard do Fluxo de Caixa
     */
    public function dashboard()
    {
        try {
            $hoje = Carbon::today();
            $inicioMes = Carbon::now()->startOfMonth();
            $fimMes = Carbon::now()->endOfMonth();

            // Saldo atual
            $saldoAtual = $this->calcularSaldoAtual();

            // Movimentações do mês
            $entradasMes = FluxoCaixa::where('tipo', 'entrada')
                ->whereBetween('data_movimento', [$inicioMes, $fimMes])
                ->sum('valor');

            $saidasMes = FluxoCaixa::where('tipo', 'saida')
                ->whereBetween('data_movimento', [$inicioMes, $fimMes])
                ->sum('valor');

            // Movimentações do dia
            $entradasDia = FluxoCaixa::where('tipo', 'entrada')
                ->whereDate('data_movimento', $hoje)
                ->sum('valor');

            $saidasDia = FluxoCaixa::where('tipo', 'saida')
                ->whereDate('data_movimento', $hoje)
                ->sum('valor');

            // Últimas movimentações
            $ultimasMovimentacoes = FluxoCaixa::with(['contaPagar', 'contaReceber'])
                ->orderBy('data_movimento', 'desc')
                ->limit(5)
                ->get();

            // Projeção próximos 30 dias
            $projecao = $this->calcularProjecao(30);

            return response()->json([
                'success' => true,
                'data' => [
                    'saldo_atual' => $saldoAtual,
                    'movimentacoes_mes' => [
                        'entradas' => $entradasMes,
                        'saidas' => $saidasMes,
                        'saldo' => $entradasMes - $saidasMes
                    ],
                    'movimentacoes_dia' => [
                        'entradas' => $entradasDia,
                        'saidas' => $saidasDia,
                        'saldo' => $entradasDia - $saidasDia
                    ],
                    'ultimas_movimentacoes' => $ultimasMovimentacoes,
                    'projecao_30_dias' => $projecao
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Saldo atual do caixa
     */
    public function saldoAtual()
    {
        try {
            $saldo = $this->calcularSaldoAtual();

            return response()->json([
                'success' => true,
                'data' => [
                    'saldo' => $saldo,
                    'data_calculo' => Carbon::now()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao calcular saldo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Movimentações por período
     */
    public function movimentacoesPorPeriodo(Request $request)
    {
        try {
            $dataInicio = $request->get('data_inicio', Carbon::now()->startOfMonth());
            $dataFim = $request->get('data_fim', Carbon::now()->endOfMonth());

            $movimentacoes = FluxoCaixa::with(['contaPagar', 'contaReceber'])
                ->whereBetween('data_movimento', [$dataInicio, $dataFim])
                ->orderBy('data_movimento', 'desc')
                ->get();

            $totais = [
                'entradas' => $movimentacoes->where('tipo', 'entrada')->sum('valor'),
                'saidas' => $movimentacoes->where('tipo', 'saida')->sum('valor')
            ];
            $totais['saldo'] = $totais['entradas'] - $totais['saidas'];

            return response()->json([
                'success' => true,
                'data' => [
                    'periodo' => [
                        'inicio' => $dataInicio,
                        'fim' => $dataFim
                    ],
                    'movimentacoes' => $movimentacoes,
                    'totais' => $totais
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar movimentações: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Entradas do período
     */
    public function entradas(Request $request)
    {
        try {
            $dataInicio = $request->get('data_inicio', Carbon::now()->startOfMonth());
            $dataFim = $request->get('data_fim', Carbon::now()->endOfMonth());

            $entradas = FluxoCaixa::where('tipo', 'entrada')
                ->whereBetween('data_movimento', [$dataInicio, $dataFim])
                ->with(['contaReceber'])
                ->orderBy('data_movimento', 'desc')
                ->get();

            $total = $entradas->sum('valor');

            return response()->json([
                'success' => true,
                'data' => [
                    'entradas' => $entradas,
                    'total' => $total,
                    'periodo' => ['inicio' => $dataInicio, 'fim' => $dataFim]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar entradas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Saídas do período
     */
    public function saidas(Request $request)
    {
        try {
            $dataInicio = $request->get('data_inicio', Carbon::now()->startOfMonth());
            $dataFim = $request->get('data_fim', Carbon::now()->endOfMonth());

            $saidas = FluxoCaixa::where('tipo', 'saida')
                ->whereBetween('data_movimento', [$dataInicio, $dataFim])
                ->with(['contaPagar'])
                ->orderBy('data_movimento', 'desc')
                ->get();

            $total = $saidas->sum('valor');

            return response()->json([
                'success' => true,
                'data' => [
                    'saidas' => $saidas,
                    'total' => $total,
                    'periodo' => ['inicio' => $dataInicio, 'fim' => $dataFim]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar saídas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Relatório diário
     */
    public function relatorioDiario(Request $request)
    {
        try {
            $data = $request->get('data', Carbon::today());

            $movimentacoes = FluxoCaixa::with(['contaPagar', 'contaReceber'])
                ->whereDate('data_movimento', $data)
                ->orderBy('data_movimento', 'desc')
                ->get();

            $entradas = $movimentacoes->where('tipo', 'entrada')->sum('valor');
            $saidas = $movimentacoes->where('tipo', 'saida')->sum('valor');
            $saldo = $entradas - $saidas;

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $data,
                    'movimentacoes' => $movimentacoes,
                    'resumo' => [
                        'entradas' => $entradas,
                        'saidas' => $saidas,
                        'saldo' => $saldo
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório diário: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Relatório mensal
     */
    public function relatorioMensal(Request $request)
    {
        try {
            $mes = $request->get('mes', Carbon::now()->month);
            $ano = $request->get('ano', Carbon::now()->year);

            $inicioMes = Carbon::createFromDate($ano, $mes, 1)->startOfMonth();
            $fimMes = Carbon::createFromDate($ano, $mes, 1)->endOfMonth();

            $movimentacoes = FluxoCaixa::with(['contaPagar', 'contaReceber'])
                ->whereBetween('data_movimento', [$inicioMes, $fimMes])
                ->orderBy('data_movimento', 'desc')
                ->get();

            // Agrupamento por categoria
            $porCategoria = $movimentacoes->groupBy('categoria')->map(function ($items, $categoria) {
                return [
                    'categoria' => $categoria,
                    'entradas' => $items->where('tipo', 'entrada')->sum('valor'),
                    'saidas' => $items->where('tipo', 'saida')->sum('valor'),
                    'total' => $items->where('tipo', 'entrada')->sum('valor') - $items->where('tipo', 'saida')->sum('valor')
                ];
            });

            $entradas = $movimentacoes->where('tipo', 'entrada')->sum('valor');
            $saidas = $movimentacoes->where('tipo', 'saida')->sum('valor');

            return response()->json([
                'success' => true,
                'data' => [
                    'periodo' => [
                        'mes' => $mes,
                        'ano' => $ano,
                        'inicio' => $inicioMes,
                        'fim' => $fimMes
                    ],
                    'movimentacoes' => $movimentacoes,
                    'por_categoria' => $porCategoria,
                    'resumo' => [
                        'entradas' => $entradas,
                        'saidas' => $saidas,
                        'saldo' => $entradas - $saidas
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório mensal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Projeção futura
     */
    public function projecaoFutura($dias = 30)
    {
        try {
            $dataInicio = Carbon::today();
            $dataFim = Carbon::today()->addDays($dias);

            $projecao = $this->calcularProjecao($dias);

            return response()->json([
                'success' => true,
                'data' => [
                    'periodo_projecao' => ['inicio' => $dataInicio, 'fim' => $dataFim],
                    'projecao' => $projecao
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao calcular projeção: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calcular saldo atual
     */
    private function calcularSaldoAtual()
    {
        $entradas = FluxoCaixa::where('tipo', 'entrada')->sum('valor');
        $saidas = FluxoCaixa::where('tipo', 'saida')->sum('valor');
        
        return $entradas - $saidas;
    }

    /**
     * Calcular projeção
     */
    private function calcularProjecao($dias)
    {
        $dataInicio = Carbon::today();
        $dataFim = Carbon::today()->addDays($dias);

        // Contas a receber no período
        $contasReceber = ContasReceber::where('status', 'pendente')
            ->whereBetween('data_vencimento', [$dataInicio, $dataFim])
            ->sum('valor_original');

        // Contas a pagar no período
        $contasPagar = ContasPagar::where('status', 'pendente')
            ->whereBetween('data_vencimento', [$dataInicio, $dataFim])
            ->sum('valor_original');

        $saldoAtual = $this->calcularSaldoAtual();
        $saldoProjetado = $saldoAtual + $contasReceber - $contasPagar;

        return [
            'saldo_atual' => $saldoAtual,
            'contas_receber' => $contasReceber,
            'contas_pagar' => $contasPagar,
            'saldo_projetado' => $saldoProjetado
        ];
    }
}
