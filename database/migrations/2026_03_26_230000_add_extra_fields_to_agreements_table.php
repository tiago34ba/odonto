<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->string('codigo')->nullable()->after('id');
            $table->string('tipo')->nullable()->after('name');
            $table->string('cnpj')->nullable()->after('tipo');
            $table->string('email')->nullable()->after('phone');
            $table->string('endereco')->nullable()->after('email');
            $table->string('numero')->nullable()->after('endereco');
            $table->string('complemento')->nullable()->after('numero');
            $table->string('bairro')->nullable()->after('complemento');
            $table->string('cidade')->nullable()->after('bairro');
            $table->string('uf', 2)->nullable()->after('cidade');
            $table->string('cep', 9)->nullable()->after('uf');
            $table->string('contato_responsavel')->nullable()->after('cep');
            $table->decimal('desconto_percentual', 5, 2)->nullable()->after('clinic_commission');
            $table->boolean('ativo')->default(true)->after('desconto_percentual');
            $table->text('observacoes')->nullable()->after('ativo');
        });
    }

    public function down(): void
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->dropColumn([
                'codigo', 'tipo', 'cnpj', 'email', 'endereco', 'numero',
                'complemento', 'bairro', 'cidade', 'uf', 'cep',
                'contato_responsavel', 'desconto_percentual', 'ativo', 'observacoes',
            ]);
        });
    }
};
