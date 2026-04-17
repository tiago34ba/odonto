<?php

namespace App\Http\Controllers;

use App\Models\Dentista;
use App\Models\Employee;
use App\Models\Paciente;
use App\Models\Procedure;
use App\Models\Scheduling;
use App\Models\User;
use App\Services\WhatsappNotificationService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class PortalPacienteController extends Controller
{
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCK_SECONDS = 300;
    private const MAX_REGISTER_ATTEMPTS = 10;
    private const REGISTER_LOCK_SECONDS = 3600;

    public function __construct(private readonly WhatsappNotificationService $whatsappNotificationService)
    {
    }

    /**
     * Registrar novo paciente no portal
     */
    public function register(Request $request): JsonResponse
    {
        $throttleKey = 'portal-register:' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_REGISTER_ATTEMPTS)) {
            $retryAfter = RateLimiter::availableIn($throttleKey);

            return response()->json([
                'message' => "Muitas tentativas de cadastro. Aguarde {$retryAfter} segundos.",
            ], 429);
        }

        RateLimiter::hit($throttleKey, self::REGISTER_LOCK_SECONDS);

        $validated = $request->validate([
            'name'            => 'required|string|max:100',
            'email'           => 'required|email|max:150|unique:users,email|unique:pacientes,email',
            'password'        => 'required|string|min:8|confirmed',
            'telefone'        => 'nullable|string|max:20',
            'cpf_cnpj'        => 'nullable|string|max:20|unique:pacientes,cpf_cnpj',
            'data_nascimento' => 'nullable|date',
        ]);

        [$user, $paciente] = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
                'tipo'     => 'paciente',
                'ativo'    => true,
            ]);

            $paciente = Paciente::create([
                'name'            => $validated['name'],
                'email'           => $validated['email'],
                'telefone'        => $validated['telefone'] ?? null,
                'cpf_cnpj'        => $validated['cpf_cnpj'] ?? null,
                'data_nascimento' => $validated['data_nascimento'] ?? null,
                'user_id'         => $user->id,
            ]);

            return [$user, $paciente];
        });

        $token = $user->createToken('portal-web')->plainTextToken;

        return response()->json([
            'success'     => true,
            'message'     => 'Conta criada com sucesso! Bem-vindo(a).',
            'token'       => $token,
            'user'        => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'paciente_id' => $paciente->id,
            ],
        ], 201);
    }

    /**
     * Login do paciente no portal
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $throttleKey = 'portal-login:' . strtolower($validated['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            $retryAfter = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'message' => "Muitas tentativas. Aguarde {$retryAfter} segundos.",
            ], 429);
        }

        $user = User::where('email', $validated['email'])
            ->where('tipo', 'paciente')
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            RateLimiter::hit($throttleKey, self::LOCK_SECONDS);
            return response()->json(['message' => 'E-mail ou senha incorretos.'], 401);
        }

        if (! $user->ativo) {
            return response()->json(['message' => 'Conta inativa. Entre em contato com a clínica.'], 403);
        }

        RateLimiter::clear($throttleKey);
        $user->tokens()->delete();

        $token = $user->createToken('portal-web')->plainTextToken;
        $paciente = Paciente::where('user_id', $user->id)->first();

        return response()->json([
            'success' => true,
            'message' => 'Login realizado com sucesso.',
            'token'   => $token,
            'user'    => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'paciente_id' => $paciente?->id,
            ],
        ]);
    }

    /**
     * Listar dentistas disponíveis (público)
     */
    public function dentistas(): JsonResponse
    {
        $activeProcedures = Procedure::query()
            ->where('active', true)
            ->select('id', 'name', 'description', 'category', 'value', 'time')
            ->orderBy('name')
            ->get();

        $dentistas = Employee::query()
            ->where('active', true)
            ->where(function (Builder $query) {
                $query->where('role', 'dentist')
                    ->orWhereRaw('LOWER(role) LIKE ?', ['%dent%'])
                    ->orWhereRaw('LOWER(role) LIKE ?', ['%odont%'])
                    ->orWhereRaw('LOWER(role) LIKE ?', ['%ortodont%'])
                    ->orWhereRaw('LOWER(role) LIKE ?', ['%endodont%'])
                    ->orWhereRaw('LOWER(role) LIKE ?', ['%periodont%'])
                    ->orWhereRaw('LOWER(role) LIKE ?', ['%implantodont%'])
                    ->orWhereRaw('LOWER(role) LIKE ?', ['%bucomaxilo%']);
            })
            ->select('id', 'name', 'specialty', 'photo', 'cro')
            ->orderBy('name')
            ->get()
            ->map(function (Employee $employee) use ($activeProcedures) {
                $dentistaCadastro = $this->resolveDentistaFromEmployee($employee);

                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'specialty' => $employee->specialty,
                    'photo' => $employee->photo,
                    'cro' => $employee->cro,
                    'horarios_atendimento' => $this->resolveDentistSchedule($dentistaCadastro),
                    'intervalo_consulta' => (int) ($dentistaCadastro?->intervalo_consulta ?? 30),
                    'servicos' => $this->resolveDentistServices($dentistaCadastro, $activeProcedures),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data'    => $dentistas,
        ]);
    }

    /**
     * Listar procedimentos disponíveis (público)
     */
    public function procedimentos(): JsonResponse
    {
        $procedimentos = Procedure::query()
            ->where('active', true)
            ->select('id', 'name', 'description', 'value', 'time')
            ->orderBy('name')
            ->get()
            ->map(function (Procedure $procedure) {
                return [
                    'id'          => $procedure->id,
                    'name'        => $procedure->name,
                    'description' => $procedure->description,
                    'price'       => (float) ($procedure->value ?? 0),
                    'duration'    => $this->resolveProcedureDuration($procedure),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data'    => $procedimentos,
        ]);
    }

    /**
     * Horários disponíveis para um dentista em uma data (público)
     */
    public function horariosDisponiveis(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'professional_id' => 'required|exists:employees,id',
            'date'            => 'required|date|after_or_equal:today',
            'procedure_id'    => 'nullable|exists:procedures,id',
            'duration'        => 'nullable|integer|min:15|max:480',
        ]);

        $duration = (int) ($validated['duration'] ?? 0);

        if ($duration <= 0 && ! empty($validated['procedure_id'])) {
            $procedure = Procedure::find($validated['procedure_id']);
            if ($procedure) {
                $duration = $this->resolveProcedureDuration($procedure);
            }
        }

        if ($duration <= 0) {
            $duration = 60;
        }

        $employee = Employee::find($validated['professional_id']);
        $dentistaCadastro = $employee ? $this->resolveDentistaFromEmployee($employee) : null;

        $slots    = $this->getAvailableSlots($validated['professional_id'], $validated['date'], $duration, $dentistaCadastro);

        return response()->json([
            'success'         => true,
            'professional_id' => (int) $validated['professional_id'],
            'date'            => $validated['date'],
            'available_slots' => $slots,
        ]);
    }

    /**
     * Agendar consulta (autenticado — apenas paciente)
     */
    public function agendar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'professional_id' => 'required|exists:employees,id',
            'procedure_id'    => 'required|exists:procedures,id',
            'date'            => 'required|date|after_or_equal:today',
            'time'            => ['required', 'string', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'obs'             => 'nullable|string|max:500',
        ]);

        $paciente = Paciente::where('user_id', $request->user()->id)->first();

        if (! $paciente) {
            return response()->json(['message' => 'Perfil de paciente não encontrado.'], 404);
        }

        $procedure = Procedure::findOrFail($validated['procedure_id']);
        $duration  = $this->resolveProcedureDuration($procedure);

        $appointmentAt = Carbon::parse($validated['date'] . ' ' . $validated['time']);

        if ($appointmentAt->lt(now()->addMinutes(15))) {
            return response()->json([
                'success' => false,
                'message' => 'Escolha um horário com pelo menos 15 minutos de antecedência.',
            ], 422);
        }

        if ($this->checkScheduleConflict($validated['professional_id'], $validated['date'], $validated['time'], $duration)) {
            return response()->json([
                'success' => false,
                'message' => 'Este horário não está mais disponível. Escolha outro horário.',
            ], 422);
        }

        $employee = Employee::find($validated['professional_id']);
        $dentistaCadastro = $employee ? $this->resolveDentistaFromEmployee($employee) : null;
        $availableSlots = $this->getAvailableSlots(
            $validated['professional_id'],
            $validated['date'],
            $duration,
            $dentistaCadastro
        );

        if (! in_array($validated['time'], $availableSlots, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Horario fora da agenda do dentista ou indisponivel para esta duracao.',
            ], 422);
        }

        $duplicado = Scheduling::where('patient_id', $paciente->id)
            ->where('professional_id', $validated['professional_id'])
            ->whereDate('date', $validated['date'])
            ->where('time', $validated['time'])
            ->where('status', '!=', 'canceled')
            ->exists();

        if ($duplicado) {
            return response()->json([
                'success' => false,
                'message' => 'Você já tem uma consulta agendada com este dentista nesta data.',
            ], 422);
        }

        try {
            $scheduling = DB::transaction(function () use ($paciente, $validated, $duration) {
                return Scheduling::create([
                    'patient_id'      => $paciente->id,
                    'professional_id' => $validated['professional_id'],
                    'procedure_id'    => $validated['procedure_id'],
                    'date'            => $validated['date'],
                    'time'            => $validated['time'],
                    'obs'             => $validated['obs'] ?? null,
                    'status'          => 'scheduled',
                    'scheduled_at'    => now(),
                    'duration'        => $duration,
                    'return'          => false,
                ]);
            });
        } catch (QueryException $e) {
            $isDuplicate = ($e->getCode() === '23000')
                || str_contains(mb_strtolower($e->getMessage()), 'uq_schedulings_business_unique');

            if ($isDuplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este agendamento acabou de ser reservado. Escolha outro horário.',
                ], 422);
            }

            throw $e;
        }

        $scheduling->load(['profissional', 'procedimento']);

        try {
            $sent = $this->whatsappNotificationService->sendAppointmentConfirmation($scheduling->load('paciente'));
            if ($sent) {
                $scheduling->update(['whatsapp_notification_sent_at' => now()]);
            }
        } catch (\Throwable) {
            // Nao interrompe o fluxo de agendamento quando o provedor de WhatsApp falha.
        }

        return response()->json([
            'success' => true,
            'message' => 'Consulta agendada com sucesso!',
            'data'    => [
                'id'           => $scheduling->id,
                'dentista'     => $scheduling->profissional?->name,
                'procedimento' => $scheduling->procedimento?->name,
                'data'         => Carbon::parse($scheduling->date)->format('d/m/Y'),
                'hora'         => $scheduling->time,
                'duracao'      => $duration,
                'status'       => 'agendado',
            ],
        ], 201);
    }

    /**
     * Meus agendamentos (autenticado — apenas paciente)
     */
    public function meusAgendamentos(Request $request): JsonResponse
    {
        $paciente = Paciente::where('user_id', $request->user()->id)->first();

        if (! $paciente) {
            return response()->json(['success' => true, 'data' => [], 'pagination' => []]);
        }

        $query = Scheduling::with(['profissional', 'procedimento'])
            ->where('patient_id', $paciente->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $schedulings = $query->orderBy('date', 'desc')->orderBy('time', 'desc')->paginate(10);

        $data = collect($schedulings->items())->map(function (Scheduling $s) {
            $date = $s->date instanceof Carbon
                ? $s->date->format('d/m/Y')
                : Carbon::parse((string) $s->date)->format('d/m/Y');

            return [
                'id'            => $s->id,
                'dentista'      => $s->profissional?->name ?? '',
                'especialidade' => $s->profissional?->specialty ?? '',
                'procedimento'  => $s->procedimento?->name ?? '',
                'data'          => $date,
                'hora'          => $s->time,
                'status'        => $s->status,
                'obs'           => $s->obs,
            ];
        });

        return response()->json([
            'success'    => true,
            'data'       => $data,
            'pagination' => [
                'current_page' => $schedulings->currentPage(),
                'last_page'    => $schedulings->lastPage(),
                'total'        => $schedulings->total(),
            ],
        ]);
    }

    /**
     * Cancelar agendamento próprio (autenticado — apenas paciente)
     */
    public function cancelarAgendamento(Request $request, int $id): JsonResponse
    {
        $paciente = Paciente::where('user_id', $request->user()->id)->first();

        if (! $paciente) {
            return response()->json(['message' => 'Perfil não encontrado.'], 404);
        }

        $scheduling = Scheduling::where('id', $id)
            ->where('patient_id', $paciente->id)
            ->first();

        if (! $scheduling) {
            return response()->json(['message' => 'Agendamento não encontrado.'], 404);
        }

        if (in_array($scheduling->status, ['completed', 'canceled', 'in_progress'], true)) {
            return response()->json([
                'message' => 'Não é possível cancelar agendamento com status: ' . $scheduling->status . '.',
            ], 422);
        }

        $validated = $request->validate([
            'motivo' => 'nullable|string|max:500',
        ]);

        $scheduling->update([
            'status'              => 'canceled',
            'canceled_at'         => now(),
            'cancellation_reason' => $validated['motivo'] ?? 'Cancelado pelo paciente',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Agendamento cancelado com sucesso.',
        ]);
    }

    /**
     * Perfil do paciente autenticado
     */
    public function perfil(Request $request): JsonResponse
    {
        $user     = $request->user();
        $paciente = Paciente::where('user_id', $user->id)->first();

        return response()->json([
            'success' => true,
            'data'    => [
                'user_id'  => $user->id,
                'name'     => $user->name,
                'email'    => $user->email,
                'paciente' => $paciente ? [
                    'id'              => $paciente->id,
                    'telefone'        => $paciente->telefone,
                    'data_nascimento' => $paciente->data_nascimento,
                    'cpf_cnpj'        => $paciente->cpf_cnpj,
                ] : null,
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers (baseado na lógica do SchedulingController)
    // -------------------------------------------------------------------------

    private function checkScheduleConflict(int $professionalId, string $date, string $time, int $duration = 60): bool
    {
        $start = Carbon::parse($date . ' ' . $time);
        $end   = $start->copy()->addMinutes($duration);

        $existing = Scheduling::where('professional_id', $professionalId)
            ->whereDate('date', $date)
            ->where('status', '!=', 'canceled')
            ->get();

        foreach ($existing as $e) {
            $eDate  = $e->date instanceof Carbon ? $e->date->toDateString() : Carbon::parse((string) $e->date)->toDateString();
            $eStart = Carbon::parse($eDate . ' ' . $e->time);
            $eEnd   = $eStart->copy()->addMinutes((int) ($e->duration ?? 60));

            if ($start->lt($eEnd) && $end->gt($eStart)) {
                return true;
            }
        }

        return false;
    }

    private function getAvailableSlots(int $professionalId, string $date, int $duration = 60, ?Dentista $dentista = null): array
    {
        $window = $this->resolveWorkingWindow($date, $dentista);

        if ($window === null) {
            return [];
        }

        $workStart = $window['start'];
        $workEnd   = $window['end'];
        $slotStep  = $window['step'];
        $slots     = [];
        $current   = $workStart->copy();

        if (Carbon::parse($date)->isToday()) {
            $current = $this->roundUpToInterval(now()->addMinutes(30), $slotStep);
        }

        while ($current->copy()->addMinutes($duration)->lte($workEnd)) {
            if (! $this->checkScheduleConflict($professionalId, $date, $current->format('H:i'), $duration)) {
                $slots[] = $current->format('H:i');
            }
            $current->addMinutes($slotStep);
        }

        return $slots;
    }

    private function roundUpToInterval(Carbon $time, int $intervalMinutes): Carbon
    {
        $interval = max(5, $intervalMinutes);
        $rounded = $time->copy()->second(0);

        $currentMinutes = ((int) $rounded->hour * 60) + (int) $rounded->minute;
        $nextSlotMinutes = (int) (ceil($currentMinutes / $interval) * $interval);
        $nextHour = intdiv($nextSlotMinutes, 60);
        $nextMinute = $nextSlotMinutes % 60;

        return $rounded->copy()->hour($nextHour)->minute($nextMinute);
    }

    private function resolveDentistaFromEmployee(Employee $employee): ?Dentista
    {
        $cro = trim((string) $employee->cro);
        $email = trim((string) $employee->email);

        if ($cro !== '') {
            $dentista = Dentista::query()->where('cro', $cro)->first();
            if ($dentista) {
                return $dentista;
            }
        }

        if ($email !== '') {
            $dentista = Dentista::query()->where('email', $email)->first();
            if ($dentista) {
                return $dentista;
            }
        }

        return Dentista::query()->where('name', $employee->name)->first();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function resolveDentistSchedule(?Dentista $dentista): array
    {
        if ($dentista && is_array($dentista->horarios_atendimento)) {
            return array_values($dentista->horarios_atendimento);
        }

        return [
            ['dia_semana' => 'segunda', 'ativo' => true, 'hora_inicio' => '08:00', 'hora_fim' => '18:00'],
            ['dia_semana' => 'terca', 'ativo' => true, 'hora_inicio' => '08:00', 'hora_fim' => '18:00'],
            ['dia_semana' => 'quarta', 'ativo' => true, 'hora_inicio' => '08:00', 'hora_fim' => '18:00'],
            ['dia_semana' => 'quinta', 'ativo' => true, 'hora_inicio' => '08:00', 'hora_fim' => '18:00'],
            ['dia_semana' => 'sexta', 'ativo' => true, 'hora_inicio' => '08:00', 'hora_fim' => '18:00'],
            ['dia_semana' => 'sabado', 'ativo' => false, 'hora_inicio' => '08:00', 'hora_fim' => '12:00'],
            ['dia_semana' => 'domingo', 'ativo' => false, 'hora_inicio' => '08:00', 'hora_fim' => '12:00'],
        ];
    }

    /**
     * @param \Illuminate\Support\Collection<int, Procedure> $activeProcedures
     * @return array<int, array<string, mixed>>
     */
    private function resolveDentistServices(?Dentista $dentista, $activeProcedures): array
    {
        $specialty = mb_strtolower((string) ($dentista?->especialidade ?? ''));

        $keywords = match (true) {
            str_contains($specialty, 'ortodont') => ['ortodont', 'alinhador', 'aparelho'],
            str_contains($specialty, 'endodont') => ['endodont', 'canal'],
            str_contains($specialty, 'periodont') => ['periodont', 'gengiva'],
            str_contains($specialty, 'implantodont') => ['implant'],
            str_contains($specialty, 'odontopedi') => ['odontopedi', 'infantil', 'crianca'],
            str_contains($specialty, 'cirurgia') => ['cirurgia', 'extracao', 'siso'],
            default => ['consulta', 'profilaxia', 'restauracao', 'limpeza'],
        };

        $filtered = $activeProcedures->filter(function (Procedure $procedure) use ($keywords) {
            $haystack = mb_strtolower(
                trim((string) ($procedure->name . ' ' . ($procedure->description ?? '') . ' ' . ($procedure->category ?? '')))
            );

            foreach ($keywords as $keyword) {
                if (str_contains($haystack, $keyword)) {
                    return true;
                }
            }

            return false;
        });

        if ($filtered->isEmpty()) {
            $filtered = $activeProcedures;
        }

        return $filtered->map(function (Procedure $procedure) {
            return [
                'id' => $procedure->id,
                'name' => $procedure->name,
                'description' => $procedure->description,
                'price' => (float) ($procedure->value ?? 0),
                'duration' => $this->resolveProcedureDuration($procedure),
            ];
        })->values()->all();
    }

    /**
     * @return array{start:Carbon,end:Carbon,step:int}|null
     */
    private function resolveWorkingWindow(string $date, ?Dentista $dentista): ?array
    {
        $dateCarbon = Carbon::parse($date);
        $dayMap = [
            Carbon::MONDAY => 'segunda',
            Carbon::TUESDAY => 'terca',
            Carbon::WEDNESDAY => 'quarta',
            Carbon::THURSDAY => 'quinta',
            Carbon::FRIDAY => 'sexta',
            Carbon::SATURDAY => 'sabado',
            Carbon::SUNDAY => 'domingo',
        ];

        $dayKey = $dayMap[$dateCarbon->dayOfWeek] ?? null;
        if (! $dayKey) {
            return null;
        }

        $step = max(5, (int) ($dentista?->intervalo_consulta ?? 30));

        if ($dentista && is_array($dentista->horarios_atendimento)) {
            $daySchedule = collect($dentista->horarios_atendimento)
                ->first(fn ($item) => is_array($item) && (($item['dia_semana'] ?? null) === $dayKey));

            if (is_array($daySchedule)) {
                $ativo = filter_var($daySchedule['ativo'] ?? false, FILTER_VALIDATE_BOOLEAN);
                if (! $ativo) {
                    return null;
                }

                $horaInicio = (string) ($daySchedule['hora_inicio'] ?? '08:00');
                $horaFim = (string) ($daySchedule['hora_fim'] ?? '18:00');

                if ($horaFim <= $horaInicio) {
                    return null;
                }

                return [
                    'start' => Carbon::parse($date . ' ' . $horaInicio),
                    'end' => Carbon::parse($date . ' ' . $horaFim),
                    'step' => $step,
                ];
            }
        }

        if ($dateCarbon->isSunday()) {
            return null;
        }

        return [
            'start' => Carbon::parse($date . ' 08:00'),
            'end' => Carbon::parse($date . ' 18:00'),
            'step' => 30,
        ];
    }

    private function resolveProcedureDuration(Procedure $procedure): int
    {
        $duration = $procedure->getAttribute('duration');

        if (is_numeric($duration) && (int) $duration > 0) {
            return max(15, (int) $duration);
        }

        $time = $procedure->time;

        if (is_numeric($time) && (int) $time > 0) {
            return max(15, (int) $time);
        }

        if (is_string($time) && preg_match('/(\d+)/', $time, $matches) === 1) {
            return max(15, (int) $matches[1]);
        }

        return 60;
    }
}
