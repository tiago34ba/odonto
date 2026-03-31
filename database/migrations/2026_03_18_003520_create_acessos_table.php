<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('acessos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('codigo')->unique();
            $table->text('descricao')->nullable();
            $table->string('categoria')->default('Geral');
            $table->enum('nivel_risco', ['baixo', 'medio', 'alto', 'critico'])->default('baixo');
            $table->boolean('sistema_interno')->default(true);
            $table->boolean('ativo')->default(true);
            $table->foreignId('grupo_acesso_id')->nullable()->constrained('grupo_acessos')->nullOnDelete();
            $table->timestamps();

            $table->index(['categoria', 'ativo']);
            $table->index('nivel_risco');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acessos');
    }
};
