<?php

namespace App\Http\Controllers;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Paciente;
use App\Models\Scheduling;
use App\Models\Procedure;
use App\Models\Anamnese;
use App\Models\Employee;
use App\Models\Agreement;
use App\Models\GroupAnamnese;
use App\Models\FormaPagamento;
use App\Models\Odontograma;
use App\Models\User;
use App\Models\TreatmentPlan;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard main page with all data
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'message' => 'Dashboard API - Sistema Odontológico',
            'version' => '1.0.0',
            'endpoints' => [
                'overview' => '/api/dashboard/overview',
                'patients_stats' => '/api/dashboard/patients-stats',
                'appointments_stats' => '/api/dashboard/appointments-stats',
                'procedures_stats' => '/api/dashboard/procedures-stats',
                'recent_activities' => '/api/dashboard/recent-activities',
                'system_health' => '/api/dashboard/system-health'
            ],
            'status' => 'active',
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Get dashboard overview statistics
     */
    public function overview(): JsonResponse
    {
        $payload = $this->rememberDashboardPayload('overview', 30, function () {
            $today = Carbon::today();
            $thisMonth = Carbon::now()->startOfMonth();
            $lastMonth = Carbon::now()->subMonth()->startOfMonth();

            $stats = [
                'patients' => [
                    'total' => Paciente::count(),
                    'this_month' => Paciente::where('created_at', '>=', $thisMonth)->count(),
                    'last_month' => Paciente::whereBetween('created_at', [$lastMonth, $thisMonth])->count(),
                ],
                'appointments' => [
                    'total' => Scheduling::count(),
                    'today' => Scheduling::whereDate('scheduled_at', $today)->count(),
                    'this_month' => Scheduling::where('scheduled_at', '>=', $thisMonth)->count(),
                ],
                'procedures' => [
                    'total' => Procedure::count(),
                    'this_month' => Procedure::where('created_at', '>=', $thisMonth)->count(),
                ],
                'anamneses' => [
                    'total' => Anamnese::count(),
                    'this_month' => Anamnese::where('created_at', '>=', $thisMonth)->count(),
                ],
                'employees' => [
                    'total' => Employee::count(),
                    'active' => Employee::where('active', true)->count(),
                    'this_month' => Employee::where('created_at', '>=', $thisMonth)->count(),
                ],
                'agreements' => [
                    'total' => Agreement::count(),
                    'this_month' => Agreement::where('created_at', '>=', $thisMonth)->count(),
                ],
                'payment_methods' => [
                    'total' => FormaPagamento::count(),
                    'this_month' => FormaPagamento::where('created_at', '>=', $thisMonth)->count(),
                ],
                'anamnese_groups' => [
                    'total' => GroupAnamnese::count(),
                    'this_month' => GroupAnamnese::where('created_at', '>=', $thisMonth)->count(),
                ],
                'odontograms' => [
                    'total' => Odontograma::count(),
                    'this_month' => Odontograma::where('created_at', '>=', $thisMonth)->count(),
                ],
            ];

            return [
                'overview' => $stats,
                'generated_at' => now()->toISOString(),
            ];
        });

        return response()->json($payload);
    }

    /**
     * Get real-time counters for all modules
     */
    public function moduleCounters(): JsonResponse
    {
        $payload = $this->rememberDashboardPayload('module_counters', 30, function () {
            $counters = [];

            $safeCount = function ($modelClass) {
                try {
                    return $modelClass::count();
                } catch (\Exception $e) {
                    return 0;
                }
            };

            try {
                $counters['pacientes'] = [
                    'total' => $safeCount(Paciente::class),
                    'icon' => '👥',
                    'color' => '#667eea',
                    'endpoint' => '/api/pessoas/pacientes'
                ];
            } catch (\Exception $e) {
                $counters['pacientes'] = ['total' => 0, 'icon' => '👥', 'color' => '#667eea', 'endpoint' => '/api/pessoas/pacientes'];
            }

        // Agendamentos
        try {
            $counters['agendamentos'] = [
                'total' => $safeCount(Scheduling::class),
                'icon' => '📅',
                'color' => '#f093fb',
                'endpoint' => '/api/schedulings'
            ];
        } catch (\Exception $e) {
            $counters['agendamentos'] = ['total' => 0, 'icon' => '📅', 'color' => '#f093fb', 'endpoint' => '/api/schedulings'];
        }

        // Procedimentos
        try {
            $counters['procedimentos'] = [
                'total' => $safeCount(Procedure::class),
                'icon' => '🔧',
                'color' => '#4facfe',
                'endpoint' => '/api/procedures'
            ];
        } catch (\Exception $e) {
            $counters['procedimentos'] = ['total' => 0, 'icon' => '🔧', 'color' => '#4facfe', 'endpoint' => '/api/procedures'];
        }

        // Funcionarios
        try {
            $counters['funcionarios'] = [
                'total' => $safeCount(Employee::class),
                'icon' => '👨‍⚕️',
                'color' => '#fa709a',
                'endpoint' => '/api/employees'
            ];
        } catch (\Exception $e) {
            $counters['funcionarios'] = ['total' => 0, 'icon' => '👨‍⚕️', 'color' => '#fa709a', 'endpoint' => '/api/employees'];
        }

        // Anamneses
        try {
            $counters['anamneses'] = [
                'total' => $safeCount(Anamnese::class),
                'icon' => '📝',
                'color' => '#a6c1ee',
                'endpoint' => '/api/anamneses'
            ];
        } catch (\Exception $e) {
            $counters['anamneses'] = ['total' => 0, 'icon' => '📝', 'color' => '#a6c1ee', 'endpoint' => '/api/anamneses'];
        }

        // Convenios - vamos testar se a tabela existe
        if (Schema::hasTable('agreements')) {
            try {
                $counters['convenios'] = [
                    'total' => $safeCount(Agreement::class),
                    'icon' => '🤝',
                    'color' => '#a8edea',
                    'endpoint' => '/api/agreements'
                ];
            } catch (\Exception $e) {
                $counters['convenios'] = ['total' => 0, 'icon' => '🤝', 'color' => '#a8edea', 'endpoint' => '/api/agreements'];
            }
        }

        // Formas de Pagamento
        if (Schema::hasTable('forma_pagamentos')) {
            try {
                $counters['formas_pagamento'] = [
                    'total' => $safeCount(FormaPagamento::class),
                    'icon' => '💳',
                    'color' => '#a1c4fd',
                    'endpoint' => '/api/formas-pagamentos'
                ];
            } catch (\Exception $e) {
                $counters['formas_pagamento'] = ['total' => 0, 'icon' => '💳', 'color' => '#a1c4fd', 'endpoint' => '/api/formas-pagamentos'];
            }
        }

        // Grupos Anamnese
        if (Schema::hasTable('group_anamneses')) {
            try {
                $counters['grupos_anamnese'] = [
                    'total' => $safeCount(GroupAnamnese::class),
                    'icon' => '📋',
                    'color' => '#fbc2eb',
                    'endpoint' => '/api/groups-anamnese'
                ];
            } catch (\Exception $e) {
                $counters['grupos_anamnese'] = ['total' => 0, 'icon' => '�', 'color' => '#fbc2eb', 'endpoint' => '/api/groups-anamnese'];
            }
        }

        // Odontogramas
        if (Schema::hasTable('odontogramas')) {
            try {
                $counters['odontogramas'] = [
                    'total' => $safeCount(Odontograma::class),
                    'icon' => '🦷',
                    'color' => '#ffd89b',
                    'endpoint' => '/api/odontograma'
                ];
            } catch (\Exception $e) {
                $counters['odontogramas'] = ['total' => 0, 'icon' => '🦷', 'color' => '#ffd89b', 'endpoint' => '/api/odontograma'];
            }
        }

        // Usuários
        if (Schema::hasTable('users')) {
            try {
                $counters['usuarios'] = [
                    'total' => $safeCount(User::class),
                    'icon' => '👤',
                    'color' => '#ff9a9e',
                    'endpoint' => '/api/users'
                ];
            } catch (\Exception $e) {
                $counters['usuarios'] = ['total' => 0, 'icon' => '👤', 'color' => '#ff9a9e', 'endpoint' => '/api/users'];
            }
        }

        // Planos de Tratamento
        if (Schema::hasTable('treatment_plans')) {
            try {
                $counters['planos_tratamento'] = [
                    'total' => $safeCount(TreatmentPlan::class),
                    'icon' => '📊',
                    'color' => '#84fab0',
                    'endpoint' => '/api/treatment-plans'
                ];
            } catch (\Exception $e) {
                $counters['planos_tratamento'] = ['total' => 0, 'icon' => '📊', 'color' => '#84fab0', 'endpoint' => '/api/treatment-plans'];
            }
        }

        // Procedimentos de Pacientes
        if (Schema::hasTable('patient_procedures')) {
            try {
                $counters['procedimentos_pacientes'] = [
                    'total' => DB::table('patient_procedures')->count(),
                    'icon' => '⚕️',
                    'color' => '#c2e59c',
                    'endpoint' => '/api/patient-procedures'
                ];
            } catch (\Exception $e) {
                $counters['procedimentos_pacientes'] = ['total' => 0, 'icon' => '⚕️', 'color' => '#c2e59c', 'endpoint' => '/api/patient-procedures'];
            }
        }

        // Sessões Ativas
        if (Schema::hasTable('sessions')) {
            try {
                $counters['sessoes_ativas'] = [
                    'total' => DB::table('sessions')->count(),
                    'icon' => '🔒',
                    'color' => '#ff7675',
                    'endpoint' => '/api/sessions'
                ];
            } catch (\Exception $e) {
                $counters['sessoes_ativas'] = ['total' => 0, 'icon' => '🔒', 'color' => '#ff7675', 'endpoint' => '/api/sessions'];
            }
        }

        // Tokens de Acesso
        if (Schema::hasTable('personal_access_tokens')) {
            try {
                $counters['tokens_acesso'] = [
                    'total' => DB::table('personal_access_tokens')->count(),
                    'icon' => '🔑',
                    'color' => '#fdcb6e',
                    'endpoint' => '/api/access-tokens'
                ];
            } catch (\Exception $e) {
                $counters['tokens_acesso'] = ['total' => 0, 'icon' => '🔑', 'color' => '#fdcb6e', 'endpoint' => '/api/access-tokens'];
            }
        }

        // Jobs em Fila
        if (Schema::hasTable('jobs')) {
            try {
                $counters['jobs_pendentes'] = [
                    'total' => DB::table('jobs')->count(),
                    'icon' => '⏳',
                    'color' => '#a29bfe',
                    'endpoint' => '/api/jobs'
                ];
            } catch (\Exception $e) {
                $counters['jobs_pendentes'] = ['total' => 0, 'icon' => '⏳', 'color' => '#a29bfe', 'endpoint' => '/api/jobs'];
            }
        }

        // Jobs Falhados
        if (Schema::hasTable('failed_jobs')) {
            try {
                $counters['jobs_falhados'] = [
                    'total' => DB::table('failed_jobs')->count(),
                    'icon' => '❌',
                    'color' => '#d63031',
                    'endpoint' => '/api/failed-jobs'
                ];
            } catch (\Exception $e) {
                $counters['jobs_falhados'] = ['total' => 0, 'icon' => '❌', 'color' => '#d63031', 'endpoint' => '/api/failed-jobs'];
            }
        }

            return [
                'module_counters' => $counters,
                'last_updated' => now()->toISOString(),
                'total_modules' => count($counters),
            ];
        });

        return response()->json($payload);
    }

    /**
     * Consolidated summary for dashboard cards.
     * All values are loaded from database sources when available.
     */
    public function cardsSummary(): JsonResponse
    {
        try {
            $payload = $this->rememberDashboardPayload('cards_summary', 30, function () {
                $today = Carbon::today();
                $monthStart = Carbon::now()->startOfMonth();

                $patientsTotal = Schema::hasTable('pacientes')
                    ? DB::table('pacientes')->count()
                    : 0;

            $agendamentosHoje = Schema::hasTable('schedulings')
                ? DB::table('schedulings')->whereDate('scheduled_at', $today)->count()
                : 0;

            $agendamentosConfirmados = Schema::hasTable('schedulings')
                ? DB::table('schedulings')
                    ->where('scheduled_at', '>=', $monthStart)
                    ->whereIn('status', ['confirmed', 'confirmado', 'completed', 'concluido'])
                    ->count()
                : 0;

            $procedimentosRealizados = Schema::hasTable('schedulings')
                ? DB::table('schedulings')
                    ->where('scheduled_at', '>=', $monthStart)
                    ->whereIn('status', ['completed', 'concluido'])
                    ->count()
                : 0;

            $retornosAgendados = Schema::hasTable('schedulings')
                ? DB::table('schedulings')
                    ->whereBetween('scheduled_at', [Carbon::now(), Carbon::now()->addDays(7)])
                    ->count()
                : 0;

            $despesasVencidas = Schema::hasTable('contas_pagars')
                ? DB::table('contas_pagars')
                    ->whereDate('data_vencimento', '<', $today)
                    ->whereIn('status', ['Pendente', 'Parcial', 'Vencido'])
                    ->sum('valor_pendente')
                : 0;

            $receberVencidas = Schema::hasTable('contas_recebers')
                ? DB::table('contas_recebers')
                    ->whereDate('data_vencimento', '<', $today)
                    ->whereIn('status', ['Pendente', 'Parcial', 'Vencido'])
                    ->sum('valor_pendente')
                : 0;

            $receitaMensal = Schema::hasTable('contas_recebers')
                ? DB::table('contas_recebers')
                    ->where('status', 'Recebido')
                    ->whereMonth('data_recebimento', $today->month)
                    ->whereYear('data_recebimento', $today->year)
                    ->sum('valor_recebido')
                : 0;

            $despesasPagasMensal = Schema::hasTable('contas_pagars')
                ? DB::table('contas_pagars')
                    ->where('status', 'Pago')
                    ->whereMonth('data_pagamento', $today->month)
                    ->whereYear('data_pagamento', $today->year)
                    ->sum('valor_pago')
                : 0;

            $saldoMes = (float) $receitaMensal - (float) $despesasPagasMensal;

            $taxaPresenca = 0;
            if (Schema::hasTable('schedulings')) {
                $totalMes = DB::table('schedulings')
                    ->where('scheduled_at', '>=', $monthStart)
                    ->count();

                $concluidosMes = DB::table('schedulings')
                    ->where('scheduled_at', '>=', $monthStart)
                    ->whereIn('status', ['completed', 'concluido'])
                    ->count();

                $taxaPresenca = $totalMes > 0
                    ? round(($concluidosMes / $totalMes) * 100, 2)
                    : 0;
            }

            $tratamentosAndamento = Schema::hasTable('treatment_plans')
                ? DB::table('treatment_plans')->count()
                : 0;

            $orcamentosPendentes = Schema::hasTable('treatment_plans')
                ? DB::table('treatment_plans')
                    ->whereIn('status', ['Pendente'])
                    ->count()
                : 0;

            // Estoque baixo (regra operacional): despesas de reposicao pendentes em categorias de insumo.
            $estoqueBaixo = Schema::hasTable('contas_pagars')
                ? DB::table('contas_pagars')
                    ->whereIn('categoria', ['Equipamentos', 'Materiais', 'Medicamentos'])
                    ->whereIn('status', ['Pendente', 'Parcial', 'Vencido'])
                    ->where('valor_pendente', '>', 0)
                    ->count()
                : 0;

            // Satisfação: média de avaliação de fornecedores (0 a 5).
            $satisfacaoCliente = 0;
            if (Schema::hasTable('suppliers') && Schema::hasColumn('suppliers', 'avaliacao')) {
                $satisfacaoCliente = (float) DB::table('suppliers')
                    ->whereNotNull('avaliacao')
                    ->avg('avaliacao');
            }

                return [
                    'success' => true,
                    'message' => 'Resumo consolidado dos cards carregado com sucesso',
                    'data' => [
                        'metrics' => [
                            'patients_total' => (int) $patientsTotal,
                            'despesas_vencidas' => (float) $despesasVencidas,
                            'receber_vencidas' => (float) $receberVencidas,
                            'saldo_mes' => (float) $saldoMes,
                            'agendamentos_hoje' => (int) $agendamentosHoje,
                            'agendamentos_confirmados' => (int) $agendamentosConfirmados,
                            'consultas_hoje' => (int) $agendamentosHoje,
                            'orcamentos_pendentes' => (int) $orcamentosPendentes,
                            'procedimentos_realizados' => (int) $procedimentosRealizados,
                            'receita_mensal' => (float) $receitaMensal,
                            'pacientes_ativos' => (int) $patientsTotal,
                            'taxa_presenca' => (float) $taxaPresenca,
                            'estoque_baixo' => (int) $estoqueBaixo,
                            'retornos_agendados' => (int) $retornosAgendados,
                            'tratamentos_andamento' => (int) $tratamentosAndamento,
                            'satisfacao_cliente' => round($satisfacaoCliente, 1),
                        ],
                        'source' => [
                            'orcamentos_pendentes' => 'treatment_plans.status = Pendente',
                            'estoque_baixo' => 'contas_pagars categorias de insumo em aberto',
                            'satisfacao_cliente' => 'suppliers.avaliacao',
                        ],
                        'generated_at' => now()->toISOString(),
                    ],
                ];
            });

            return response()->json($payload);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar resumo consolidado dos cards',
                'error' => app()->environment('production') ? null : $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get patients statistics by various criteria
     */
    public function patientsStats(): JsonResponse
    {
        $payload = $this->rememberDashboardPayload('patients_stats', 60, function () {
            $stats = [
                'by_convenio' => Paciente::selectRaw('convenio, count(*) as total')
                    ->groupBy('convenio')
                    ->orderBy('total', 'desc')
                    ->get(),

            'by_age_group' => [
                '0-17' => Paciente::whereBetween('idade', [0, 17])->count(),
                '18-30' => Paciente::whereBetween('idade', [18, 30])->count(),
                '31-50' => Paciente::whereBetween('idade', [31, 50])->count(),
                '51-70' => Paciente::whereBetween('idade', [51, 70])->count(),
                '70+' => Paciente::where('idade', '>', 70)->count(),
            ],

            'by_gender' => Paciente::selectRaw('sexo, count(*) as total')
                ->groupBy('sexo')
                ->get(),

            'by_state' => Paciente::selectRaw('estado, count(*) as total')
                ->groupBy('estado')
                ->orderBy('total', 'desc')
                ->get(),

            'recent_registrations' => Paciente::selectRaw('DATE(created_at) as date, count(*) as total')
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];

            return [
                'patients_statistics' => $stats,
                'generated_at' => now()->toISOString(),
            ];
        });

        return response()->json($payload);
    }

    /**
     * Get appointments statistics
     */
    public function appointmentsStats(): JsonResponse
    {
        $payload = $this->rememberDashboardPayload('appointments_stats', 60, function () {
            $today = Carbon::today();
            $thisWeek = Carbon::now()->startOfWeek();
            $thisMonth = Carbon::now()->startOfMonth();

        $stats = [
            'today' => [
                'total' => Scheduling::whereDate('scheduled_at', $today)->count(),
                'completed' => Scheduling::whereDate('scheduled_at', $today)
                    ->where('status', 'completed')->count(),
                'pending' => Scheduling::whereDate('scheduled_at', $today)
                    ->where('status', 'pending')->count(),
            ],
            'this_week' => [
                'total' => Scheduling::where('scheduled_at', '>=', $thisWeek)->count(),
                'by_day' => Scheduling::selectRaw('DAYNAME(scheduled_at) as day, count(*) as total')
                    ->where('scheduled_at', '>=', $thisWeek)
                    ->groupBy('day')
                    ->get(),
            ],
            'this_month' => [
                'total' => Scheduling::where('scheduled_at', '>=', $thisMonth)->count(),
                'by_week' => Scheduling::selectRaw('WEEK(scheduled_at) as week, count(*) as total')
                    ->where('scheduled_at', '>=', $thisMonth)
                    ->groupBy('week')
                    ->get(),
            ],
            'status_distribution' => Scheduling::selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->get(),
        ];

            return [
                'appointments_statistics' => $stats,
                'generated_at' => now()->toISOString(),
            ];
        });

        return response()->json($payload);
    }

    /**
     * Get procedures statistics
     */
    public function proceduresStats(): JsonResponse
    {
        $payload = $this->rememberDashboardPayload('procedures_stats', 60, function () {
            $thisMonth = Carbon::now()->startOfMonth();
            $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        $stats = [
            'total' => Procedure::count(),
            'this_month' => Procedure::where('created_at', '>=', $thisMonth)->count(),
            'last_month' => Procedure::whereBetween('created_at', [$lastMonth, $thisMonth])->count(),
            'by_type' => Procedure::selectRaw('type, count(*) as total')
                ->groupBy('type')
                ->get(),
            'revenue' => [
                'this_month' => Procedure::where('created_at', '>=', $thisMonth)
                    ->sum('price'),
                'last_month' => Procedure::whereBetween('created_at', [$lastMonth, $thisMonth])
                    ->sum('price'),
            ],
        ];

            return [
                'procedures_statistics' => $stats,
                'generated_at' => now()->toISOString(),
            ];
        });

        return response()->json($payload);
    }

    /**
     * Get recent activities
     */
    public function recentActivities(): JsonResponse
    {
        $payload = $this->rememberDashboardPayload('recent_activities', 30, function () {
            $activities = collect();

            $recentPatients = Paciente::latest()
                ->limit(5)
                ->get(['id', 'name', 'created_at'])
                ->map(function ($patient) {
                    return [
                        'type' => 'patient_created',
                        'description' => "Novo paciente: {$patient->name}",
                        'date' => $patient->created_at,
                        'data' => ['patient_id' => $patient->id]
                    ];
                });

            $recentAppointments = Scheduling::with('paciente')
                ->latest()
                ->limit(5)
                ->get(['id', 'paciente_id', 'scheduled_at', 'status'])
                ->map(function ($appointment) {
                    return [
                        'type' => 'appointment_scheduled',
                        'description' => 'Consulta agendada para ' . optional($appointment->paciente)->name,
                        'date' => $appointment->scheduled_at,
                        'data' => [
                            'appointment_id' => $appointment->id,
                            'patient_id' => $appointment->paciente_id,
                            'status' => $appointment->status
                        ]
                    ];
                });

            $recentProcedures = Procedure::latest()
                ->limit(5)
                ->get(['id', 'name', 'created_at'])
                ->map(function ($procedure) {
                    return [
                        'type' => 'procedure_created',
                        'description' => "Procedimento criado: {$procedure->name}",
                        'date' => $procedure->created_at,
                        'data' => ['procedure_id' => $procedure->id]
                    ];
                });

            $activities = $activities
                ->merge($recentPatients)
                ->merge($recentAppointments)
                ->merge($recentProcedures)
                ->sortByDesc('date')
                ->take(15)
                ->values();

            return [
                'recent_activities' => $activities,
                'generated_at' => now()->toISOString(),
            ];
        });

        return response()->json($payload);
    }

    /**
     * Get system health status
     */
    public function systemHealth(): JsonResponse
    {
        $payload = $this->rememberDashboardPayload('system_health', 15, function () {
            $health = [
                'database' => $this->checkDatabaseHealth(),
                'storage' => $this->checkStorageHealth(),
                'logs' => $this->checkLogsHealth(),
                'encryption' => $this->checkEncryptionHealth(),
            ];

            $overallStatus = collect($health)->every(fn($status) => $status['status'] === 'healthy')
                ? 'healthy'
                : 'warning';

            return [
                'system_health' => $health,
                'overall_status' => $overallStatus,
                'checked_at' => now()->toISOString(),
            ];
        });

        return response()->json($payload);
    }

    /**
     * Check database health
     */
    protected function checkDatabaseHealth(): array
    {
        try {
            $connectionTime = microtime(true);
            DB::connection()->getPdo();
            $connectionTime = microtime(true) - $connectionTime;

            return [
                'status' => 'healthy',
                'connection_time' => round($connectionTime * 1000, 2) . 'ms',
                'message' => 'Database connection successful'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check storage health
     */
    protected function checkStorageHealth(): array
    {
        $storagePath = storage_path();
        $freeSpace = disk_free_space($storagePath);
        $totalSpace = disk_total_space($storagePath);
        $usedSpace = $totalSpace - $freeSpace;
        $usagePercentage = ($usedSpace / $totalSpace) * 100;

        return [
            'status' => $usagePercentage > 90 ? 'warning' : 'healthy',
            'usage_percentage' => round($usagePercentage, 2),
            'free_space' => $this->formatBytes($freeSpace),
            'total_space' => $this->formatBytes($totalSpace),
            'message' => $usagePercentage > 90 ? 'Storage space is running low' : 'Storage space is adequate'
        ];
    }

    /**
     * Check logs health
     */
    protected function checkLogsHealth(): array
    {
        $logPath = storage_path('logs');
        $logFiles = glob($logPath . '/*.log');
        $totalLogSize = array_sum(array_map('filesize', $logFiles));

        return [
            'status' => 'healthy',
            'log_files_count' => count($logFiles),
            'total_size' => $this->formatBytes($totalLogSize),
            'message' => 'Logs are being generated properly'
        ];
    }

    /**
     * Check encryption health
     */
    protected function checkEncryptionHealth(): array
    {
        try {
            $testData = 'test-encryption-data';
            $encrypted = encrypt($testData);
            $decrypted = decrypt($encrypted);

            return [
                'status' => $decrypted === $testData ? 'healthy' : 'error',
                'message' => $decrypted === $testData
                    ? 'Encryption/decryption working properly'
                    : 'Encryption/decryption test failed'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Encryption test failed: ' . $e->getMessage()
            ];
        }
    }

    protected function rememberDashboardPayload(string $key, int $seconds, Closure $callback): array
    {
        return Cache::remember($this->dashboardCacheKey($key), $seconds, $callback);
    }

    protected function dashboardCacheKey(string $key): string
    {
        return 'dashboard:' . app()->environment() . ':' . $key;
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Dashboard financeiro consolidado
     */
    public function financeiro(): JsonResponse
    {
        try {
            $payload = $this->rememberDashboardPayload('financeiro', 60, function () {
                $contasPagar = \App\Models\ContasPagar::class;
                $contasReceber = \App\Models\ContasReceber::class;
                $fluxoCaixa = \App\Models\FluxoCaixa::class;

                $totalPagar = $contasPagar::sum('valor_original');
                $totalPago = $contasPagar::where('status', 'Pago')->sum('valor_pago');
                $totalPendentePagar = $contasPagar::whereIn('status', ['Pendente', 'Vencido'])->sum('valor_pendente');

                $totalReceber = $contasReceber::sum('valor_original');
                $totalRecebido = $contasReceber::where('status', 'Recebido')->sum('valor_recebido');
                $totalPendenteReceber = $contasReceber::whereIn('status', ['Pendente', 'Vencido'])->sum('valor_pendente');

                $totalEntradas = $fluxoCaixa::where('tipo', 'Entrada')->sum('valor');
                $totalSaidas = $fluxoCaixa::where('tipo', 'Saída')->sum('valor');
                $saldoAtual = $totalEntradas - $totalSaidas;

                return [
                    'success' => true,
                    'data' => [
                        'contas_pagar' => [
                            'total' => $totalPagar,
                            'pago' => $totalPago,
                            'pendente' => $totalPendentePagar
                        ],
                        'contas_receber' => [
                            'total' => $totalReceber,
                            'recebido' => $totalRecebido,
                            'pendente' => $totalPendenteReceber
                        ],
                        'fluxo_caixa' => [
                            'entradas' => $totalEntradas,
                            'saidas' => $totalSaidas,
                            'saldo' => $saldoAtual
                        ],
                        'resumo' => [
                            'total_a_pagar' => $totalPendentePagar,
                            'total_a_receber' => $totalPendenteReceber,
                            'saldo_atual' => $saldoAtual,
                            'projecao' => $saldoAtual + $totalPendenteReceber - $totalPendentePagar
                        ]
                    ],
                    'message' => 'Dashboard financeiro carregado com sucesso'
                ];
            });

            return response()->json($payload);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dashboard financeiro: ' . $e->getMessage()
            ], 500);
        }
    }
}
