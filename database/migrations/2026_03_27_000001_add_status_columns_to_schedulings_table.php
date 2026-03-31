<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedulings', function (Blueprint $table) {
            if (!Schema::hasColumn('schedulings', 'status')) {
                $table->enum('status', [
                    'scheduled', 'confirmed', 'in_progress',
                    'completed', 'canceled', 'no_show',
                ])->default('scheduled')->after('obs');
            }
            if (!Schema::hasColumn('schedulings', 'duration')) {
                $table->integer('duration')->nullable()->after('status');
            }
            if (!Schema::hasColumn('schedulings', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->after('duration');
            }
            if (!Schema::hasColumn('schedulings', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable()->after('scheduled_at');
            }
            if (!Schema::hasColumn('schedulings', 'canceled_at')) {
                $table->timestamp('canceled_at')->nullable()->after('confirmed_at');
            }
            if (!Schema::hasColumn('schedulings', 'cancellation_reason')) {
                $table->string('cancellation_reason', 500)->nullable()->after('canceled_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('schedulings', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn([
                'status',
                'duration',
                'scheduled_at',
                'confirmed_at',
                'canceled_at',
                'cancellation_reason',
            ]);
        });
    }
};
