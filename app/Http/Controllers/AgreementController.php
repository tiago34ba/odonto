<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Http\Requests\AgreementRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgreementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Agreement::query();

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('cnpj', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('tipo', 'like', "%{$search}%")
                    ->orWhere('contato_responsavel', 'like', "%{$search}%");
            });
        }

        if ($request->filled('ativo')) {
            $query->where('ativo', filter_var($request->input('ativo'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->string('tipo'));
        }

        $perPage = (int) $request->input('per_page', 15);
        $agreements = $query->orderByDesc('id')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Convênios listados com sucesso',
            'data' => $agreements->items(),
            'pagination' => [
                'current_page' => $agreements->currentPage(),
                'last_page'    => $agreements->lastPage(),
                'per_page'     => $agreements->perPage(),
                'total'        => $agreements->total(),
            ],
        ]);
    }

    public function store(AgreementRequest $request): JsonResponse
    {
        $agreement = Agreement::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Convênio criado com sucesso',
            'data'    => $agreement,
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $agreement = Agreement::find($id);

        if (!$agreement) {
            return response()->json(['success' => false, 'message' => 'Convênio não encontrado'], 404);
        }

        return response()->json(['success' => true, 'data' => $agreement]);
    }

    public function update(AgreementRequest $request, $id): JsonResponse
    {
        $agreement = Agreement::find($id);

        if (!$agreement) {
            return response()->json(['success' => false, 'message' => 'Convênio não encontrado'], 404);
        }

        $agreement->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Convênio atualizado com sucesso',
            'data'    => $agreement->fresh(),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $agreement = Agreement::find($id);

        if (!$agreement) {
            return response()->json(['success' => false, 'message' => 'Convênio não encontrado'], 404);
        }

        $agreement->delete();

        return response()->json(['success' => true, 'message' => 'Convênio removido com sucesso']);
    }
}

