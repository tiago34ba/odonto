<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forma_pagamentos', function (Blueprint $table) {
            $table->string('codigo')->nullable()->after('id');
            $table->string('tipo')->nullable()->after('nome');
            $table->string('cor', 7)->nullable()->default('#3498db')->after('tipo');
            $table->string('icone')->nullable()->after('cor');
            $table->decimal('taxa_juros', 5, 2)->default(0)->after('taxa');
            $table->integer('parcelas_max')->default(1)->after('taxa_juros');
            $table->integer('dias_vencimento')->default(0)->after('parcelas_max');
            $table->boolean('ativo')->default(true)->after('dias_vencimento');
            $table->boolean('aceita_parcelamento')->default(false)->after('ativo');
        });
    }

    public function down(): void
    {
        Schema::table('forma_pagamentos', function (Blueprint $table) {
            $table->dropColumn([
                'codigo', 'tipo', 'cor', 'icone', 'taxa_juros',
                'parcelas_max', 'dias_vencimento', 'ativo', 'aceita_parcelamento',
            ]);
        });
    }
};
