<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anamneses', function (Blueprint $table) {
            $table->string('codigo')->nullable()->after('id');
            $table->string('tipo_resposta')->default('Sim/Não')->after('description');
            $table->boolean('obrigatorio')->default(false)->after('tipo_resposta');
            $table->json('opcoes_resposta')->nullable()->after('obrigatorio');
            $table->boolean('ativo')->default(true)->after('opcoes_resposta');
        });
    }

    public function down(): void
    {
        Schema::table('anamneses', function (Blueprint $table) {
            $table->dropColumn(['codigo', 'tipo_resposta', 'obrigatorio', 'opcoes_resposta', 'ativo']);
        });
    }
};
