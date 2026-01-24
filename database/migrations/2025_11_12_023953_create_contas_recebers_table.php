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
        Schema::create('contas_recebers', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->unsignedBigInteger('paciente_id');
            $table->unsignedBigInteger('procedure_id');
            $table->unsignedBigInteger('scheduling_id')->nullable();
            $table->enum('categoria', ['Consulta', 'Limpeza', 'Restauração', 'Endodontia', 'Ortodontia', 'Cirurgia', 'Prótese', 'Implante', 'Clareamento', 'Outros'])->default('Consulta');
            $table->decimal('valor_original', 10, 2);
            $table->decimal('valor_recebido', 10, 2)->default(0);
            $table->decimal('valor_pendente', 10, 2);
            $table->date('data_vencimento');
            $table->date('data_recebimento')->nullable();
            $table->enum('status', ['Pendente', 'Vencido', 'Recebido', 'Parcial'])->default('Pendente');
            $table->enum('prioridade', ['Baixa', 'Média', 'Alta', 'Crítica'])->default('Média');
            $table->string('forma_pagamento')->nullable();
            $table->string('convenio')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->foreign('paciente_id')->references('id')->on('pacientes')->onDelete('cascade');
            $table->foreign('procedure_id')->references('id')->on('procedures')->onDelete('cascade');
            $table->foreign('scheduling_id')->references('id')->on('schedulings')->onDelete('set null');
            $table->index(['status', 'data_vencimento']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contas_recebers');
    }
};
