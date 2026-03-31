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
     * @var array<int, string>
     */
    private array $allowedSortColumns = [
        'date',
        'time',
        'status',
        'created_at',
        'updated_at',
    ];

    private function mapStatusToTable(string $status): string
    {
        $map = [
            'scheduled'   => 'agendado',
            'confirmed'   => 'confirmado',
            'in_progress' => 'em_atendimento',
            'completed'   => 'concluido',
            'canceled'    => 'cancelado',
            'cancelled'   => 'cancelado',
            'no_show'     => 'cancelado',
        ];

        return $map[$status] ?? 'agendado';
    }

    private function formatSchedulingForTable(Scheduling $scheduling): array
    {
        $date = $scheduling->date instanceof Carbon
            ? $scheduling->date->format('Y-m-d')
            : (string) $scheduling->date;

        return [
            'id'          => $scheduling->id,
            'patient_id'  => $scheduling->patient_id,
            'professional_id' => $scheduling->professional_id,
            'procedure_id' => $scheduling->procedure_id,
            'date'        => $date,
            'time'        => $scheduling->time,
            'return'      => (bool) $scheduling->return,
            'obs'         => $scheduling->obs,
            'paciente'    => $scheduling->paciente?->name ?? '',
            'dentista'    => $scheduling->profissional?->name ?? '',
            'procedimento'=> $scheduling->procedimento?->name ?? '',
            'data'        => $date,
            'hora'        => $scheduling->time,
            'status'      => $this->mapStatusToTable((string) $scheduling->status),
            'telefone'    => $scheduling->paciente?->phone ?? $scheduling->paciente?->telefone ?? '',
            'observacoes' => $scheduling->obs,
        ];
    }

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

        // Ordenacao com whitelist para evitar injecao no orderBy.
        $sortBy = (string) $request->get('sort_by', 'date');
        if (! in_array($sortBy, $this->allowedSortColumns, true)) {
            $sortBy = 'date';
        }

        $sortOrder = mb_strtolower((string) $request->get('sort_order', 'asc'));
        if (! in_array($sortOrder, ['asc', 'desc'], true)) {
            $sortOrder = 'asc';
        }

        $query->orderBy($sortBy, $sortOrder);

        $schedulings = $query->paginate($request->get('per_page', 15));
        $formattedData = collect($schedulings->items())
            ->map(fn (Scheduling $scheduling) => $this->formatSchedulingForTable($scheduling))
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'data' => $formattedData,
            'pagination' => [
                'current_page' => $schedulings->currentPage(),
                'last_page'    => $schedulings->lastPage(),
                'per_page'     => $schedulings->perPage(),
                'total'        => $schedulings->total(),
            ],
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
            'time' => ['required', 'string', 'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'return' => 'boolean',
            'obs' => 'nullable|string|max:500',
            'duration' => 'nullable|integer|min:15|max:480', // 15min a 8h
        ]);

        // Regra de negócio: não permitir agendamento duplicado
        if ($this->hasDuplicateScheduling(
            $validated['patient_id'],
            $validated['professional_id'],
            $validated['procedure_id'],
            $validated['date'],
            $validated['time']
        )) {
            return response()->json([
                'success' => false,
                'message' => 'Dados duplicados: já existe um agendamento com paciente, dentista, procedimento, data e hora iguais.',
            ], 422);
        }

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

        $scheduling = Scheduling::create(array_merge($validated, [
            'status'       => 'scheduled',
            'scheduled_at' => now(),
        ]));
        $scheduling->load(['paciente', 'profissional', 'procedimento']);

        return response()->json([
            'success' => true,
            'message' => 'Agendamento criado com sucesso',
            'data'    => $this->formatSchedulingForTable($scheduling),
        ], 201);
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

        return response()->json([
            'success' => true,
            'data' => $this->formatSchedulingForTable($scheduling),
        ]);
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
            'time' => ['sometimes', 'string', 'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'return' => 'sometimes|boolean',
            'obs' => 'nullable|string|max:500',
            'status' => 'sometimes|in:scheduled,confirmed,in_progress,completed,canceled,no_show',
            'duration' => 'nullable|integer|min:15|max:480',
        ]);

        // Regra de negócio: não permitir duplicação ao atualizar
        $patientId = $validated['patient_id'] ?? $scheduling->patient_id;
        $professionalId = $validated['professional_id'] ?? $scheduling->professional_id;
        $procedureId = $validated['procedure_id'] ?? $scheduling->procedure_id;
        $date = $validated['date'] ?? $scheduling->date;
        $time = $validated['time'] ?? $scheduling->time;

        if ($this->hasDuplicateScheduling(
            $patientId,
            $professionalId,
            $procedureId,
            $date,
            $time,
            $scheduling->id
        )) {
            return response()->json([
                'success' => false,
                'message' => 'Dados duplicados: já existe outro agendamento com paciente, dentista, procedimento, data e hora iguais.',
            ], 422);
        }

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

        return response()->json([
            'success' => true,
            'message' => 'Agendamento atualizado com sucesso',
            'data' => $this->formatSchedulingForTable($scheduling),
        ]);
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
        $scheduling->load(['paciente', 'profissional', 'procedimento']);

        return response()->json([
            'success' => true,
            'message' => 'Agendamento confirmado com sucesso',
            'data' => $this->formatSchedulingForTable($scheduling),
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
        $scheduling->load(['paciente', 'profissional', 'procedimento']);

        return response()->json([
            'success' => true,
            'message' => 'Agendamento cancelado com sucesso',
            'data' => $this->formatSchedulingForTable($scheduling),
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
        $scheduling->load(['paciente', 'profissional', 'procedimento']);

        return response()->json([
            'success' => true,
            'message' => 'Agendamento concluído com sucesso',
            'data' => $this->formatSchedulingForTable($scheduling),
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
        $formattedData = $schedulings
            ->map(fn (Scheduling $scheduling) => $this->formatSchedulingForTable($scheduling))
            ->values();

        return response()->json([
            'success' => true,
            'data' => $formattedData,
        ]);
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
        $formattedData = $schedulings
            ->map(fn (Scheduling $scheduling) => $this->formatSchedulingForTable($scheduling))
            ->values();

        return response()->json([
            'success' => true,
            'data' => $formattedData,
        ]);
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
        $dateString = $date instanceof Carbon
            ? $date->toDateString()
            : Carbon::parse((string) $date)->toDateString();

        $durationMinutes = (int) $duration;
        $startTime = Carbon::parse($dateString . ' ' . $time);
        $endTime = $startTime->copy()->addMinutes($durationMinutes);

        $query = Scheduling::where('professional_id', $professionalId)
                          ->whereDate('date', $dateString)
                          ->where('status', '!=', 'canceled');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $existingSchedulings = $query->get();

        foreach ($existingSchedulings as $existing) {
            $existingDateString = $existing->date instanceof Carbon
                ? $existing->date->toDateString()
                : Carbon::parse((string) $existing->date)->toDateString();

            $existingStart = Carbon::parse($existingDateString . ' ' . $existing->time);
            $existingEnd = $existingStart->copy()->addMinutes((int) ($existing->duration ?? 60));

            // Verificar sobreposição
            if ($startTime->lt($existingEnd) && $endTime->gt($existingStart)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verificar duplicidade de dados principais do agendamento
     */
    private function hasDuplicateScheduling($patientId, $professionalId, $procedureId, $date, $time, $excludeId = null): bool
    {
        $query = Scheduling::query()
            ->where('patient_id', $patientId)
            ->where('professional_id', $professionalId)
            ->where('procedure_id', $procedureId)
            ->whereDate('date', $date)
            ->where('time', $time)
            ->where('status', '!=', 'canceled');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Obter horários disponíveis
     */
    private function getAvailableSlots($professionalId, $date, $duration = 60): array
    {
        $durationMinutes = (int) $duration;

        // Horário de funcionamento padrão: 8h às 18h
        $workStart = Carbon::parse($date . ' 08:00');
        $workEnd = Carbon::parse($date . ' 18:00');

        $slots = [];
        $current = $workStart->copy();

        while ($current->copy()->addMinutes($durationMinutes)->lte($workEnd)) {
            if (!$this->checkScheduleConflict($professionalId, $date, $current->format('H:i'), $durationMinutes)) {
                $slots[] = $current->format('H:i');
            }
            $current->addMinutes(30); // Intervalo de 30 minutos entre slots
        }

        return $slots;
    }
}
