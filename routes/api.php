<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\AnamneseController;
use App\Http\Controllers\ProcedureController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\SchedulingController;
use App\Http\Controllers\AgreementController;
use App\Http\Controllers\GroupAnamneseController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\FormaPagamentoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OdontogramaController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ContasPagarController;
use App\Http\Controllers\ContasReceberController;
use App\Http\Controllers\FluxoCaixaController;


// Usuários apenas GET
Route::get('users', [UserController::class, 'index']);

// Cadastro via RegisterController
Route::post('/register', [RegisterController::class, 'register']);

// Login
Route::post('/login', [AuthController::class, 'login']);

// Dados do usuário autenticado
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Dashboard routes
Route::prefix('dashboard')->group(function () {
    Route::apiResource('/', DashboardController::class);
    Route::get('/overview', [DashboardController::class, 'overview']);
    Route::get('/module-counters', [DashboardController::class, 'moduleCounters']); // Contadores em tempo real
    Route::get('/patients-stats', [DashboardController::class, 'patientsStats']);
    Route::get('/appointments-stats', [DashboardController::class, 'appointmentsStats']);
    Route::get('/procedures-stats', [DashboardController::class, 'proceduresStats']);
    Route::get('/recent-activities', [DashboardController::class, 'recentActivities']);
    Route::get('/system-health', [DashboardController::class, 'systemHealth']);
});


Route::prefix('pessoas/pacientes')->group(function () {
    Route::apiResource('/', PacienteController::class);
    Route::get('/search', [PacienteController::class, 'search']); // Busca avançada
    Route::get('/statistics', [PacienteController::class, 'statistics']); // Estatísticas
    Route::get('/export', [PacienteController::class, 'export']); // Exportar dados
    Route::get('/{id}/masked', [PacienteController::class, 'masked']); // Dados mascarados

    // Endpoints de dados de referência
    Route::get('/reference/convenios', [PacienteController::class, 'convenios']);
    Route::get('/reference/estados', [PacienteController::class, 'estados']);
    Route::get('/reference/sexos', [PacienteController::class, 'sexos']);
    Route::get('/reference/estados-civis', [PacienteController::class, 'estadosCivis']);
    Route::get('/reference/tipos-sanguineos', [PacienteController::class, 'tiposSanguineos']);
});

Route::prefix('pessoas/employees')->group(function () {
    Route::apiResource('/', EmployeeController::class);
});

Route::prefix('anamneses')->group(function () {
    Route::apiResource('/', AnamneseController::class);
});

Route::prefix('procedures')->group(function () {
    Route::apiResource('/', ProcedureController::class);
});

Route::prefix('schedulings')->group(function () {
    Route::apiResource('/', SchedulingController::class);
    Route::get('/today', [SchedulingController::class, 'today']);
    Route::get('/this-week', [SchedulingController::class, 'thisWeek']);
    Route::get('/available-slots', [SchedulingController::class, 'availableSlots']);
    Route::patch('/{id}/confirm', [SchedulingController::class, 'confirm']);
    Route::patch('/{id}/cancel', [SchedulingController::class, 'cancel']);
    Route::patch('/{id}/complete', [SchedulingController::class, 'complete']);
});

Route::prefix('agreements')->group(function () {
    Route::apiResource('/', AgreementController::class);
});

Route::prefix('groups-anamnese')->group(function () {
    Route::apiResource('/', GroupAnamneseController::class);
});

Route::prefix('suppliers')->group(function () {
    Route::apiResource('/', SupplierController::class);
});

Route::prefix('formas-pagamentos')->group(function () {
    Route::apiResource('/', FormaPagamentoController::class);
});

// Odontograma routes
Route::prefix('pacientes/{pacienteId}/odontograma')->group(function () {
    Route::get('/', [OdontogramaController::class, 'show']); // Obter odontograma
    Route::put('/', [OdontogramaController::class, 'update']); // Atualizar odontograma
    Route::get('/historico', [OdontogramaController::class, 'historico']); // Histórico de alterações
    Route::get('/export-pdf', [OdontogramaController::class, 'exportPdf']); // Exportar PDF
});

Route::prefix('odontograma')->group(function () {
    Route::get('/legendas', [OdontogramaController::class, 'getLegendas']); // Legendas e códigos
    Route::get('/template-dentes', [OdontogramaController::class, 'getDentesTemplate']); // Template de dentes
});

// Reports routes
Route::prefix('reports')->group(function () {
    Route::get('/', [ReportController::class, 'index']); // Listar relatórios salvos
    Route::get('/system-overview', [ReportController::class, 'systemOverview']); // Relatório geral do sistema
    Route::get('/patients', [ReportController::class, 'patientsReport']); // Relatório de pacientes
    Route::get('/appointments', [ReportController::class, 'appointmentsReport']); // Relatório de agendamentos
    Route::get('/financial', [ReportController::class, 'financialReport']); // Relatório financeiro
    Route::get('/staff-performance', [ReportController::class, 'staffPerformanceReport']); // Relatório de profissionais
    Route::get('/monthly-productivity', [ReportController::class, 'monthlyProductivityReport']); // Relatório de produtividade
    Route::post('/', [ReportController::class, 'store']); // Salvar relatório
    Route::get('/{id}', [ReportController::class, 'show']); // Mostrar relatório específico
    Route::delete('/{id}', [ReportController::class, 'destroy']); // Deletar relatório
});

