<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('saas_billing_modulos', function (Blueprint $table) {
            $table->id();
            $table->string('submodulo', 60);
            $table->string('nome', 180);
            $table->string('codigo', 120)->unique();
            $table->enum('status', ['ativo', 'inativo', 'arquivado'])->default('ativo');
            $table->enum('prioridade', ['baixa', 'media', 'alta', 'critica'])->default('media');
            $table->string('owner_nome', 180)->nullable();
            $table->string('owner_email', 180)->nullable();
            $table->text('descricao')->nullable();
            $table->json('configuracao')->nullable();
            $table->json('metricas')->nullable();
            $table->json('limites')->nullable();
            $table->json('integracoes')->nullable();
            $table->json('politicas')->nullable();
            $table->json('tags')->nullable();
            $table->timestamp('ultimo_evento_em')->nullable();
            $table->integer('sla_horas')->default(24);
            $table->decimal('custo_mensal', 12, 2)->default(0);
            $table->decimal('receita_mensal', 12, 2)->default(0);
            $table->enum('risco_nivel', ['baixo', 'medio', 'alto', 'critico'])->default('medio');
            $table->decimal('conformidade_score', 5, 2)->default(0);
            $table->boolean('ativo')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->index(['submodulo', 'status']);
            $table->index(['status', 'prioridade']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_billing_modulos');
    }
};
