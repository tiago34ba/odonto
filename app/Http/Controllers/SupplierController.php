<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Http\Requests\SupplierRequest;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * Query params:
     *   search  – nome, email, cnpj, contato (LIKE)
     *   tipo    – exact match
     *   status  – 0 | 1 | 2
     *   per_page – default 15, max 100
     *   page    – default 1
     */
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name',     'like', "%{$search}%")
                  ->orWhere('email',  'like', "%{$search}%")
                  ->orWhere('cnpj',   'like', "%{$search}%")
                  ->orWhere('contato','like', "%{$search}%")
                  ->orWhere('categoria','like', "%{$search}%");
            });
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->query('tipo'));
        }

        if ($request->filled('status')) {
            $query->where('status', (int) $request->query('status'));
        }

        $perPage = min((int) $request->query('per_page', 15), 100);
        $paginated = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Fornecedores listados com sucesso',
            'data'    => $paginated->items(),
            'total'   => $paginated->total(),
            'current_page' => $paginated->currentPage(),
            'last_page'    => $paginated->lastPage(),
            'per_page'     => $paginated->perPage(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SupplierRequest $request)
    {
        $supplier = Supplier::create($request->validated());
        return response()->json($supplier, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            return response()->json(['message' => 'Fornecedor não encontrado'], 404);
        }
        return response()->json($supplier);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SupplierRequest $request, $id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            return response()->json(['message' => 'Fornecedor não encontrado'], 404);
        }
        $supplier->update($request->validated());
        return response()->json($supplier);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            return response()->json(['message' => 'Fornecedor não encontrado'], 404);
        }
        $supplier->delete();
        return response()->json(['message' => 'Fornecedor deletado com sucesso']);
    }
}
