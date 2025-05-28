<?php

namespace App\Http\Controllers;

use App\Models\Procedure;
use App\Http\Requests\ProcedureRequest;
use Illuminate\Http\Request;

class ProcedureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $procedures = Procedure::all();
        return view('procedures.index', compact('procedures'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('procedures.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProcedureRequest $request)
    {
        Procedure::create($request->validated());
        return redirect()->route('procedures.index')->with('success', 'Procedimento criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Procedure $procedure)
    {
        return view('procedures.show', compact('procedure'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Procedure $procedure)
    {
        return view('procedures.edit', compact('procedure'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProcedureRequest $request, Procedure $procedure)
    {
        $procedure->update($request->validated());
        return redirect()->route('procedures.index')->with('success', 'Procedimento atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Procedure $procedure)
    {
        $procedure->delete();
        return redirect()->route('procedures.index')->with('success', 'Procedimento removido com sucesso!');
    }
}
