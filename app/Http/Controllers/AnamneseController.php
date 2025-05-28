<?php

namespace App\Http\Controllers;

use App\Models\Anamnese;
use App\Http\Requests\AnamneseRequest;
use Illuminate\Http\Request;

class AnamneseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $anamneses = Anamnese::all();
        return view('anamneses.index', compact('anamneses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('anamneses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AnamneseRequest $request)
    {
        Anamnese::create($request->validated());
        return redirect()->route('anamneses.index')->with('success', 'Item cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Anamnese $anamnese)
    {
        return view('anamneses.show', compact('anamnese'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Anamnese $anamnese)
    {
        return view('anamneses.edit', compact('anamnese'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AnamneseRequest $request, Anamnese $anamnese)
    {
        $anamnese->update($request->validated());
        return redirect()->route('anamneses.index')->with('success', 'Item atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Anamnese $anamnese)
    {
        $anamnese->delete();
        return redirect()->route('anamneses.index')->with('success', 'Item removido com sucesso!');
    }
}
