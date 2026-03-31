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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('name');
            }

            if (!Schema::hasColumn('users', 'nome')) {
                $table->string('nome')->nullable()->after('username');
            }

            if (!Schema::hasColumn('users', 'sobrenome')) {
                $table->string('sobrenome')->nullable()->after('nome');
            }

            if (!Schema::hasColumn('users', 'telefone')) {
                $table->string('telefone', 20)->nullable()->after('email');
            }

            if (!Schema::hasColumn('users', 'clinica')) {
                $table->string('clinica')->nullable()->after('telefone');
            }

            if (!Schema::hasColumn('users', 'especialidade')) {
                $table->string('especialidade')->nullable()->after('clinica');
            }

            if (!Schema::hasColumn('users', 'grupo_acesso_id')) {
                $table->foreignId('grupo_acesso_id')->nullable()->after('especialidade')->constrained('grupo_acessos')->nullOnDelete();
            }

            if (!Schema::hasColumn('users', 'ativo')) {
                $table->boolean('ativo')->default(true)->after('grupo_acesso_id');
            }

            if (!Schema::hasColumn('users', 'ultimo_login_em')) {
                $table->timestamp('ultimo_login_em')->nullable()->after('ativo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'grupo_acesso_id')) {
                $table->dropConstrainedForeignId('grupo_acesso_id');
            }

            $columns = [
                'username',
                'nome',
                'sobrenome',
                'telefone',
                'clinica',
                'especialidade',
                'ativo',
                'ultimo_login_em',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
