<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CargoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Cargo::query();

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                    ->orWhere('descricao', 'like', "%{$search}%");
            });
        }

        if ($request->filled('nivel_acesso')) {
            $query->where('nivel_acesso', $request->string('nivel_acesso'));
        }

        if ($request->filled('ativo')) {
            $query->where('ativo', filter_var($request->input('ativo'), FILTER_VALIDATE_BOOLEAN));
        }

        $perPage = (int) $request->input('per_page', 15);
        $cargos = $query->orderByDesc('id')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $cargos->items(),
            'pagination' => [
                'current_page' => $cargos->currentPage(),
                'last_page' => $cargos->lastPage(),
                'per_page' => $cargos->perPage(),
                'total' => $cargos->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255', 'unique:cargos,nome'],
            'descricao' => ['nullable', 'string'],
            'nivel_acesso' => ['required', 'in:baixo,medio,alto,admin'],
            'ativo' => ['sometimes', 'boolean'],
        ]);

        $cargo = Cargo::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cargo criado com sucesso',
            'data' => $cargo,
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $cargo = Cargo::find($id);
        if (!$cargo) {
            return response()->json(['success' => false, 'message' => 'Cargo não encontrado'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $cargo,
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $cargo = Cargo::find($id);
        if (!$cargo) {
            return response()->json(['success' => false, 'message' => 'Cargo não encontrado'], 404);
        }

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255', 'unique:cargos,nome,' . $cargo->id],
            'descricao' => ['nullable', 'string'],
            'nivel_acesso' => ['required', 'in:baixo,medio,alto,admin'],
            'ativo' => ['sometimes', 'boolean'],
        ]);

        $cargo->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cargo atualizado com sucesso',
            'data' => $cargo,
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $cargo = Cargo::find($id);
        if (!$cargo) {
            return response()->json(['success' => false, 'message' => 'Cargo não encontrado'], 404);
        }

        $cargo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cargo removido com sucesso',
        ]);
    }
}
