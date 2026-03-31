<?php

namespace App\Http\Controllers;

use App\Models\GroupAnamnese;
use App\Http\Requests\GroupAnamneseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupAnamneseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = GroupAnamnese::query();

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                    ->orWhere('descricao', 'like', "%{$search}%")
                    ->orWhere('codigo', 'like', "%{$search}%");
            });
        }

        if ($request->filled('ativo')) {
            $query->where('ativo', filter_var($request->input('ativo'), FILTER_VALIDATE_BOOLEAN));
        }

        $perPage = (int) $request->input('per_page', 15);
        $groups = $query->orderBy('ordem')->orderByDesc('id')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Grupos de anamnese listados com sucesso',
            'data' => $groups->items(),
            'pagination' => [
                'current_page' => $groups->currentPage(),
                'last_page'    => $groups->lastPage(),
                'per_page'     => $groups->perPage(),
                'total'        => $groups->total(),
            ],
        ]);
    }

    public function store(GroupAnamneseRequest $request): JsonResponse
    {
        $group = GroupAnamnese::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Grupo de anamnese criado com sucesso',
            'data'    => $group,
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $group = GroupAnamnese::find($id);
        if (!$group) {
            return response()->json(['success' => false, 'message' => 'Grupo de anamnese não encontrado'], 404);
        }

        return response()->json(['success' => true, 'data' => $group]);
    }

    public function update(GroupAnamneseRequest $request, $id): JsonResponse
    {
        $group = GroupAnamnese::find($id);
        if (!$group) {
            return response()->json(['success' => false, 'message' => 'Grupo de anamnese não encontrado'], 404);
        }

        $group->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Grupo de anamnese atualizado com sucesso',
            'data'    => $group->fresh(),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $group = GroupAnamnese::find($id);
        if (!$group) {
            return response()->json(['success' => false, 'message' => 'Grupo de anamnese não encontrado'], 404);
        }

        $group->delete();

        return response()->json(['success' => true, 'message' => 'Grupo de anamnese removido com sucesso']);
    }
}

