<?php

namespace App\Http\Controllers;

use App\Models\GroupAnamnese;
use App\Http\Requests\GroupAnamneseRequest;
use Illuminate\Http\Request;

class GroupAnamneseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $groups = GroupAnamnese::all();
        return response()->json($groups);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Se for API, normalmente não precisa implementar
        return response()->json(['message' => 'Formulário de criação'], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GroupAnamneseRequest $request)
    {
        $group = GroupAnamnese::create($request->validated());
        return response()->json($group, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(GroupAnamnese $groupAnamnese)
    {
        return response()->json($groupAnamnese);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GroupAnamnese $groupAnamnese)
    {
        // Se for API, normalmente não precisa implementar
        return response()->json($groupAnamnese);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(GroupAnamneseRequest $request, GroupAnamnese $groupAnamnese)
    {
        $groupAnamnese->update($request->validated());
        return response()->json($groupAnamnese);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GroupAnamnese $groupAnamnese)
    {
        $groupAnamnese->delete();
        return response()->json(['message' => 'Deletado com sucesso']);
    }
}
