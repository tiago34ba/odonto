<?php

namespace App\Http\Controllers;

use App\Models\Procedure;
use App\Http\Requests\ProcedureRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProcedureController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Procedure::query();

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        if ($request->filled('active')) {
            $query->where('active', filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('accepts_agreement')) {
            $query->where('accepts_agreement', filter_var($request->input('accepts_agreement'), FILTER_VALIDATE_BOOLEAN));
        }

        $perPage = (int) $request->input('per_page', 15);
        $procedures = $query->orderByDesc('id')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Procedimentos listados com sucesso',
            'data' => $procedures->items(),
            'pagination' => [
                'current_page' => $procedures->currentPage(),
                'last_page'    => $procedures->lastPage(),
                'per_page'     => $procedures->perPage(),
                'total'        => $procedures->total(),
            ],
        ]);
    }

    public function store(ProcedureRequest $request): JsonResponse
    {
        $procedure = Procedure::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Procedimento criado com sucesso',
            'data'    => $procedure,
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $procedure = Procedure::find($id);

        if (!$procedure) {
            return response()->json(['success' => false, 'message' => 'Procedimento não encontrado'], 404);
        }

        return response()->json(['success' => true, 'data' => $procedure]);
    }

    public function update(ProcedureRequest $request, $id): JsonResponse
    {
        $procedure = Procedure::find($id);

        if (!$procedure) {
            return response()->json(['success' => false, 'message' => 'Procedimento não encontrado'], 404);
        }

        $procedure->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Procedimento atualizado com sucesso',
            'data'    => $procedure->fresh(),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $procedure = Procedure::find($id);

        if (!$procedure) {
            return response()->json(['success' => false, 'message' => 'Procedimento não encontrado'], 404);
        }

        $procedure->delete();

        return response()->json(['success' => true, 'message' => 'Procedimento removido com sucesso']);
    }
}

