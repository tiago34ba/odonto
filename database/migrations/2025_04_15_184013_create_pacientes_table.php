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
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('convenio')->nullable();
            $table->string('telefone')->nullable();
            $table->integer('idade')->nullable();
            $table->date('data_nascimento');
            $table->string('responsavel')->nullable(); // Nome do responsável
            $table->string('cpf_responsavel')->nullable();
            $table->string('celular')->nullable();
            $table->string('estado', 2)->nullable(); // Sigla do estado, ex: SP
            $table->string('sexo', 20)->nullable(); // Ex: Masculino, Feminino, Outro
            $table->string('profissao')->nullable();
            $table->string('estado_civil')->nullable();
            $table->string('tipo_sanguineo', 10)->nullable(); // Ex: A+, O-
            $table->string('pessoa', 20)->nullable(); // Ex: Física, Jurídica
            $table->string('cpf_cnpj')->nullable()->unique(); // CPF ou CNPJ, único se preenchido
            $table->string('email')->nullable()->unique(); // Email, único se preenchido
            $table->string('cep', 20)->nullable(); // Aumente para 20 caracteres
            $table->string('rua')->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
