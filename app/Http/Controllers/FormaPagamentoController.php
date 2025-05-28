<?php

namespace App\Http\Controllers;

use App\Models\FormaPagamento;
use App\Http\Requests\FormaPagamentoRequest;
use Illuminate\Http\Request;

class FormaPagamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $formas = FormaPagamento::all();
        return response()->json($formas);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FormaPagamentoRequest $request)
    {
        $forma = FormaPagamento::create($request->validated());
        return response()->json($forma, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(FormaPagamento $formaPagamento)
    {
        return response()->json($formaPagamento);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FormaPagamentoRequest $request, FormaPagamento $formaPagamento)
    {
        $formaPagamento->update($request->validated());
        return response()->json($formaPagamento);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FormaPagamento $formaPagamento)
    {
        $formaPagamento->delete();
        return response()->json(['message' => 'Forma de pagamento deletada com sucesso']);
    }
}
