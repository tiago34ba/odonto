<?php

namespace App\Http\Controllers;

use App\Models\FormaPagamento;
use App\Http\Requests\FormaPagamentoRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FormaPagamentoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = FormaPagamento::query();

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                    ->orWhere('tipo', 'like', "%{$search}%")
                    ->orWhere('codigo', 'like', "%{$search}%");
            });
        }

        if ($request->filled('ativo')) {
            $query->where('ativo', filter_var($request->input('ativo'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->string('tipo'));
        }

        $perPage = (int) $request->input('per_page', 15);
        $formas = $query->orderByDesc('id')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Formas de pagamento listadas com sucesso',
            'data' => $formas->items(),
            'pagination' => [
                'current_page' => $formas->currentPage(),
                'last_page'    => $formas->lastPage(),
                'per_page'     => $formas->perPage(),
                'total'        => $formas->total(),
            ],
        ]);
    }

    public function store(FormaPagamentoRequest $request): JsonResponse
    {
        $forma = FormaPagamento::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Forma de pagamento criada com sucesso',
            'data'    => $forma,
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $forma = FormaPagamento::find($id);

        if (!$forma) {
            return response()->json(['success' => false, 'message' => 'Forma de pagamento não encontrada'], 404);
        }

        return response()->json(['success' => true, 'data' => $forma]);
    }

    public function update(FormaPagamentoRequest $request, $id): JsonResponse
    {
        $forma = FormaPagamento::find($id);

        if (!$forma) {
            return response()->json(['success' => false, 'message' => 'Forma de pagamento não encontrada'], 404);
        }

        $forma->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Forma de pagamento atualizada com sucesso',
            'data'    => $forma->fresh(),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $forma = FormaPagamento::find($id);

        if (!$forma) {
            return response()->json(['success' => false, 'message' => 'Forma de pagamento não encontrada'], 404);
        }

        $forma->delete();

        return response()->json(['success' => true, 'message' => 'Forma de pagamento removida com sucesso']);
    }
}

