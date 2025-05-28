<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\AnamneseController;
use App\Http\Controllers\ProcedureController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\SchedulingController;
use App\Http\Controllers\AgreementController;
use App\Http\Controllers\GroupAnamneseController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\FormaPagamentoController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


/*Route::prefix('pessoas/pacientes')->group(function () {
    Route::get('/', [PacienteController::class, 'index']); // Listar pacientes
    Route::post('/', [PacienteController::class, 'store']); // Criar paciente
    Route::get('/{id}', [PacienteController::class, 'show']); // Mostrar paciente específico
    Route::put('/{id}', [PacienteController::class, 'update']); // Atualizar paciente
    Route::delete('/{id}', [PacienteController::class, 'destroy']); // Deletar paciente
});*/
Route::apiResource('pessoas/pacientes', PacienteController::class);
Route::apiResource('anamneses', AnamneseController::class);
Route::apiResource('procedures', ProcedureController::class);
Route::apiResource('employees', EmployeeController::class);
Route::apiResource('schedulings', SchedulingController::class);
Route::apiResource('agreements', AgreementController::class);
Route::apiResource('groups-anamnese', GroupAnamneseController::class);
Route::apiResource('suppliers', SupplierController::class);
Route::apiResource('formas-pagamentos', FormaPagamentoController::class);
