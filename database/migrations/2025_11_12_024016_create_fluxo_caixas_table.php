<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fluxo_caixas', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['Entrada', 'Saída']);
            $table->string('descricao');
            $table->string('categoria');
            $table->decimal('valor', 10, 2);
            $table->date('data_movimento');
            $table->string('forma_pagamento');
            $table->string('documento')->nullable();
            $table->unsignedBigInteger('conta_receber_id')->nullable();
            $table->unsignedBigInteger('conta_pagar_id')->nullable();
            $table->unsignedBigInteger('paciente_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->text('observacoes')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();

            $table->foreign('conta_receber_id')->references('id')->on('contas_recebers')->onDelete('set null');
            $table->foreign('conta_pagar_id')->references('id')->on('contas_pagars')->onDelete('set null');
            $table->foreign('paciente_id')->references('id')->on('pacientes')->onDelete('set null');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
            $table->index(['tipo', 'data_movimento']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fluxo_caixas');
    }
};
