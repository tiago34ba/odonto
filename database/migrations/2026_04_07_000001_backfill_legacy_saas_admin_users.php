<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        DB::table('users')
            ->where(function ($query) {
                $query->where('email', 'saasadmin@odonto.local')
                    ->orWhere('username', 'saasadmin')
                    ->orWhere('name', 'saasadmin');
            })
            ->update([
                'tipo' => 'saas_admin',
                'username' => 'saasadmin',
                'ativo' => true,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Backfill irreversivel: manter os dados promovidos evita regressao no login administrativo.
    }
};
