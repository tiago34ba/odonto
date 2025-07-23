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


Route::prefix('pessoas/pacientes')->group(function () {
    Route::get('/', [PacienteController::class, 'index']); // Listar pacientes
    Route::post('/', [PacienteController::class, 'store']); // Criar paciente
    Route::get('/{id}', [PacienteController::class, 'show']); // Mostrar paciente específico
    Route::put('/{id}', [PacienteController::class, 'update']); // Atualizar paciente
    Route::delete('/{id}', [PacienteController::class, 'destroy']); // Deletar paciente
});

Route::prefix('anamneses')->group(function () {
    Route::get('/', [AnamneseController::class, 'index']); // Listar
    Route::post('/', [AnamneseController::class, 'store']); // Criar
    Route::get('/{id}', [AnamneseController::class, 'show']); // Mostrar específico
    Route::put('/{id}', [AnamneseController::class, 'update']); // Atualizar
    Route::delete('/{id}', [AnamneseController::class, 'destroy']); // Deletar
});

Route::prefix('procedures')->group(function () {
    Route::get('/', [ProcedureController::class, 'index']);
    Route::post('/', [ProcedureController::class, 'store']);
    Route::get('/{id}', [ProcedureController::class, 'show']);
    Route::put('/{id}', [ProcedureController::class, 'update']);
    Route::delete('/{id}', [ProcedureController::class, 'destroy']);
});

Route::prefix('employees')->group(function () {
    Route::get('/', [EmployeeController::class, 'index']);
    Route::post('/', [EmployeeController::class, 'store']);
    Route::get('/{id}', [EmployeeController::class, 'show']);
    Route::put('/{id}', [EmployeeController::class, 'update']);
    Route::delete('/{id}', [EmployeeController::class, 'destroy']);
});

Route::prefix('schedulings')->group(function () {
    Route::get('/', [SchedulingController::class, 'index']);
    Route::post('/', [SchedulingController::class, 'store']);
    Route::get('/{id}', [SchedulingController::class, 'show']);
    Route::put('/{id}', [SchedulingController::class, 'update']);
    Route::delete('/{id}', [SchedulingController::class, 'destroy']);
});

Route::prefix('agreements')->group(function () {
    Route::get('/', [AgreementController::class, 'index']);
    Route::post('/', [AgreementController::class, 'store']);
    Route::get('/{id}', [AgreementController::class, 'show']);
    Route::put('/{id}', [AgreementController::class, 'update']);
    Route::delete('/{id}', [AgreementController::class, 'destroy']);
});

Route::prefix('groups-anamnese')->group(function () {
    Route::get('/', [GroupAnamneseController::class, 'index']);
    Route::post('/', [GroupAnamneseController::class, 'store']);
    Route::get('/{id}', [GroupAnamneseController::class, 'show']);
    Route::put('/{id}', [GroupAnamneseController::class, 'update']);
    Route::delete('/{id}', [GroupAnamneseController::class, 'destroy']);
});

Route::prefix('suppliers')->group(function () {
    Route::get('/', [SupplierController::class, 'index']);
    Route::post('/', [SupplierController::class, 'store']);
    Route::get('/{id}', [SupplierController::class, 'show']);
    Route::put('/{id}', [SupplierController::class, 'update']);
    Route::delete('/{id}', [SupplierController::class, 'destroy']);
});

Route::prefix('formas-pagamentos')->group(function () {
    Route::get('/', [FormaPagamentoController::class, 'index']);
    Route::post('/', [FormaPagamentoController::class, 'store']);
    Route::get('/{id}', [FormaPagamentoController::class, 'show']);
    Route::put('/{id}', [FormaPagamentoController::class, 'update']);
    Route::delete('/{id}', [FormaPagamentoController::class, 'destroy']);
});
