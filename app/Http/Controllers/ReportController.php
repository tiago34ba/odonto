<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Paciente;
use App\Models\Scheduling;
use App\Models\Procedure;
use App\Models\Employee;
use App\Models\Agreement;
use App\Models\FormaPagamento;
use App\Models\Anamnese;
use App\Models\Report;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Listar todos os relatórios salvos
     */
    public function index(Request $request): JsonResponse
    {
        $query = Report::query();

        // Filtros
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('is_scheduled')) {
            $query->where('is_scheduled', $request->boolean('is_scheduled'));
        }

        $reports = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Relatórios listados com sucesso',
            'data' => $reports,
            'total' => $reports->count(),
            'report_types' => Report::getReportTypes()
        ]);
    }

    /**
     * Mostrar relatório específico
     */
    public function show($id): JsonResponse
    {
        $report = Report::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Relatório encontrado com sucesso',
            'data' => $report
        ]);
    }

    /**
     * Salvar novo relatório
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:system,patients,appointments,financial,staff,productivity',
            'description' => 'required|string',
            'data' => 'required|array',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'generated_by' => 'nullable|string|max:255',
            'is_scheduled' => 'boolean',
            'schedule_frequency' => 'nullable|string|in:daily,weekly,monthly,quarterly'
        ]);

        $report = Report::create([
            'name' => $request->name,
            'type' => $request->type,
            'description' => $request->description,
            'data' => $request->data,
            'period_start' => $request->period_start,
            'period_end' => $request->period_end,
            'generated_by' => $request->generated_by,
            'is_scheduled' => $request->boolean('is_scheduled'),
            'schedule_frequency' => $request->schedule_frequency,
            'last_generated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Relatório salvo com sucesso',
            'data' => $report
        ], 201);
    }

    /**
     * Deletar relatório
     */
    public function destroy($id): JsonResponse
    {
        $report = Report::findOrFail($id);
        $report->delete();

        return response()->json([
            'success' => true,
            'message' => 'Relatório deletado com sucesso'
        ]);
    }
    /**
     * Relatório Geral do Sistema
     */
    public function systemOverview(): JsonResponse
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth();

        $data = [
            'period' => [
                'start_date' => $thisMonth->toDateString(),
                'end_date' => $today->toDateString(),
                'generated_at' => now()->toISOString()
            ],
            'summary' => [
                'total_patients' => Paciente::count(),
                'total_appointments' => Scheduling::count(),
                'total_procedures' => Procedure::count(),
                'total_employees' => Employee::count(),
                'active_agreements' => Agreement::count(),
            ],
            'monthly_growth' => [
                'patients' => [
                    'this_month' => Paciente::where('created_at', '>=', $thisMonth)->count(),
                    'last_month' => Paciente::whereBetween('created_at', [$lastMonth->startOfMonth(), $thisMonth])->count(),
                ],
                'appointments' => [
                    'this_month' => Scheduling::where('scheduled_at', '>=', $thisMonth)->count(),
                    'last_month' => Scheduling::whereBetween('scheduled_at', [$lastMonth->startOfMonth(), $thisMonth])->count(),
                ],
            ],
            'daily_stats' => [
                'appointments_today' => Scheduling::whereDate('scheduled_at', $today)->count(),
                'completed_today' => Scheduling::whereDate('scheduled_at', $today)->where('status', 'realizado')->count(),
                'pending_today' => Scheduling::whereDate('scheduled_at', $today)->where('status', 'agendado')->count(),
            ]
        ];

        return response()->json([
            'success' => true,
            'message' => 'Relatório geral do sistema gerado com sucesso',
            'data' => $data
        ]);
    }

    /**
     * Relatório de Pacientes
     */
    public function patientsReport(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::today()->toDateString());

        // Estatísticas básicas
        $totalPatients = Paciente::count();
        $newPatients = Paciente::whereBetween('created_at', [$startDate, $endDate])->count();
        
        // Distribuição por gênero
        $genderDistribution = Paciente::select('sexo', DB::raw('count(*) as total'))
            ->groupBy('sexo')
            ->get();

        // Distribuição por convênio
        $agreementDistribution = Paciente::select('convenio', DB::raw('count(*) as total'))
            ->whereNotNull('convenio')
            ->groupBy('convenio')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Distribuição por cidade
        $cityDistribution = Paciente::select('cidade', DB::raw('count(*) as total'))
            ->whereNotNull('cidade')
            ->groupBy('cidade')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Idade média
        $averageAge = Paciente::whereNotNull('data_nascimento')
            ->get()
            ->avg(function($patient) {
                return Carbon::parse($patient->data_nascimento)->age;
            });

        $data = [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'summary' => [
                'total_patients' => $totalPatients,
                'new_patients_period' => $newPatients,
                'average_age' => round($averageAge, 1)
            ],
            'demographics' => [
                'gender_distribution' => $genderDistribution,
                'agreement_distribution' => $agreementDistribution,
                'city_distribution' => $cityDistribution
            ],
            'trends' => [
                'monthly_registrations' => Paciente::select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('count(*) as total')
                )
                ->whereBetween('created_at', [Carbon::now()->subMonths(6), Carbon::now()])
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get()
            ]
        ];

        return response()->json([
            'success' => true,
            'message' => 'Relatório de pacientes gerado com sucesso',
            'data' => $data
        ]);
    }

    /**
     * Relatório de Agendamentos
     */
    public function appointmentsReport(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::today()->toDateString());

        // Estatísticas básicas
        $totalAppointments = Scheduling::whereBetween('scheduled_at', [$startDate, $endDate])->count();
        $completedAppointments = Scheduling::whereBetween('scheduled_at', [$startDate, $endDate])
            ->where('status', 'realizado')->count();
        $canceledAppointments = Scheduling::whereBetween('scheduled_at', [$startDate, $endDate])
            ->where('status', 'cancelado')->count();

        // Distribuição por status
        $statusDistribution = Scheduling::select('status', DB::raw('count(*) as total'))
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->groupBy('status')
            ->get();

        // Horários mais movimentados
        $busyHours = Scheduling::select('time', DB::raw('count(*) as total'))
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->groupBy('time')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Profissionais mais acionados
        $busyProfessionals = Scheduling::select('professional_id', DB::raw('count(*) as total'))
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->with('profissional:id,name')
            ->groupBy('professional_id')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Taxa de comparecimento por dia da semana
        $weeklyAttendance = Scheduling::select(
            DB::raw('DAYOFWEEK(scheduled_at) as day_of_week'),
            DB::raw('count(*) as total'),
            DB::raw('sum(case when status = "realizado" then 1 else 0 end) as completed')
        )
        ->whereBetween('scheduled_at', [$startDate, $endDate])
        ->groupBy('day_of_week')
        ->get();

        $data = [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'summary' => [
                'total_appointments' => $totalAppointments,
                'completed_appointments' => $completedAppointments,
                'canceled_appointments' => $canceledAppointments,
                'completion_rate' => $totalAppointments > 0 ? round(($completedAppointments / $totalAppointments) * 100, 2) : 0,
                'cancellation_rate' => $totalAppointments > 0 ? round(($canceledAppointments / $totalAppointments) * 100, 2) : 0
            ],
            'distributions' => [
                'status_distribution' => $statusDistribution,
                'busy_hours' => $busyHours,
                'busy_professionals' => $busyProfessionals,
                'weekly_attendance' => $weeklyAttendance
            ]
        ];

        return response()->json([
            'success' => true,
            'message' => 'Relatório de agendamentos gerado com sucesso',
            'data' => $data
        ]);
    }

    /**
     * Relatório Financeiro
     */
    public function financialReport(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::today()->toDateString());

        // Receita por procedimentos realizados
        $completedAppointments = Scheduling::whereBetween('scheduled_at', [$startDate, $endDate])
            ->where('status', 'realizado')
            ->with('procedimento')
            ->get();

        $totalRevenue = $completedAppointments->sum(function($appointment) {
            return $appointment->procedimento->value ?? 0;
        });

        // Procedimentos mais realizados
        $topProcedures = Scheduling::select('procedure_id', DB::raw('count(*) as quantity'))
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->where('status', 'realizado')
            ->with('procedimento:id,name,value')
            ->groupBy('procedure_id')
            ->orderBy('quantity', 'desc')
            ->limit(10)
            ->get();

        // Receita por forma de pagamento (simulada)
        $paymentMethods = FormaPagamento::all();
        $paymentDistribution = $paymentMethods->map(function($method) use ($totalRevenue) {
            // Simulação de distribuição baseada na taxa
            $percentage = $method->taxa == 0 ? 30 : (10 - $method->taxa);
            return [
                'method' => $method->nome,
                'amount' => round(($totalRevenue * $percentage) / 100, 2),
                'percentage' => $percentage
            ];
        });

        // Receita mensal
        $monthlyRevenue = Scheduling::select(
            DB::raw('YEAR(scheduled_at) as year'),
            DB::raw('MONTH(scheduled_at) as month'),
            DB::raw('count(*) as appointments')
        )
        ->where('status', 'realizado')
        ->whereBetween('scheduled_at', [Carbon::now()->subMonths(6), Carbon::now()])
        ->with('procedimento:id,value')
        ->groupBy('year', 'month')
        ->orderBy('year', 'desc')
        ->orderBy('month', 'desc')
        ->get();

        $data = [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'summary' => [
                'total_revenue' => $totalRevenue,
                'completed_procedures' => $completedAppointments->count(),
                'average_procedure_value' => $completedAppointments->count() > 0 ? 
                    round($totalRevenue / $completedAppointments->count(), 2) : 0
            ],
            'procedures' => [
                'top_procedures' => $topProcedures,
                'total_procedure_types' => Procedure::count()
            ],
            'payments' => [
                'payment_distribution' => $paymentDistribution,
                'total_payment_methods' => $paymentMethods->count()
            ],
            'trends' => [
                'monthly_revenue' => $monthlyRevenue
            ]
        ];

        return response()->json([
            'success' => true,
            'message' => 'Relatório financeiro gerado com sucesso',
            'data' => $data
        ]);
    }

    /**
     * Relatório de Desempenho dos Profissionais
     */
    public function staffPerformanceReport(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::today()->toDateString());

        // Desempenho por profissional
        $staffPerformance = Employee::withCount([
            'agendamentos as total_appointments' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('scheduled_at', [$startDate, $endDate]);
            },
            'agendamentos as completed_appointments' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('scheduled_at', [$startDate, $endDate])
                      ->where('status', 'realizado');
            }
        ])->get();

        // Calcular taxas de conclusão
        $staffPerformance->map(function($employee) {
            $employee->completion_rate = $employee->total_appointments > 0 ? 
                round(($employee->completed_appointments / $employee->total_appointments) * 100, 2) : 0;
            return $employee;
        });

        // Top performers
        $topPerformers = $staffPerformance->sortByDesc('completed_appointments')->take(5);

        $data = [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'summary' => [
                'total_staff' => Employee::count(),
                'active_staff' => Employee::where('active', true)->count(),
                'total_staff_appointments' => $staffPerformance->sum('total_appointments'),
                'total_completed_procedures' => $staffPerformance->sum('completed_appointments')
            ],
            'performance' => [
                'all_staff' => $staffPerformance,
                'top_performers' => $topPerformers->values()
            ],
            'roles_distribution' => Employee::select('role', DB::raw('count(*) as total'))
                ->groupBy('role')
                ->get()
        ];

        return response()->json([
            'success' => true,
            'message' => 'Relatório de desempenho dos profissionais gerado com sucesso',
            'data' => $data
        ]);
    }

    /**
     * Relatório de Produtividade Mensal
     */
    public function monthlyProductivityReport(): JsonResponse
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        
        $currentMonthData = [
            'patients' => Paciente::where('created_at', '>=', $currentMonth)->count(),
            'appointments' => Scheduling::where('scheduled_at', '>=', $currentMonth)->count(),
            'completed_procedures' => Scheduling::where('scheduled_at', '>=', $currentMonth)
                ->where('status', 'realizado')->count(),
            'revenue' => Scheduling::where('scheduled_at', '>=', $currentMonth)
                ->where('status', 'realizado')
                ->with('procedimento')
                ->get()
                ->sum(function($appointment) {
                    return $appointment->procedimento->value ?? 0;
                })
        ];

        $lastMonthData = [
            'patients' => Paciente::whereBetween('created_at', [$lastMonth, $currentMonth])->count(),
            'appointments' => Scheduling::whereBetween('scheduled_at', [$lastMonth, $currentMonth])->count(),
            'completed_procedures' => Scheduling::whereBetween('scheduled_at', [$lastMonth, $currentMonth])
                ->where('status', 'realizado')->count(),
            'revenue' => Scheduling::whereBetween('scheduled_at', [$lastMonth, $currentMonth])
                ->where('status', 'realizado')
                ->with('procedimento')
                ->get()
                ->sum(function($appointment) {
                    return $appointment->procedimento->value ?? 0;
                })
        ];

        // Calcular crescimento
        $growth = [];
        foreach ($currentMonthData as $key => $current) {
            $previous = $lastMonthData[$key];
            $growth[$key] = $previous > 0 ? round((($current - $previous) / $previous) * 100, 2) : 0;
        }

        $data = [
            'current_month' => [
                'period' => $currentMonth->format('Y-m'),
                'data' => $currentMonthData
            ],
            'last_month' => [
                'period' => $lastMonth->format('Y-m'),
                'data' => $lastMonthData
            ],
            'growth_rates' => $growth,
            'generated_at' => now()->toISOString()
        ];

        return response()->json([
            'success' => true,
            'message' => 'Relatório de produtividade mensal gerado com sucesso',
            'data' => $data
        ]);
    }
}
