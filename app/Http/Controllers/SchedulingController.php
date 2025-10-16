<?php

namespace App\Http\Controllers;

use App\Models\Scheduling;
use App\Models\Paciente;
use App\Models\Employee;
use App\Models\Procedure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\SchedulingRequest;
use Carbon\Carbon;

class SchedulingController extends Controller
{
    /**
     * Listar agendamentos com filtros
     */
    public function index(Request $request): JsonResponse
    {
        $query = Scheduling::with(['paciente', 'profissional', 'procedimento']);

        // Filtros
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('professional_id')) {
            $query->where('professional_id', $request->professional_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('patient_name')) {
            $query->whereHas('paciente', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->patient_name . '%');
            });
        }

        // Ordenação
        $query->orderBy($request->get('sort_by', 'date'), $request->get('sort_order', 'asc'));

        $schedulings = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $schedulings->items(),
            'pagination' => [
                'current_page' => $schedulings->currentPage(),
                'last_page' => $schedulings->lastPage(),
                'per_page' => $schedulings->perPage(),
                'total' => $schedulings->total(),
            ]
        ]);
    }

    /**
     * Criar novo agendamento
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:pacientes,id',
            'professional_id' => 'required|exists:employees,id',
            'procedure_id' => 'required|exists:procedures,id',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|string|regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/',
            'return' => 'boolean',
            'obs' => 'nullable|string|max:500',
            'duration' => 'nullable|integer|min:15|max:480', // 15min a 8h
        ]);

        // Verificar conflitos de horário
        $conflict = $this->checkScheduleConflict(
            $validated['professional_id'],
            $validated['date'],
            $validated['time'],
            $validated['duration'] ?? 60
        );

        if ($conflict) {
            return response()->json([
                'message' => 'Conflito de horário detectado',
                'error' => 'Este horário já está ocupado'
            ], 422);
        }

        $scheduling = Scheduling::create($validated);
        $scheduling->load(['paciente', 'profissional', 'procedimento']);

        return response()->json($scheduling, 201);
    }

    /**
     * Mostrar agendamento específico
     */
    public function show($id): JsonResponse
    {
        $scheduling = Scheduling::with(['paciente', 'profissional', 'procedimento'])->find($id);
        
        if (!$scheduling) {
            return response()->json(['message' => 'Agendamento não encontrado'], 404);
        }

        return response()->json($scheduling);
    }

    /**
     * Atualizar agendamento
     */
    public function update(Request $request, $id): JsonResponse
    {
        $scheduling = Scheduling::find($id);
        
        if (!$scheduling) {
            return response()->json(['message' => 'Agendamento não encontrado'], 404);
        }

        $validated = $request->validate([
            'patient_id' => 'sometimes|exists:pacientes,id',
            'professional_id' => 'sometimes|exists:employees,id',
            'procedure_id' => 'sometimes|exists:procedures,id',
            'date' => 'sometimes|date',
            'time' => 'sometimes|string|regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/',
            'return' => 'sometimes|boolean',
            'obs' => 'nullable|string|max:500',
            'status' => 'sometimes|in:scheduled,confirmed,in_progress,completed,canceled,no_show',
            'duration' => 'nullable|integer|min:15|max:480',
        ]);

        // Verificar conflitos apenas se data, hora ou profissional mudaram
        if (isset($validated['date']) || isset($validated['time']) || isset($validated['professional_id'])) {
            $professionalId = $validated['professional_id'] ?? $scheduling->professional_id;
            $date = $validated['date'] ?? $scheduling->date;
            $time = $validated['time'] ?? $scheduling->time;
            $duration = $validated['duration'] ?? $scheduling->duration ?? 60;

            $conflict = $this->checkScheduleConflict($professionalId, $date, $time, $duration, $scheduling->id);

            if ($conflict) {
                return response()->json([
                    'message' => 'Conflito de horário detectado',
                    'error' => 'Este horário já está ocupado'
                ], 422);
            }
        }

        $scheduling->update($validated);
        $scheduling->load(['paciente', 'profissional', 'procedimento']);

        return response()->json($scheduling);
    }

    /**
     * Deletar agendamento
     */
    public function destroy($id): JsonResponse
    {
        $scheduling = Scheduling::find($id);
        
        if (!$scheduling) {
            return response()->json(['message' => 'Agendamento não encontrado'], 404);
        }

        $scheduling->delete();
        
        return response()->json(['message' => 'Agendamento deletado com sucesso']);
    }

    /**
     * Confirmar agendamento
     */
    public function confirm($id): JsonResponse
    {
        $scheduling = Scheduling::find($id);
        
        if (!$scheduling) {
            return response()->json(['message' => 'Agendamento não encontrado'], 404);
        }

        $scheduling->confirm();
        
        return response()->json([
            'message' => 'Agendamento confirmado com sucesso',
            'scheduling' => $scheduling
        ]);
    }

    /**
     * Cancelar agendamento
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        $scheduling = Scheduling::find($id);
        
        if (!$scheduling) {
            return response()->json(['message' => 'Agendamento não encontrado'], 404);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        $scheduling->cancel($validated['reason'] ?? null);
        
        return response()->json([
            'message' => 'Agendamento cancelado com sucesso',
            'scheduling' => $scheduling
        ]);
    }

    /**
     * Marcar como concluído
     */
    public function complete($id): JsonResponse
    {
        $scheduling = Scheduling::find($id);
        
        if (!$scheduling) {
            return response()->json(['message' => 'Agendamento não encontrado'], 404);
        }

        $scheduling->complete();
        
        return response()->json([
            'message' => 'Agendamento concluído com sucesso',
            'scheduling' => $scheduling
        ]);
    }

    /**
     * Agenda do dia
     */
    public function today(Request $request): JsonResponse
    {
        $query = Scheduling::with(['paciente', 'profissional', 'procedimento'])
                           ->whereDate('date', today());

        if ($request->filled('professional_id')) {
            $query->where('professional_id', $request->professional_id);
        }

        $schedulings = $query->orderBy('time')->get();

        return response()->json($schedulings);
    }

    /**
     * Agenda da semana
     */
    public function thisWeek(Request $request): JsonResponse
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $query = Scheduling::with(['paciente', 'profissional', 'procedimento'])
                           ->whereBetween('date', [$startOfWeek, $endOfWeek]);

        if ($request->filled('professional_id')) {
            $query->where('professional_id', $request->professional_id);
        }

        $schedulings = $query->orderBy('date')->orderBy('time')->get();

        return response()->json($schedulings);
    }

    /**
     * Horários disponíveis
     */
    public function availableSlots(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'professional_id' => 'required|exists:employees,id',
            'date' => 'required|date|after_or_equal:today',
            'duration' => 'integer|min:15|max:480'
        ]);

        $duration = $validated['duration'] ?? 60;
        $availableSlots = $this->getAvailableSlots(
            $validated['professional_id'],
            $validated['date'],
            $duration
        );

        return response()->json(['available_slots' => $availableSlots]);
    }

    /**
     * Métodos auxiliares privados
     */

    /**
     * Verificar conflitos de horário
     */
    private function checkScheduleConflict($professionalId, $date, $time, $duration = 60, $excludeId = null): bool
    {
        $startTime = Carbon::parse($date . ' ' . $time);
        $endTime = $startTime->copy()->addMinutes($duration);

        $query = Scheduling::where('professional_id', $professionalId)
                          ->whereDate('date', $date)
                          ->where('status', '!=', 'canceled');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $existingSchedulings = $query->get();

        foreach ($existingSchedulings as $existing) {
            $existingStart = Carbon::parse($existing->date . ' ' . $existing->time);
            $existingEnd = $existingStart->copy()->addMinutes($existing->duration ?? 60);

            // Verificar sobreposição
            if ($startTime->lt($existingEnd) && $endTime->gt($existingStart)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obter horários disponíveis
     */
    private function getAvailableSlots($professionalId, $date, $duration = 60): array
    {
        // Horário de funcionamento padrão: 8h às 18h
        $workStart = Carbon::parse($date . ' 08:00');
        $workEnd = Carbon::parse($date . ' 18:00');
        
        $slots = [];
        $current = $workStart->copy();

        while ($current->addMinutes($duration)->lte($workEnd)) {
            if (!$this->checkScheduleConflict($professionalId, $date, $current->format('H:i'), $duration)) {
                $slots[] = $current->format('H:i');
            }
            $current->addMinutes(30); // Intervalo de 30 minutos entre slots
        }

        return $slots;
    }
}
