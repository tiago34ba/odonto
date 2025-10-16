<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Paciente;
use App\Models\Scheduling;
use App\Models\Procedure;
use App\Models\Anamnese;
use Carbon\Carbon;

class DashboardController extends Controller
{
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
        ];

        return response()->json([
            'overview' => $stats,
            'generated_at' => now()->toISOString()
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
}
