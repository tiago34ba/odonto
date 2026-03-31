<?php

namespace App\Http\Controllers;

use App\Models\GrupoAcesso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GrupoAcessoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = GrupoAcesso::query();

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                    ->orWhere('descricao', 'like', "%{$search}%");
            });
        }

        if ($request->filled('ativo')) {
            $query->where('ativo', filter_var($request->input('ativo'), FILTER_VALIDATE_BOOLEAN));
        }

        $perPage = (int) $request->input('per_page', 15);
        $grupos = $query->orderByDesc('id')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $grupos->items(),
            'pagination' => [
                'current_page' => $grupos->currentPage(),
                'last_page' => $grupos->lastPage(),
                'per_page' => $grupos->perPage(),
                'total' => $grupos->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255', 'unique:grupo_acessos,nome'],
            'descricao' => ['nullable', 'string'],
            'cor' => ['nullable', 'string', 'max:7'],
            'permissoes' => ['nullable', 'array'],
            'permissoes.*' => ['string', 'max:255'],
            'ativo' => ['sometimes', 'boolean'],
        ]);

        $grupo = GrupoAcesso::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Grupo de acesso criado com sucesso',
            'data' => $grupo,
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $grupo = GrupoAcesso::find($id);
        if (!$grupo) {
            return response()->json(['success' => false, 'message' => 'Grupo de acesso não encontrado'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $grupo,
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $grupo = GrupoAcesso::find($id);
        if (!$grupo) {
            return response()->json(['success' => false, 'message' => 'Grupo de acesso não encontrado'], 404);
        }

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255', 'unique:grupo_acessos,nome,' . $grupo->id],
            'descricao' => ['nullable', 'string'],
            'cor' => ['nullable', 'string', 'max:7'],
            'permissoes' => ['nullable', 'array'],
            'permissoes.*' => ['string', 'max:255'],
            'ativo' => ['sometimes', 'boolean'],
        ]);

        $grupo->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Grupo de acesso atualizado com sucesso',
            'data' => $grupo,
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $grupo = GrupoAcesso::find($id);
        if (!$grupo) {
            return response()->json(['success' => false, 'message' => 'Grupo de acesso não encontrado'], 404);
        }

        $grupo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Grupo de acesso removido com sucesso',
        ]);
    }
}
