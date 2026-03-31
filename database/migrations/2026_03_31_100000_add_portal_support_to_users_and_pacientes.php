<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adicionar campo tipo ao users para distinguir staff x paciente
        if (! Schema::hasColumn('users', 'tipo')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('tipo', 20)->default('staff')->after('ativo');
            });
        }

        // Vincular paciente a um usuário do portal
        if (! Schema::hasColumn('pacientes', 'user_id')) {
            Schema::table('pacientes', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pacientes', 'user_id')) {
            Schema::table('pacientes', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }

        if (Schema::hasColumn('users', 'tipo')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('tipo');
            });
        }
    }
};
