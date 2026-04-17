<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedulings', function (Blueprint $table) {
            if (! Schema::hasColumn('schedulings', 'whatsapp_notification_sent_at')) {
                $table->timestamp('whatsapp_notification_sent_at')->nullable()->after('duration');
            }

            if (! Schema::hasColumn('schedulings', 'reminder_sent_at')) {
                $table->timestamp('reminder_sent_at')->nullable()->after('whatsapp_notification_sent_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('schedulings', function (Blueprint $table) {
            if (Schema::hasColumn('schedulings', 'reminder_sent_at')) {
                $table->dropColumn('reminder_sent_at');
            }

            if (Schema::hasColumn('schedulings', 'whatsapp_notification_sent_at')) {
                $table->dropColumn('whatsapp_notification_sent_at');
            }
        });
    }
};
