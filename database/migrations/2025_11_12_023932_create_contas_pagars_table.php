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
        Schema::create('contas_pagars', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('descricao');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('categoria');
            $table->decimal('valor_original', 10, 2);
            $table->decimal('valor_pago', 10, 2)->default(0);
            $table->decimal('valor_pendente', 10, 2);
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            $table->enum('status', ['Pendente', 'Vencido', 'Pago', 'Parcial'])->default('Pendente');
            $table->enum('prioridade', ['Baixa', 'Média', 'Alta', 'Crítica'])->default('Média');
            $table->string('forma_pagamento')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
            $table->index(['status', 'data_vencimento']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contas_pagars');
    }
};
