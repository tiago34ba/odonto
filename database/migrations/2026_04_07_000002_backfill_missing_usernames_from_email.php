<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'username')) {
            return;
        }

        DB::table('users')
            ->select('id', 'email')
            ->whereNull('username')
            ->whereNotNull('email')
            ->orderBy('id')
            ->get()
            ->each(function ($user): void {
                $baseUsername = Str::lower(trim(Str::before((string) $user->email, '@')));

                if ($baseUsername === '') {
                    return;
                }

                $username = $baseUsername;
                $suffix = 2;

                while (DB::table('users')
                    ->where('username', $username)
                    ->where('id', '!=', $user->id)
                    ->exists()) {
                    $username = $baseUsername . '.' . $suffix;
                    $suffix++;
                }

                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'username' => $username,
                        'updated_at' => now(),
                    ]);
            });
    }

    public function down(): void
    {
        // Backfill irreversivel: manter usernames evita regressao de login por identificador curto.
    }
};
