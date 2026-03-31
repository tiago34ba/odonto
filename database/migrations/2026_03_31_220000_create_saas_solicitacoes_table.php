<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_solicitacoes', function (Blueprint $table) {
            $table->id();
            $table->string('nome_clinica');
            $table->string('responsavel');
            $table->string('email')->unique();
            $table->string('telefone')->nullable();
            $table->string('cnpj', 20)->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();
            $table->enum('plano_id', ['basico', 'profissional', 'premium'])->default('basico');
            $table->enum('status', [
                'pendente',
                'aguardando_pagamento',
                'aprovada',
                'rejeitada',
            ])->default('pendente');
            $table->boolean('pagamento_confirmado')->default(false);
            $table->timestamp('data_pagamento')->nullable();
            $table->string('comprovante_pagamento')->nullable();
            $table->text('observacoes')->nullable();
            $table->string('motivo_rejeicao')->nullable();
            $table->unsignedBigInteger('aprovado_por')->nullable();
            $table->timestamp('aprovado_em')->nullable();
            $table->unsignedBigInteger('rejeitado_por')->nullable();
            $table->timestamp('rejeitado_em')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('plano_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_solicitacoes');
    }
};
