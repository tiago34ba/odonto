<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->date('data_nascimento')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            // Reverte apenas se não houver NULLs — seguro para rollback local
            $table->date('data_nascimento')->nullable(false)->change();
        });
    }
};
