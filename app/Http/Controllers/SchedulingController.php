<?php

namespace App\Http\Controllers;

use App\Models\Scheduling;
use Illuminate\Http\Request;
use App\Http\Requests\SchedulingRequest;

class SchedulingController extends Controller
{
    // Listar todos os agendamentos
    public function index()
    {
        return response()->json(Scheduling::all());
    }

    // Criar novo agendamento
    public function store(SchedulingRequest $request)
    {
        $scheduling = Scheduling::create($request->validated());
        return response()->json($scheduling, 201);
    }

    // Mostrar agendamento específico
    public function show($id)
    {
        $scheduling = Scheduling::find($id);
        if (!$scheduling) {
            return response()->json(['message' => 'Agendamento não encontrado'], 404);
        }
        return response()->json($scheduling);
    }

    // Atualizar agendamento
    public function update(Request $request, $id)
    {
        $scheduling = Scheduling::find($id);
        if (!$scheduling) {
            return response()->json(['message' => 'Agendamento não encontrado'], 404);
        }

        $validated = $request->validate([
            'patient_id'      => 'sometimes|required|integer|exists:patients,id',
            'professional_id' => 'sometimes|required|integer|exists:professionals,id',
            'procedure_id'    => 'sometimes|required|integer|exists:procedures,id',
            'date'            => 'sometimes|required|date',
            'time'            => 'sometimes|required|string|max:5',
            'return'          => 'sometimes|required|boolean',
            'obs'             => 'nullable|string|max:100',
        ]);

        $scheduling->update($validated);
        return response()->json($scheduling);
    }

    // Deletar agendamento
    public function destroy($id)
    {
        $scheduling = Scheduling::find($id);
        if (!$scheduling) {
            return response()->json(['message' => 'Agendamento não encontrado'], 404);
        }
        $scheduling->delete();
        return response()->json(['message' => 'Agendamento deletado com sucesso']);
    }
}