// Financial Management Routes - Sistema Financeiro Completo
Route::prefix('financeiro')->group(function () {

    // Dashboard Financeiro
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'financeiro']);

    // Contas a Pagar
    Route::prefix('contas-pagar')->group(function () {
        Route::get('/', [ContasPagarController::class, 'index']); // Listar contas
        Route::post('/', [ContasPagarController::class, 'store']); // Criar conta
        Route::get('/dashboard', [ContasPagarController::class, 'dashboard']); // Dashboard contas a pagar
        Route::get('/vencendo', [ContasPagarController::class, 'vencendo']); // Contas vencendo
        Route::get('/vencidas', [ContasPagarController::class, 'vencidas']); // Contas vencidas
        Route::get('/pagas', [ContasPagarController::class, 'pagas']); // Contas pagas
        Route::get('/pendentes', [ContasPagarController::class, 'pendentes']); // Contas pendentes
        Route::get('/por-fornecedor/{fornecedorId}', [ContasPagarController::class, 'porFornecedor']); // Por fornecedor
        Route::get('/relatorio-periodo', [ContasPagarController::class, 'relatorioPorPeriodo']); // Relatório por período
        Route::get('/{id}', [ContasPagarController::class, 'show']); // Mostrar conta específica
        Route::put('/{id}', [ContasPagarController::class, 'update']); // Atualizar conta
        Route::delete('/{id}', [ContasPagarController::class, 'destroy']); // Deletar conta
        Route::patch('/{id}/pagar', [ContasPagarController::class, 'registrarPagamento']); // Registrar pagamento
        Route::patch('/{id}/cancelar-pagamento', [ContasPagarController::class, 'cancelarPagamento']); // Cancelar pagamento
    });

    // Contas a Receber
    Route::prefix('contas-receber')->group(function () {
        Route::get('/', [ContasReceberController::class, 'index']); // Listar contas
        Route::post('/', [ContasReceberController::class, 'store']); // Criar conta
        Route::get('/dashboard', [ContasReceberController::class, 'dashboard']); // Dashboard contas a receber
        Route::get('/vencendo', [ContasReceberController::class, 'vencendo']); // Contas vencendo
        Route::get('/vencidas', [ContasReceberController::class, 'vencidas']); // Contas vencidas
        Route::get('/recebidas', [ContasReceberController::class, 'recebidas']); // Contas recebidas
        Route::get('/pendentes', [ContasReceberController::class, 'pendentes']); // Contas pendentes
        Route::get('/por-paciente/{pacienteId}', [ContasReceberController::class, 'porPaciente']); // Por paciente
        Route::get('/por-categoria/{categoria}', [ContasReceberController::class, 'porCategoria']); // Por categoria
        Route::get('/relatorio-periodo', [ContasReceberController::class, 'relatorioPorPeriodo']); // Relatório por período
        Route::get('/{id}', [ContasReceberController::class, 'show']); // Mostrar conta específica
        Route::put('/{id}', [ContasReceberController::class, 'update']); // Atualizar conta
        Route::delete('/{id}', [ContasReceberController::class, 'destroy']); // Deletar conta
        Route::patch('/{id}/receber', [ContasReceberController::class, 'registrarRecebimento']); // Registrar recebimento
        Route::patch('/{id}/cancelar-recebimento', [ContasReceberController::class, 'cancelarRecebimento']); // Cancelar recebimento
    });

    // Fluxo de Caixa
    Route::prefix('fluxo-caixa')->group(function () {
        Route::get('/', [FluxoCaixaController::class, 'index']); // Histórico de movimentações
        Route::post('/', [FluxoCaixaController::class, 'store']); // Criar movimentação manual
        Route::get('/dashboard', [FluxoCaixaController::class, 'dashboard']); // Dashboard do fluxo
        Route::get('/saldo-atual', [FluxoCaixaController::class, 'saldoAtual']); // Saldo atual
        Route::get('/movimentacoes-periodo', [FluxoCaixaController::class, 'movimentacoesPorPeriodo']); // Por período
        Route::get('/entradas', [FluxoCaixaController::class, 'entradas']); // Entradas
        Route::get('/saidas', [FluxoCaixaController::class, 'saidas']); // Saídas
        Route::get('/relatorio-diario', [FluxoCaixaController::class, 'relatorioDiario']); // Relatório diário
        Route::get('/relatorio-mensal', [FluxoCaixaController::class, 'relatorioMensal']); // Relatório mensal
        Route::get('/projecao/{dias}', [FluxoCaixaController::class, 'projecaoFutura']); // Projeção futura
        Route::get('/{id}', [FluxoCaixaController::class, 'show']); // Mostrar movimentação específica
        Route::put('/{id}', [FluxoCaixaController::class, 'update']); // Atualizar movimentação
        Route::delete('/{id}', [FluxoCaixaController::class, 'destroy']); // Deletar movimentação
    });

    // Relatórios Financeiros Consolidados
    Route::prefix('relatorios')->group(function () {
        Route::get('/consolidado', [\App\Http\Controllers\DashboardController::class, 'financeiro']); // Relatório consolidado
        Route::get('/dre', [\App\Http\Controllers\DashboardController::class, 'financeiro']); // DRE - Demonstração do Resultado do Exercício
        Route::get('/balancete', [FluxoCaixaController::class, 'relatorioMensal']); // Balancete mensal
    });
});
