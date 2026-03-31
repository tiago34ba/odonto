<?php

namespace App\Http\Controllers;

use App\Models\Acesso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AcessoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Acesso::query();

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                    ->orWhere('codigo', 'like', "%{$search}%")
                    ->orWhere('descricao', 'like', "%{$search}%")
                    ->orWhere('categoria', 'like', "%{$search}%");
            });
        }

        if ($request->filled('nivel_risco')) {
            $query->where('nivel_risco', $request->string('nivel_risco'));
        }

        if ($request->filled('ativo')) {
            $query->where('ativo', filter_var($request->input('ativo'), FILTER_VALIDATE_BOOLEAN));
        }

        $perPage = (int) $request->input('per_page', 15);
        $acessos = $query->orderByDesc('id')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $acessos->items(),
            'pagination' => [
                'current_page' => $acessos->currentPage(),
                'last_page' => $acessos->lastPage(),
                'per_page' => $acessos->perPage(),
                'total' => $acessos->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'codigo' => ['required', 'string', 'max:255', 'unique:acessos,codigo'],
            'descricao' => ['nullable', 'string'],
            'categoria' => ['required', 'string', 'max:255'],
            'nivel_risco' => ['required', 'in:baixo,medio,alto,critico'],
            'sistema_interno' => ['sometimes', 'boolean'],
            'ativo' => ['sometimes', 'boolean'],
            'grupo_acesso_id' => ['nullable', 'integer', 'exists:grupo_acessos,id'],
        ]);

        $acesso = Acesso::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Acesso criado com sucesso',
            'data' => $acesso,
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $acesso = Acesso::find($id);
        if (!$acesso) {
            return response()->json(['success' => false, 'message' => 'Acesso não encontrado'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $acesso,
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $acesso = Acesso::find($id);
        if (!$acesso) {
            return response()->json(['success' => false, 'message' => 'Acesso não encontrado'], 404);
        }

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'codigo' => ['required', 'string', 'max:255', 'unique:acessos,codigo,' . $acesso->id],
            'descricao' => ['nullable', 'string'],
            'categoria' => ['required', 'string', 'max:255'],
            'nivel_risco' => ['required', 'in:baixo,medio,alto,critico'],
            'sistema_interno' => ['sometimes', 'boolean'],
            'ativo' => ['sometimes', 'boolean'],
            'grupo_acesso_id' => ['nullable', 'integer', 'exists:grupo_acessos,id'],
        ]);

        $acesso->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Acesso atualizado com sucesso',
            'data' => $acesso,
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $acesso = Acesso::find($id);
        if (!$acesso) {
            return response()->json(['success' => false, 'message' => 'Acesso não encontrado'], 404);
        }

        $acesso->delete();

        return response()->json([
            'success' => true,
            'message' => 'Acesso removido com sucesso',
        ]);
    }
}
