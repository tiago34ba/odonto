<?php

namespace App\Services;

use App\Models\Paciente;
use App\Models\Agendamento;
use App\Models\Procedimento;
use App\Models\Funcionario;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    /**
     * Relatório de agendamentos otimizado
     */
    public static function getAppointmentsReport($filters = [])
    {
        return CacheService::cacheReports(function () use ($filters) {
            $query = Agendamento::query()
                ->select([
                    'agendamentos.id',
                    'agendamentos.data_agendamento',
                    'agendamentos.hora',
                    'agendamentos.status',
                    'pacientes.nome as paciente_nome',
                    'pacientes.telefone as paciente_telefone',
                    'funcionarios.nome as funcionario_nome',
                    'procedimentos.nome as procedimento_nome',
                    'procedimentos.valor'
                ])
                ->join('pacientes', 'agendamentos.paciente_id', '=', 'pacientes.id')
                ->join('funcionarios', 'agendamentos.funcionario_id', '=', 'funcionarios.id')
                ->join('procedimentos', 'agendamentos.procedimento_id', '=', 'procedimentos.id');

            // Aplicar filtros
            if (isset($filters['start_date'])) {
                $query->where('data_agendamento', '>=', $filters['start_date']);
            }

            if (isset($filters['end_date'])) {
                $query->where('data_agendamento', '<=', $filters['end_date']);
            }

            if (isset($filters['status'])) {
                $query->where('agendamentos.status', $filters['status']);
            }

            if (isset($filters['funcionario_id'])) {
                $query->where('funcionarios.id', $filters['funcionario_id']);
            }

            return $query->orderBy('data_agendamento', 'desc')
                         ->orderBy('hora', 'desc')
                         ->get();
        }, 'appointments', $filters);
    }

    /**
     * Relatório de procedimentos mais realizados
     */
    public static function getProceduresReport($filters = [])
    {
        return CacheService::cacheReports(function () use ($filters) {
            $query = DB::table('agendamentos')
                ->select([
                    'procedimentos.nome',
                    'procedimentos.valor',
                    DB::raw('COUNT(*) as total_realizados'),
                    DB::raw('SUM(procedimentos.valor) as receita_total'),
                    DB::raw('AVG(procedimentos.valor) as valor_medio')
                ])
                ->join('procedimentos', 'agendamentos.procedimento_id', '=', 'procedimentos.id')
                ->where('agendamentos.status', 'concluido');

            // Aplicar filtros de data
            if (isset($filters['start_date'])) {
                $query->where('agendamentos.data_agendamento', '>=', $filters['start_date']);
            }

            if (isset($filters['end_date'])) {
                $query->where('agendamentos.data_agendamento', '<=', $filters['end_date']);
            }

            return $query->groupBy('procedimentos.id', 'procedimentos.nome', 'procedimentos.valor')
                         ->orderBy('total_realizados', 'desc')
                         ->get();
        }, 'procedures', $filters);
    }

    /**
     * Relatório financeiro otimizado
     */
    public static function getFinancialReport($filters = [])
    {
        return CacheService::cacheReports(function () use ($filters) {
            $startDate = $filters['start_date'] ?? Carbon::now()->startOfMonth();
            $endDate = $filters['end_date'] ?? Carbon::now()->endOfMonth();

            // Receitas (agendamentos concluídos)
            $receitas = DB::table('agendamentos')
                ->select([
                    DB::raw('DATE(data_agendamento) as data'),
                    DB::raw('SUM(procedimentos.valor) as receita_diaria'),
                    DB::raw('COUNT(*) as agendamentos_concluidos')
                ])
                ->join('procedimentos', 'agendamentos.procedimento_id', '=', 'procedimentos.id')
                ->where('agendamentos.status', 'concluido')
                ->whereBetween('data_agendamento', [$startDate, $endDate])
                ->groupBy(DB::raw('DATE(data_agendamento)'))
                ->orderBy('data')
                ->get();

            // Estatísticas gerais
            $estatisticas = DB::table('agendamentos')
                ->select([
                    DB::raw('COUNT(*) as total_agendamentos'),
                    DB::raw('SUM(CASE WHEN status = "concluido" THEN 1 ELSE 0 END) as concluidos'),
                    DB::raw('SUM(CASE WHEN status = "cancelado" THEN 1 ELSE 0 END) as cancelados'),
                    DB::raw('SUM(CASE WHEN status = "concluido" THEN procedimentos.valor ELSE 0 END) as receita_total')
                ])
                ->join('procedimentos', 'agendamentos.procedimento_id', '=', 'procedimentos.id')
                ->whereBetween('data_agendamento', [$startDate, $endDate])
                ->first();

            return [
                'receitas_diarias' => $receitas,
                'estatisticas' => $estatisticas,
                'periodo' => [
                    'inicio' => $startDate,
                    'fim' => $endDate
                ]
            ];
        }, 'financial', $filters);
    }

    /**
     * Relatório de performance dos funcionários
     */
    public static function getEmployeePerformanceReport($filters = [])
    {
        return CacheService::cacheReports(function () use ($filters) {
            $query = DB::table('funcionarios')
                ->select([
                    'funcionarios.id',
                    'funcionarios.nome',
                    'funcionarios.especialidade',
                    DB::raw('COUNT(agendamentos.id) as total_agendamentos'),
                    DB::raw('SUM(CASE WHEN agendamentos.status = "concluido" THEN 1 ELSE 0 END) as agendamentos_concluidos'),
                    DB::raw('SUM(CASE WHEN agendamentos.status = "concluido" THEN procedimentos.valor ELSE 0 END) as receita_gerada'),
                    DB::raw('ROUND(AVG(CASE WHEN agendamentos.status = "concluido" THEN procedimentos.valor END), 2) as ticket_medio')
                ])
                ->leftJoin('agendamentos', 'funcionarios.id', '=', 'agendamentos.funcionario_id')
                ->leftJoin('procedimentos', 'agendamentos.procedimento_id', '=', 'procedimentos.id');

            // Aplicar filtros de data
            if (isset($filters['start_date'])) {
                $query->where('agendamentos.data_agendamento', '>=', $filters['start_date']);
            }

            if (isset($filters['end_date'])) {
                $query->where('agendamentos.data_agendamento', '<=', $filters['end_date']);
            }

            return $query->groupBy('funcionarios.id', 'funcionarios.nome', 'funcionarios.especialidade')
                         ->orderBy('receita_gerada', 'desc')
                         ->get();
        }, 'employee_performance', $filters);
    }

    /**
     * Relatório de pacientes otimizado
     */
    public static function getPatientsReport($filters = [])
    {
        return CacheService::cacheReports(function () use ($filters) {
            $query = DB::table('pacientes')
                ->select([
                    'pacientes.id',
                    'pacientes.nome',
                    'pacientes.email',
                    'pacientes.telefone',
                    'pacientes.created_at as data_cadastro',
                    DB::raw('COUNT(agendamentos.id) as total_agendamentos'),
                    DB::raw('SUM(CASE WHEN agendamentos.status = "concluido" THEN procedimentos.valor ELSE 0 END) as valor_total_gasto'),
                    DB::raw('MAX(agendamentos.data_agendamento) as ultimo_agendamento')
                ])
                ->leftJoin('agendamentos', 'pacientes.id', '=', 'agendamentos.paciente_id')
                ->leftJoin('procedimentos', 'agendamentos.procedimento_id', '=', 'procedimentos.id');

            // Aplicar filtros
            if (isset($filters['start_date'])) {
                $query->where('pacientes.created_at', '>=', $filters['start_date']);
            }

            if (isset($filters['end_date'])) {
                $query->where('pacientes.created_at', '<=', $filters['end_date']);
            }

            return $query->groupBy('pacientes.id', 'pacientes.nome', 'pacientes.email', 'pacientes.telefone', 'pacientes.created_at')
                         ->orderBy('valor_total_gasto', 'desc')
                         ->get();
        }, 'patients', $filters);
    }

    /**
     * Dashboard - Estatísticas rápidas
     */
    public static function getDashboardStats()
    {
        return CacheService::cacheDashboardStats(function () {
            $hoje = Carbon::today();
            $mesAtual = Carbon::now()->startOfMonth();
            $mesPassado = Carbon::now()->subMonth()->startOfMonth();

            return [
                'pacientes_total' => Paciente::count(),
                'pacientes_mes_atual' => Paciente::where('created_at', '>=', $mesAtual)->count(),
                'agendamentos_hoje' => Agendamento::whereDate('data_agendamento', $hoje)->count(),
                'agendamentos_mes_atual' => Agendamento::where('data_agendamento', '>=', $mesAtual)->count(),
                'agendamentos_concluidos_mes' => Agendamento::where('data_agendamento', '>=', $mesAtual)
                    ->where('status', 'concluido')->count(),
                'receita_mes_atual' => DB::table('agendamentos')
                    ->join('procedimentos', 'agendamentos.procedimento_id', '=', 'procedimentos.id')
                    ->where('agendamentos.data_agendamento', '>=', $mesAtual)
                    ->where('agendamentos.status', 'concluido')
                    ->sum('procedimentos.valor'),
                'funcionarios_ativos' => DB::table('funcionarios')->where('ativo', true)->count(),
                'procedimentos_disponiveis' => Procedimento::where('ativo', true)->count()
            ];
        });
    }

    /**
     * Limpar cache de relatórios
     */
    public static function clearReportsCache()
    {
        CacheService::forgetReports();
        CacheService::forgetDashboard();
    }
}
