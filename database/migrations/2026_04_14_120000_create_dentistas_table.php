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
        Schema::create('dentistas', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('telefone', 20)->nullable();
            $table->string('celular', 20)->nullable();
            $table->string('cpf', 14)->nullable()->unique();

            $table->string('cro', 20)->unique();
            $table->string('cro_uf', 2);
            $table->string('especialidade', 120);
            $table->date('data_nascimento')->nullable();
            $table->date('data_cadastro')->nullable();

            $table->integer('intervalo_consulta')->default(30)->comment('Intervalo entre atendimentos em minutos');
            $table->json('horarios_atendimento')->nullable();

            $table->string('chave_pix')->nullable();
            $table->string('cep', 10)->nullable();
            $table->string('rua')->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();

            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index(['status', 'name']);
            $table->index('especialidade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dentistas');
    }
};
