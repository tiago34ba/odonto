<?php

namespace App\Http\Controllers;

use App\Models\SaasMensalidade;
use App\Models\User;
use App\Services\SaasMensalidadeSyncService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SaasMensalidadeController extends Controller
{
    public function __construct(private readonly SaasMensalidadeSyncService $syncService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        if ($request->boolean('sync', true)) {
            $this->syncService->sync();
        }

        $query = SaasMensalidade::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where('clinica', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->query('status'));
        }

        if ($request->filled('plano_id')) {
            $query->where('plano_id', (string) $request->query('plano_id'));
        }

        $perPage = min((int) $request->query('per_page', 10), 100);
        $mensalidades = $query->orderBy('clinica')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Mensalidades SaaS listadas com sucesso',
            'data' => $mensalidades->items(),
            'pagination' => [
                'current_page' => $mensalidades->currentPage(),
                'last_page' => $mensalidades->lastPage(),
                'per_page' => $mensalidades->perPage(),
                'total' => $mensalidades->total(),
            ],
        ]);
    }

    public function sync(): JsonResponse
    {
        $result = $this->syncService->sync();

        return response()->json([
            'success' => true,
            'message' => 'Mensalidades sincronizadas com sucesso',
            'data' => [
                'upserted' => $result['upserted'],
            ],
        ]);
    }

    public function inadimplencia(Request $request): JsonResponse
    {
        if ($request->boolean('sync', true)) {
            $this->syncService->sync();
        }

        $query = SaasMensalidade::query()
            ->whereIn('status', ['atrasada', 'inadimplente']);

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where('clinica', 'like', "%{$search}%");
        }

        if ($request->filled('plano_id')) {
            $query->where('plano_id', (string) $request->query('plano_id'));
        }

        if ($request->filled('status')) {
            $status = (string) $request->query('status');
            if (in_array($status, ['atrasada', 'inadimplente'], true)) {
                $query->where('status', $status);
            }
        }

        $perPage = min((int) $request->query('per_page', 10), 100);
        $mensalidades = $query->orderByDesc('proximo_vencimento')->paginate($perPage);

        $today = CarbonImmutable::today();
        $rows = collect($mensalidades->items())->map(function (SaasMensalidade $item) use ($today) {
            $daysLate = $this->resolveDaysLate($item->proximo_vencimento?->toDateString(), $today);
            $risk = $this->resolveRisk($daysLate, (string) $item->status);
            $openAmount = $this->resolveOpenAmount($item->valor_mensal, $daysLate);

            return [
                'clinica' => $item->clinica,
                'plano_id' => $item->plano_id,
                'plano_nome' => $item->plano_nome,
                'valor_mensal' => (float) $item->valor_mensal,
                'proximo_vencimento' => $item->proximo_vencimento?->toDateString(),
                'status' => $item->status,
                'dias_em_atraso' => $daysLate,
                'nivel_risco' => $risk,
                'valor_em_aberto' => $openAmount,
                'total_usuarios' => (int) $item->total_usuarios,
                'usuarios_ativos' => (int) $item->usuarios_ativos,
            ];
        })->values();

        $summaryRows = SaasMensalidade::query()
            ->whereIn('status', ['atrasada', 'inadimplente'])
            ->get(['status', 'valor_mensal', 'proximo_vencimento']);

        $totalOpenAmount = $summaryRows->sum(function (SaasMensalidade $item) use ($today) {
            $daysLate = $this->resolveDaysLate($item->proximo_vencimento?->toDateString(), $today);
            return $this->resolveOpenAmount($item->valor_mensal, $daysLate);
        });

        return response()->json([
            'success' => true,
            'message' => 'Lista de inadimplencia SaaS carregada com sucesso',
            'data' => $rows,
            'summary' => [
                'total_clinicas' => $summaryRows->count(),
                'total_atrasadas' => $summaryRows->where('status', 'atrasada')->count(),
                'total_inadimplentes' => $summaryRows->where('status', 'inadimplente')->count(),
                'valor_total_em_aberto' => (float) $totalOpenAmount,
            ],
            'pagination' => [
                'current_page' => $mensalidades->currentPage(),
                'last_page' => $mensalidades->lastPage(),
                'per_page' => $mensalidades->perPage(),
                'total' => $mensalidades->total(),
            ],
        ]);
    }

    public function clinicas(Request $request): JsonResponse
    {
        if ($request->boolean('sync', true)) {
            $this->syncService->sync();
        }

        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));
        $planoId = trim((string) $request->query('plano_id', ''));
        $perPage = min((int) $request->query('per_page', 10), 100);
        $page = max((int) $request->query('page', 1), 1);

        $mensalidades = SaasMensalidade::query()->get()->keyBy('clinica');

        $users = User::query()
            ->select([
                'id',
                'nome',
                'name',
                'email',
                'telefone',
                'clinica',
                'tipo',
                'ativo',
                'ultimo_login_em',
                'created_at',
            ])
            ->orderBy('clinica')
            ->orderBy('created_at')
            ->get();

        $rows = $users
            ->groupBy(function (User $user) {
                $clinic = trim((string) ($user->clinica ?? ''));

                return $clinic !== '' ? $clinic : 'Sem clinica';
            })
            ->map(function ($clinicUsers, $clinicName) use ($mensalidades) {
                $responsavel = $clinicUsers
                    ->first(function (User $user) {
                        return in_array((string) $user->tipo, ['admin', 'saas_admin', 'secretaria.odonto'], true);
                    })
                    ?? $clinicUsers->first();

                $email = $clinicUsers
                    ->pluck('email')
                    ->filter()
                    ->map(fn ($value) => trim((string) $value))
                    ->first() ?? '--';

                $telefone = $clinicUsers
                    ->pluck('telefone')
                    ->filter()
                    ->map(fn ($value) => trim((string) $value))
                    ->first() ?? '--';

                $totalUsuarios = $clinicUsers->count();
                $usuariosAtivos = $clinicUsers->where('ativo', true)->count();

                $lastLogin = $clinicUsers
                    ->pluck('ultimo_login_em')
                    ->filter()
                    ->sortDesc()
                    ->first();

                $firstCreatedAt = $clinicUsers
                    ->pluck('created_at')
                    ->filter()
                    ->sort()
                    ->first();

                /** @var SaasMensalidade|null $mensalidade */
                $mensalidade = $mensalidades->get((string) $clinicName);

                return [
                    'clinica' => (string) $clinicName,
                    'responsavel' => trim((string) (($responsavel?->nome ?: $responsavel?->name) ?: '--')),
                    'email' => $email,
                    'telefone' => $telefone,
                    'plano_id' => (string) ($mensalidade?->plano_id ?? 'basico'),
                    'plano_nome' => (string) ($mensalidade?->plano_nome ?? 'Basico'),
                    'status' => (string) ($mensalidade?->status ?? 'suspensa'),
                    'valor_mensal' => (float) ($mensalidade?->valor_mensal ?? 0),
                    'usuarios_ativos' => (int) $usuariosAtivos,
                    'total_usuarios' => (int) $totalUsuarios,
                    'ultimo_login_em' => $lastLogin,
                    'cadastrado_em' => $firstCreatedAt,
                ];
            })
            ->values();

        if ($search !== '') {
            $searchLower = mb_strtolower($search);
            $rows = $rows->filter(function (array $row) use ($searchLower) {
                return str_contains(mb_strtolower((string) $row['clinica']), $searchLower)
                    || str_contains(mb_strtolower((string) $row['responsavel']), $searchLower)
                    || str_contains(mb_strtolower((string) $row['email']), $searchLower);
            })->values();
        }

        if ($status !== '') {
            $rows = $rows->where('status', $status)->values();
        }

        if ($planoId !== '') {
            $rows = $rows->where('plano_id', $planoId)->values();
        }

        $total = $rows->count();
        $items = $rows->slice(($page - 1) * $perPage, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page
        );

        return response()->json([
            'success' => true,
            'message' => 'Lista de clinicas SaaS carregada com sucesso',
            'data' => $paginator->items(),
            'summary' => [
                'total_clinicas' => $total,
                'total_ativas' => (int) $rows->where('status', 'em_dia')->count(),
                'total_com_risco' => (int) $rows->whereIn('status', ['atrasada', 'inadimplente'])->count(),
                'total_suspensas' => (int) $rows->where('status', 'suspensa')->count(),
            ],
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    private function resolveDaysLate(?string $dueDate, CarbonImmutable $today): int
    {
        if (empty($dueDate)) {
            return 0;
        }

        $due = CarbonImmutable::parse($dueDate);
        if ($today->lessThanOrEqualTo($due)) {
            return 0;
        }

        return $due->diffInDays($today);
    }

    private function resolveRisk(int $daysLate, string $status): string
    {
        if ($status === 'inadimplente' || $daysLate > 15) {
            return 'alto';
        }

        if ($daysLate > 5) {
            return 'medio';
        }

        return 'baixo';
    }

    private function resolveOpenAmount(float $monthlyAmount, int $daysLate): float
    {
        if ($monthlyAmount <= 0) {
            return 0;
        }

        // Regra de negocio: considerar ao menos 1 mensalidade em aberto quando ha atraso.
        $monthsOverdue = max(1, (int) ceil(max($daysLate, 1) / 30));

        return round($monthlyAmount * $monthsOverdue, 2);
    }
}
