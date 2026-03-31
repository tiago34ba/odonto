<?php

namespace App\Services;

use App\Models\SaasMensalidade;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class SaasMensalidadeSyncService
{
    private const PLANOS = [
        'basico' => [
            'id' => 'basico',
            'nome' => 'Basico',
            'valor' => 70,
            'limite_usuarios' => 1,
        ],
        'profissional' => [
            'id' => 'profissional',
            'nome' => 'Profissional',
            'valor' => 90,
            'limite_usuarios' => 8,
        ],
        'premium' => [
            'id' => 'premium',
            'nome' => 'Premium',
            'valor' => 160,
            'limite_usuarios' => null,
        ],
    ];

    public function sync(): array
    {
        $users = User::query()
            ->select(['clinica', 'ativo', 'created_at'])
            ->orderBy('created_at')
            ->get();

        $grouped = $users->groupBy(function (User $user) {
            $clinic = trim((string) ($user->clinica ?? ''));

            return $clinic !== '' ? $clinic : 'Sem clinica';
        });

        $today = CarbonImmutable::today();
        $payload = [];

        foreach ($grouped as $clinica => $clinicUsers) {
            $totalUsuarios = $clinicUsers->count();
            $usuariosAtivos = $clinicUsers->where('ativo', true)->count();
            $plano = $this->inferPlan($usuariosAtivos);

            $anchorCreatedAt = $clinicUsers
                ->pluck('created_at')
                ->filter()
                ->sort()
                ->first();

            $anchorDate = $anchorCreatedAt
                ? CarbonImmutable::parse($anchorCreatedAt)
                : $today;

            $dueDate = $this->nextDueDate($today, $anchorDate->day);
            $status = $this->computeStatus($usuariosAtivos > 0, $dueDate, $today);

            $payload[] = [
                'clinica' => $clinica,
                'plano_id' => $plano['id'],
                'plano_nome' => $plano['nome'],
                'valor_mensal' => $plano['valor'],
                'total_usuarios' => $totalUsuarios,
                'usuarios_ativos' => $usuariosAtivos,
                'proximo_vencimento' => $dueDate->toDateString(),
                'status' => $status,
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        DB::transaction(function () use ($payload) {
            $clinicasAtuais = collect($payload)->pluck('clinica')->all();
            if (!empty($clinicasAtuais)) {
                SaasMensalidade::query()->whereNotIn('clinica', $clinicasAtuais)->delete();
            }

            if (empty($payload)) {
                return;
            }

            SaasMensalidade::upsert(
                $payload,
                ['clinica'],
                [
                    'plano_id',
                    'plano_nome',
                    'valor_mensal',
                    'total_usuarios',
                    'usuarios_ativos',
                    'proximo_vencimento',
                    'status',
                    'updated_at',
                ]
            );
        });

        return [
            'upserted' => count($payload),
        ];
    }

    private function inferPlan(int $usuariosAtivos): array
    {
        if ($usuariosAtivos <= (int) self::PLANOS['basico']['limite_usuarios']) {
            return self::PLANOS['basico'];
        }

        if ($usuariosAtivos <= (int) self::PLANOS['profissional']['limite_usuarios']) {
            return self::PLANOS['profissional'];
        }

        return self::PLANOS['premium'];
    }

    private function nextDueDate(CarbonImmutable $today, int $anchorDay): CarbonImmutable
    {
        $dueDay = max(1, min($anchorDay, 28));
        $dueThisMonth = CarbonImmutable::create($today->year, $today->month, $dueDay);

        if ($today->lessThanOrEqualTo($dueThisMonth)) {
            return $dueThisMonth;
        }

        $nextMonth = $today->addMonthNoOverflow();

        return CarbonImmutable::create($nextMonth->year, $nextMonth->month, $dueDay);
    }

    private function computeStatus(bool $hasActiveUsers, CarbonImmutable $dueDate, CarbonImmutable $today): string
    {
        if (!$hasActiveUsers) {
            return 'suspensa';
        }

        $diffDays = $today->diffInDays($dueDate, false) * -1;

        if ($diffDays <= 0) {
            return 'em_dia';
        }

        if ($diffDays <= 5) {
            return 'atrasada';
        }

        return 'inadimplente';
    }
}
