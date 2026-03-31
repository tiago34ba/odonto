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
        Schema::table('pacientes', function (Blueprint $table) {
            // Adicionar coluna cidade se não existir
            if (!Schema::hasColumn('pacientes', 'cidade')) {
                $table->string('cidade')->nullable()->after('bairro');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            if (Schema::hasColumn('pacientes', 'cidade')) {
                $table->dropColumn('cidade');
            }
        });
    }
};
