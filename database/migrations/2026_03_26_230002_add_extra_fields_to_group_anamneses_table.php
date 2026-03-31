<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_anamneses', function (Blueprint $table) {
            $table->string('codigo')->nullable()->after('id');
            $table->string('cor', 7)->nullable()->default('#3498db')->after('descricao');
            $table->string('icone')->nullable()->after('cor');
            $table->integer('ordem')->default(0)->after('icone');
            $table->boolean('ativo')->default(true)->after('ordem');
        });
    }

    public function down(): void
    {
        Schema::table('group_anamneses', function (Blueprint $table) {
            $table->dropColumn(['codigo', 'cor', 'icone', 'ordem', 'ativo']);
        });
    }
};
