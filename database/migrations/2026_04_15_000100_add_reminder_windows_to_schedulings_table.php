<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedulings', function (Blueprint $table) {
            if (! Schema::hasColumn('schedulings', 'reminder_windows_sent_at')) {
                $table->json('reminder_windows_sent_at')->nullable()->after('reminder_sent_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('schedulings', function (Blueprint $table) {
            if (Schema::hasColumn('schedulings', 'reminder_windows_sent_at')) {
                $table->dropColumn('reminder_windows_sent_at');
            }
        });
    }
};
