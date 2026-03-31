<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('grupo_acessos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->text('descricao')->nullable();
            $table->string('cor', 7)->default('#3B82F6');
            $table->json('permissoes')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index('ativo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupo_acessos');
    }
};
