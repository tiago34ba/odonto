<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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

        return response()->json([
            'overview' => $stats,
            'generated_at' => now()->toISOString()
        ]);
    }

    /**
     * Get real-time counters for all modules
     */
    public function moduleCounters(): JsonResponse
    {
        $counters = [];

        // Helper function to safely count records
        $safeCount = function($modelClass) {
            try {
                return $modelClass::count();
            } catch (\Exception $e) {
                return 0;
            }
        };

        // Pacientes
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

        return response()->json([
            'module_counters' => $counters,
            'last_updated' => now()->toISOString(),
            'total_modules' => count($counters)
        ]);
    }

    /**
     * Get patients statistics by various criteria
     */
    public function patientsStats(): JsonResponse
    {
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

        return response()->json([
            'patients_statistics' => $stats,
            'generated_at' => now()->toISOString()
        ]);
    }

    /**
     * Get appointments statistics
     */
    public function appointmentsStats(): JsonResponse
    {
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

        return response()->json([
            'appointments_statistics' => $stats,
            'generated_at' => now()->toISOString()
        ]);
    }

    /**
     * Get procedures statistics
     */
    public function proceduresStats(): JsonResponse
    {
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

        return response()->json([
            'procedures_statistics' => $stats,
            'generated_at' => now()->toISOString()
        ]);
    }

    /**
     * Get recent activities
     */
    public function recentActivities(): JsonResponse
    {
        $activities = collect();

        // Recent patients
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

        // Recent appointments
        $recentAppointments = Scheduling::with('paciente')
            ->latest()
            ->limit(5)
            ->get(['id', 'paciente_id', 'scheduled_at', 'status'])
            ->map(function ($appointment) {
                return [
                    'type' => 'appointment_scheduled',
                    'description' => "Consulta agendada para {$appointment->paciente->name}",
                    'date' => $appointment->scheduled_at,
                    'data' => [
                        'appointment_id' => $appointment->id,
                        'patient_id' => $appointment->paciente_id,
                        'status' => $appointment->status
                    ]
                ];
            });

        // Recent procedures
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

        return response()->json([
            'recent_activities' => $activities,
            'generated_at' => now()->toISOString()
        ]);
    }

    /**
     * Get system health status
     */
    public function systemHealth(): JsonResponse
    {
        $health = [
            'database' => $this->checkDatabaseHealth(),
            'storage' => $this->checkStorageHealth(),
            'logs' => $this->checkLogsHealth(),
            'encryption' => $this->checkEncryptionHealth(),
        ];

        $overallStatus = collect($health)->every(fn($status) => $status['status'] === 'healthy')
            ? 'healthy'
            : 'warning';

        return response()->json([
            'system_health' => $health,
            'overall_status' => $overallStatus,
            'checked_at' => now()->toISOString()
        ]);
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
            // Importar modelos necessários
            $contasPagar = \App\Models\ContasPagar::class;
            $contasReceber = \App\Models\ContasReceber::class;
            $fluxoCaixa = \App\Models\FluxoCaixa::class;
            
            // Totalizadores das contas a pagar
            $totalPagar = $contasPagar::sum('valor_original');
            $totalPago = $contasPagar::where('status', 'Pago')->sum('valor_pago');
            $totalPendentePagar = $contasPagar::whereIn('status', ['Pendente', 'Vencido'])->sum('valor_pendente');
            
            // Totalizadores das contas a receber
            $totalReceber = $contasReceber::sum('valor_original');
            $totalRecebido = $contasReceber::where('status', 'Recebido')->sum('valor_recebido');
            $totalPendenteReceber = $contasReceber::whereIn('status', ['Pendente', 'Vencido'])->sum('valor_pendente');
            
            // Saldo do fluxo de caixa
            $totalEntradas = $fluxoCaixa::where('tipo', 'Entrada')->sum('valor');
            $totalSaidas = $fluxoCaixa::where('tipo', 'Saída')->sum('valor');
            $saldoAtual = $totalEntradas - $totalSaidas;
            
            return response()->json([
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
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dashboard financeiro: ' . $e->getMessage()
            ], 500);
        }
    }
}
