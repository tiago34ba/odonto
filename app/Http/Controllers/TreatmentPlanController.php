<?php

namespace App\Http\Controllers;

use App\Models\TreatmentPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TreatmentPlanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TreatmentPlan::with('paciente');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('numero', 'like', "%{$search}%")
                    ->orWhere('tipo', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('profissional', 'like', "%{$search}%")
                    ->orWhereHas('paciente', function ($patientQuery) use ($search) {
                        $patientQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('data', '>=', $request->string('data_inicio'));
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('data', '<=', $request->string('data_fim'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $orcamentos = $query->orderByDesc('data')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Orcamentos listados com sucesso',
            'data' => $orcamentos,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'tipo' => 'required|string|max:150',
            'valor' => 'required|numeric|min:0.01',
            'status' => 'required|in:Concluído,Pendente,Aprovado,Rejeitado',
            'data' => 'required|date',
            'dias_validade' => 'required|integer|min:1|max:365',
            'procedimentos' => 'nullable|array',
            'procedimentos.*.nome' => 'required_with:procedimentos|string|max:150',
            'procedimentos.*.quantidade' => 'required_with:procedimentos|integer|min:1',
            'procedimentos.*.valorUnit' => 'required_with:procedimentos|numeric|min:0',
            'procedimentos.*.total' => 'required_with:procedimentos|numeric|min:0',
            'desconto' => 'nullable|numeric|min:0',
            'desconto_tipo' => 'nullable|in:%,R$',
            'forma_pagamento' => 'nullable|string|max:100',
            'profissional' => 'nullable|string|max:150',
            'odontograma' => 'nullable|string|max:150',
            'observacoes' => 'nullable|string|max:2000',
        ]);

        $orcamento = TreatmentPlan::create([
            'paciente_id' => $validated['paciente_id'],
            'tipo' => $validated['tipo'],
            'valor' => $validated['valor'],
            'status' => $validated['status'],
            'data' => $validated['data'],
            'dias_validade' => $validated['dias_validade'],
            'procedimentos' => $validated['procedimentos'] ?? [],
            'desconto' => $validated['desconto'] ?? 0,
            'desconto_tipo' => $validated['desconto_tipo'] ?? '%',
            'forma_pagamento' => $validated['forma_pagamento'] ?? null,
            'profissional' => $validated['profissional'] ?? null,
            'odontograma' => $validated['odontograma'] ?? null,
            'observacoes' => $validated['observacoes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Orcamento criado com sucesso',
            'data' => $orcamento->load('paciente'),
        ], 201);
    }

    public function show(TreatmentPlan $treatmentPlan): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Orcamento encontrado com sucesso',
            'data' => $treatmentPlan->load('paciente'),
        ]);
    }

    public function update(Request $request, TreatmentPlan $treatmentPlan): JsonResponse
    {
        $validated = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'tipo' => 'required|string|max:150',
            'valor' => 'required|numeric|min:0.01',
            'status' => 'required|in:Concluído,Pendente,Aprovado,Rejeitado',
            'data' => 'required|date',
            'dias_validade' => 'required|integer|min:1|max:365',
            'procedimentos' => 'nullable|array',
            'procedimentos.*.nome' => 'required_with:procedimentos|string|max:150',
            'procedimentos.*.quantidade' => 'required_with:procedimentos|integer|min:1',
            'procedimentos.*.valorUnit' => 'required_with:procedimentos|numeric|min:0',
            'procedimentos.*.total' => 'required_with:procedimentos|numeric|min:0',
            'desconto' => 'nullable|numeric|min:0',
            'desconto_tipo' => 'nullable|in:%,R$',
            'forma_pagamento' => 'nullable|string|max:100',
            'profissional' => 'nullable|string|max:150',
            'odontograma' => 'nullable|string|max:150',
            'observacoes' => 'nullable|string|max:2000',
        ]);

        $treatmentPlan->update([
            'paciente_id' => $validated['paciente_id'],
            'tipo' => $validated['tipo'],
            'valor' => $validated['valor'],
            'status' => $validated['status'],
            'data' => $validated['data'],
            'dias_validade' => $validated['dias_validade'],
            'procedimentos' => $validated['procedimentos'] ?? [],
            'desconto' => $validated['desconto'] ?? 0,
            'desconto_tipo' => $validated['desconto_tipo'] ?? '%',
            'forma_pagamento' => $validated['forma_pagamento'] ?? null,
            'profissional' => $validated['profissional'] ?? null,
            'odontograma' => $validated['odontograma'] ?? null,
            'observacoes' => $validated['observacoes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Orcamento atualizado com sucesso',
            'data' => $treatmentPlan->fresh()->load('paciente'),
        ]);
    }

    public function destroy(TreatmentPlan $treatmentPlan): JsonResponse
    {
        $treatmentPlan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Orcamento excluido com sucesso',
        ]);
    }
}
