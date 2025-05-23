<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\Api\PatientController;

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
Route::apiResource('patients', PacienteController::class);
