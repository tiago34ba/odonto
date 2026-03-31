<?php

namespace App\Http\Controllers;

use App\Models\Anamnese;
use App\Http\Requests\AnamneseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnamneseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Anamnese::query();

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('group', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('codigo', 'like', "%{$search}%");
            });
        }

        if ($request->filled('group')) {
            $query->where('group', $request->string('group'));
        }

        if ($request->filled('ativo')) {
            $query->where('ativo', filter_var($request->input('ativo'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('tipo_resposta')) {
            $query->where('tipo_resposta', $request->string('tipo_resposta'));
        }

        $perPage = (int) $request->input('per_page', 15);
        $anamneses = $query->orderByDesc('id')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Itens de anamnese listados com sucesso',
            'data' => $anamneses->items(),
            'pagination' => [
                'current_page' => $anamneses->currentPage(),
                'last_page'    => $anamneses->lastPage(),
                'per_page'     => $anamneses->perPage(),
                'total'        => $anamneses->total(),
            ],
        ]);
    }

    public function store(AnamneseRequest $request): JsonResponse
    {
        $validated = $request->validated();
        if (isset($validated['opcoes_resposta']) && is_string($validated['opcoes_resposta'])) {
            $validated['opcoes_resposta'] = array_filter(array_map('trim', explode(',', $validated['opcoes_resposta'])));
        }

        $anamnese = Anamnese::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Item de anamnese criado com sucesso',
            'data'    => $anamnese,
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $anamnese = Anamnese::find($id);

        if (!$anamnese) {
            return response()->json(['success' => false, 'message' => 'Item de anamnese não encontrado'], 404);
        }

        return response()->json(['success' => true, 'data' => $anamnese]);
    }

    public function update(AnamneseRequest $request, $id): JsonResponse
    {
        $anamnese = Anamnese::find($id);

        if (!$anamnese) {
            return response()->json(['success' => false, 'message' => 'Item de anamnese não encontrado'], 404);
        }

        $validated = $request->validated();
        if (isset($validated['opcoes_resposta']) && is_string($validated['opcoes_resposta'])) {
            $validated['opcoes_resposta'] = array_filter(array_map('trim', explode(',', $validated['opcoes_resposta'])));
        }

        $anamnese->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Item de anamnese atualizado com sucesso',
            'data'    => $anamnese->fresh(),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $anamnese = Anamnese::find($id);

        if (!$anamnese) {
            return response()->json(['success' => false, 'message' => 'Item de anamnese não encontrado'], 404);
        }

        $anamnese->delete();

        return response()->json(['success' => true, 'message' => 'Item de anamnese removido com sucesso']);
    }
}

