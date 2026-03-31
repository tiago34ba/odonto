<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treatment_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('treatment_plans', 'numero')) {
                $table->string('numero')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('treatment_plans', 'paciente_id')) {
                $table->unsignedBigInteger('paciente_id')->nullable()->after('numero');
                $table->index('paciente_id');
            }
            if (!Schema::hasColumn('treatment_plans', 'tipo')) {
                $table->string('tipo')->nullable()->after('paciente_id');
            }
            if (!Schema::hasColumn('treatment_plans', 'valor')) {
                $table->decimal('valor', 10, 2)->default(0)->after('tipo');
            }
            if (!Schema::hasColumn('treatment_plans', 'status')) {
                $table->enum('status', ['Concluído', 'Pendente', 'Aprovado', 'Rejeitado'])->default('Pendente')->after('valor');
                $table->index('status');
            }
            if (!Schema::hasColumn('treatment_plans', 'data')) {
                $table->date('data')->nullable()->after('status');
            }
            if (!Schema::hasColumn('treatment_plans', 'dias_validade')) {
                $table->integer('dias_validade')->default(30)->after('data');
            }
            if (!Schema::hasColumn('treatment_plans', 'procedimentos')) {
                $table->json('procedimentos')->nullable()->after('dias_validade');
            }
            if (!Schema::hasColumn('treatment_plans', 'desconto')) {
                $table->decimal('desconto', 10, 2)->default(0)->after('procedimentos');
            }
            if (!Schema::hasColumn('treatment_plans', 'desconto_tipo')) {
                $table->string('desconto_tipo', 5)->default('%')->after('desconto');
            }
            if (!Schema::hasColumn('treatment_plans', 'forma_pagamento')) {
                $table->string('forma_pagamento')->nullable()->after('desconto_tipo');
            }
            if (!Schema::hasColumn('treatment_plans', 'profissional')) {
                $table->string('profissional')->nullable()->after('forma_pagamento');
            }
            if (!Schema::hasColumn('treatment_plans', 'odontograma')) {
                $table->string('odontograma')->nullable()->after('profissional');
            }
            if (!Schema::hasColumn('treatment_plans', 'observacoes')) {
                $table->text('observacoes')->nullable()->after('odontograma');
            }
        });
    }

    public function down(): void
    {
        Schema::table('treatment_plans', function (Blueprint $table) {
            $columns = [
                'numero',
                'paciente_id',
                'tipo',
                'valor',
                'status',
                'data',
                'dias_validade',
                'procedimentos',
                'desconto',
                'desconto_tipo',
                'forma_pagamento',
                'profissional',
                'odontograma',
                'observacoes',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('treatment_plans', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
