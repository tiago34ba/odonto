<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Paciente;
use App\Models\Procedure;
use App\Models\Scheduling;
use App\Models\User;
use Carbon\Carbon;
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
        $dentistas = Employee::where('active', true)
            ->select('id', 'name', 'specialty', 'photo', 'cro')
            ->orderBy('name')
            ->get();

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
        $procedimentos = Procedure::select('id', 'name', 'description', 'value', 'time')
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

        $slots    = $this->getAvailableSlots($validated['professional_id'], $validated['date'], $duration);

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

        $scheduling = Scheduling::create([
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

        $scheduling->load(['profissional', 'procedimento']);

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

    private function getAvailableSlots(int $professionalId, string $date, int $duration = 60): array
    {
        if (Carbon::parse($date)->isSunday()) {
            return [];
        }

        $workStart = Carbon::parse($date . ' 08:00');
        $workEnd   = Carbon::parse($date . ' 18:00');
        $slots     = [];
        $current   = $workStart->copy();

        if (Carbon::parse($date)->isToday()) {
            $current = $this->roundUpToNextHalfHour(now()->addMinutes(30));
        }

        while ($current->copy()->addMinutes($duration)->lte($workEnd)) {
            if (! $this->checkScheduleConflict($professionalId, $date, $current->format('H:i'), $duration)) {
                $slots[] = $current->format('H:i');
            }
            $current->addMinutes(30);
        }

        return $slots;
    }

    private function roundUpToNextHalfHour(Carbon $time): Carbon
    {
        $rounded = $time->copy()->second(0);
        $minute  = $rounded->minute;

        if ($minute === 0 || $minute === 30) {
            return $rounded;
        }

        if ($minute < 30) {
            return $rounded->minute(30);
        }

        return $rounded->addHour()->minute(0);
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
