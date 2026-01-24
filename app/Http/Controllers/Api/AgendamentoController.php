<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agendamento;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AgendamentoController extends Controller
{
    /**
     * Listar agendamentos com paginação
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'status' => 'nullable|string|in:agendado,confirmado,em_andamento,concluido,cancelado',
            'paciente_id' => 'nullable|integer|exists:pacientes,id',
            'funcionario_id' => 'nullable|integer|exists:funcionarios,id',
            'sort_by' => 'string|in:data_agendamento,status,created_at',
            'sort_order' => 'string|in:asc,desc'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $perPage = $request->get('per_page', 15);
        $page = $request->get('page', 1);
        $dataInicio = $request->get('data_inicio');
        $dataFim = $request->get('data_fim');
        $status = $request->get('status');
        $pacienteId = $request->get('paciente_id');
        $funcionarioId = $request->get('funcionario_id');
        $sortBy = $request->get('sort_by', 'data_agendamento');
        $sortOrder = $request->get('sort_order', 'asc');

        $cacheKey = "agendamentos:page:{$page}:per_page:{$perPage}:" .
                   md5(json_encode($request->only([
                       'data_inicio', 'data_fim', 'status', 'paciente_id',
                       'funcionario_id', 'sort_by', 'sort_order'
                   ])));

        $result = CacheService::remember($cacheKey, function () use (
            $perPage, $dataInicio, $dataFim, $status, $pacienteId,
            $funcionarioId, $sortBy, $sortOrder
        ) {
            $query = Agendamento::with(['paciente:id,nome,telefone', 'funcionario:id,nome', 'procedimento:id,nome,valor']);

            // Aplicar filtros
            if ($dataInicio) {
                $query->whereDate('data_agendamento', '>=', $dataInicio);
            }

            if ($dataFim) {
                $query->whereDate('data_agendamento', '<=', $dataFim);
            }

            if ($status) {
                $query->where('status', $status);
            }

            if ($pacienteId) {
                $query->where('paciente_id', $pacienteId);
            }

            if ($funcionarioId) {
                $query->where('funcionario_id', $funcionarioId);
            }

            return $query->orderBy($sortBy, $sortOrder)
                         ->paginate($perPage);
        }, CacheService::SHORT_TTL);

        return response()->json([
            'data' => $result->items(),
            'meta' => [
                'current_page' => $result->currentPage(),
                'from' => $result->firstItem(),
                'last_page' => $result->lastPage(),
                'per_page' => $result->perPage(),
                'to' => $result->lastItem(),
                'total' => $result->total(),
            ],
            'links' => [
                'first' => $result->url(1),
                'last' => $result->url($result->lastPage()),
                'prev' => $result->previousPageUrl(),
                'next' => $result->nextPageUrl(),
            ],
            'stats' => [
                'hoje' => $this->getStatsForDate(Carbon::today()),
                'semana' => $this->getStatsForPeriod(Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()),
                'mes' => $this->getStatsForPeriod(Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()),
            ]
        ]);
    }

    /**
     * Criar novo agendamento
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'paciente_id' => 'required|integer|exists:pacientes,id',
            'funcionario_id' => 'required|integer|exists:funcionarios,id',
            'procedimento_id' => 'required|integer|exists:procedimentos,id',
            'data_agendamento' => 'required|date|after:now',
            'observacoes' => 'nullable|string|max:1000',
            'status' => 'nullable|string|in:agendado,confirmado|default:agendado'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verificar disponibilidade
        $conflito = Agendamento::where('funcionario_id', $request->funcionario_id)
            ->where('data_agendamento', $request->data_agendamento)
            ->whereIn('status', ['agendado', 'confirmado', 'em_andamento'])
            ->exists();

        if ($conflito) {
            return response()->json([
                'error' => 'Horário não disponível para este funcionário'
            ], 422);
        }

        $agendamento = Agendamento::create($request->validated());
        $agendamento->load(['paciente', 'funcionario', 'procedimento']);

        // Invalidar cache de agendamentos
        CacheService::forgetAppointments();

        return response()->json([
            'message' => 'Agendamento criado com sucesso',
            'data' => $agendamento
        ], 201);
    }

    /**
     * Mostrar agendamento específico
     */
    public function show(int $id): JsonResponse
    {
        $cacheKey = "agendamento:{$id}";

        $agendamento = CacheService::remember($cacheKey, function () use ($id) {
            return Agendamento::with(['paciente', 'funcionario', 'procedimento'])
                             ->findOrFail($id);
        });

        return response()->json(['data' => $agendamento]);
    }

    /**
     * Atualizar agendamento
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $agendamento = Agendamento::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'paciente_id' => 'sometimes|required|integer|exists:pacientes,id',
            'funcionario_id' => 'sometimes|required|integer|exists:funcionarios,id',
            'procedimento_id' => 'sometimes|required|integer|exists:procedimentos,id',
            'data_agendamento' => 'sometimes|required|date',
            'observacoes' => 'nullable|string|max:1000',
            'status' => 'sometimes|required|string|in:agendado,confirmado,em_andamento,concluido,cancelado'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verificar disponibilidade se data ou funcionário mudaram
        if ($request->has('data_agendamento') || $request->has('funcionario_id')) {
            $funcionarioId = $request->get('funcionario_id', $agendamento->funcionario_id);
            $dataAgendamento = $request->get('data_agendamento', $agendamento->data_agendamento);

            $conflito = Agendamento::where('funcionario_id', $funcionarioId)
                ->where('data_agendamento', $dataAgendamento)
                ->where('id', '!=', $id)
                ->whereIn('status', ['agendado', 'confirmado', 'em_andamento'])
                ->exists();

            if ($conflito) {
                return response()->json([
                    'error' => 'Horário não disponível para este funcionário'
                ], 422);
            }
        }

        $agendamento->update($request->validated());
        $agendamento->load(['paciente', 'funcionario', 'procedimento']);

        // Invalidar cache relacionado
        CacheService::forgetAppointments();
        CacheService::forget("agendamento:{$id}");

        return response()->json([
            'message' => 'Agendamento atualizado com sucesso',
            'data' => $agendamento
        ]);
    }

    /**
     * Deletar agendamento
     */
    public function destroy(int $id): JsonResponse
    {
        $agendamento = Agendamento::findOrFail($id);
        $agendamento->delete();

        // Invalidar cache relacionado
        CacheService::forgetAppointments();
        CacheService::forget("agendamento:{$id}");

        return response()->json([
            'message' => 'Agendamento deletado com sucesso'
        ]);
    }

    /**
     * Obter estatísticas para uma data específica
     */
    private function getStatsForDate(Carbon $date): array
    {
        $cacheKey = "stats:date:" . $date->format('Y-m-d');

        return CacheService::remember($cacheKey, function () use ($date) {
            return [
                'total' => Agendamento::whereDate('data_agendamento', $date)->count(),
                'agendados' => Agendamento::whereDate('data_agendamento', $date)->where('status', 'agendado')->count(),
                'confirmados' => Agendamento::whereDate('data_agendamento', $date)->where('status', 'confirmado')->count(),
                'concluidos' => Agendamento::whereDate('data_agendamento', $date)->where('status', 'concluido')->count(),
                'cancelados' => Agendamento::whereDate('data_agendamento', $date)->where('status', 'cancelado')->count(),
            ];
        }, CacheService::SHORT_TTL);
    }

    /**
     * Obter estatísticas para um período
     */
    private function getStatsForPeriod(Carbon $inicio, Carbon $fim): array
    {
        $cacheKey = "stats:period:" . $inicio->format('Y-m-d') . ":" . $fim->format('Y-m-d');

        return CacheService::remember($cacheKey, function () use ($inicio, $fim) {
            return [
                'total' => Agendamento::whereBetween('data_agendamento', [$inicio, $fim])->count(),
                'agendados' => Agendamento::whereBetween('data_agendamento', [$inicio, $fim])->where('status', 'agendado')->count(),
                'confirmados' => Agendamento::whereBetween('data_agendamento', [$inicio, $fim])->where('status', 'confirmado')->count(),
                'concluidos' => Agendamento::whereBetween('data_agendamento', [$inicio, $fim])->where('status', 'concluido')->count(),
                'cancelados' => Agendamento::whereBetween('data_agendamento', [$inicio, $fim])->where('status', 'cancelado')->count(),
            ];
        }, CacheService::SHORT_TTL);
    }
}
