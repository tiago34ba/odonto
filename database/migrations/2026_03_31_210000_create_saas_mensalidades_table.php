<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_mensalidades', function (Blueprint $table) {
            $table->id();
            $table->string('clinica')->unique();
            $table->string('plano_id', 30);
            $table->string('plano_nome', 60);
            $table->decimal('valor_mensal', 10, 2)->default(0);
            $table->unsignedInteger('total_usuarios')->default(0);
            $table->unsignedInteger('usuarios_ativos')->default(0);
            $table->date('proximo_vencimento');
            $table->enum('status', ['em_dia', 'atrasada', 'inadimplente', 'suspensa'])->default('suspensa');
            $table->timestamps();

            $table->index(['status', 'plano_id'], 'saas_mensalidades_status_plano_idx');
            $table->index('proximo_vencimento', 'saas_mensalidades_vencimento_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_mensalidades');
    }
};
