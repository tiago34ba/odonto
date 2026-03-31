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
        Schema::create('funcionarios', function (Blueprint $table) {
            $table->id();

            // Dados pessoais
            $table->string('name');
            $table->string('telefone', 20)->nullable();
            $table->string('email')->nullable()->unique();

            // Cargo (FK opcional para tabela cargos)
            $table->foreignId('cargo_id')->nullable()->constrained('cargos')->nullOnDelete();

            // Data de cadastro do funcionário (não confundir com created_at)
            $table->date('data_cadastro')->nullable();

            // Foto
            $table->string('foto')->nullable();

            // Endereço
            $table->string('cep', 10)->nullable();
            $table->string('rua')->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();

            // Configurações profissionais
            $table->string('cro', 20)->nullable();
            $table->integer('intervalo')->nullable()->comment('Intervalo entre atendimentos em minutos');
            $table->decimal('comissao', 5, 2)->nullable()->comment('Percentual de comissão');
            $table->string('chave_pix')->nullable();

            // Status do sistema
            $table->boolean('status')->default(true)->comment('true = ativo, false = inativo');

            $table->timestamps();

            // Índices para consultas frequentes
            $table->index(['status', 'name']);
            $table->index('cargo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funcionarios');
    }
};
