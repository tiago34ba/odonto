<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PacienteController extends Controller
{
    /**
     * Listar pacientes com paginação
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'search' => 'string|max:255',
            'sort_by' => 'string|in:nome,email,created_at,telefone',
            'sort_order' => 'string|in:asc,desc'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $perPage = $request->get('per_page', 15);
        $page = $request->get('page', 1);
        $search = $request->get('search');
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $cacheKey = "pacientes:page:{$page}:per_page:{$perPage}:search:" . md5($search ?? '') . ":sort:{$sortBy}:{$sortOrder}";

        $result = CacheService::remember($cacheKey, function () use ($perPage, $search, $sortBy, $sortOrder) {
            $query = Paciente::query();

            // Aplicar busca se fornecida
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nome', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%")
                      ->orWhere('telefone', 'LIKE', "%{$search}%")
                      ->orWhere('cpf', 'LIKE', "%{$search}%");
                });
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
            ]
        ]);
    }

    /**
     * Criar novo paciente
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:pacientes,email',
            'telefone' => 'required|string|max:20',
            'cpf' => 'required|string|size:11|unique:pacientes,cpf',
            'data_nascimento' => 'required|date',
            'endereco' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $paciente = Paciente::create($request->validated());

        // Invalidar cache de pacientes
        CacheService::forgetPatients();

        return response()->json([
            'message' => 'Paciente criado com sucesso',
            'data' => $paciente
        ], 201);
    }

    /**
     * Mostrar paciente específico
     */
    public function show(int $id): JsonResponse
    {
        $cacheKey = "paciente:{$id}";

        $paciente = CacheService::remember($cacheKey, function () use ($id) {
            return Paciente::with(['agendamentos' => function ($query) {
                $query->with(['procedimento', 'funcionario'])
                      ->orderBy('data_agendamento', 'desc')
                      ->limit(10);
            }])->findOrFail($id);
        });

        return response()->json(['data' => $paciente]);
    }

    /**
     * Atualizar paciente
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $paciente = Paciente::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nome' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:pacientes,email,' . $id,
            'telefone' => 'sometimes|required|string|max:20',
            'cpf' => 'sometimes|required|string|size:11|unique:pacientes,cpf,' . $id,
            'data_nascimento' => 'sometimes|required|date',
            'endereco' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $paciente->update($request->validated());

        // Invalidar cache relacionado
        CacheService::forgetPatients();
        CacheService::forget("paciente:{$id}");

        return response()->json([
            'message' => 'Paciente atualizado com sucesso',
            'data' => $paciente
        ]);
    }

    /**
     * Deletar paciente
     */
    public function destroy(int $id): JsonResponse
    {
        $paciente = Paciente::findOrFail($id);
        $paciente->delete();

        // Invalidar cache relacionado
        CacheService::forgetPatients();
        CacheService::forget("paciente:{$id}");

        return response()->json([
            'message' => 'Paciente deletado com sucesso'
        ]);
    }
}