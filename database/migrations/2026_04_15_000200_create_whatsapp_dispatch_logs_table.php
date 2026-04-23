<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_dispatch_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduling_id')->nullable()->constrained('schedulings')->nullOnDelete();
            $table->string('message_type', 60)->nullable();
            $table->string('provider', 40)->nullable();
            $table->string('destination', 32)->nullable();
            $table->string('status', 20); // sent | failed | skipped
            $table->string('reason', 120)->nullable();
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->text('response_body')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('dispatched_at');
            $table->timestamps();

            $table->index(['status', 'dispatched_at']);
            $table->index(['message_type', 'dispatched_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_dispatch_logs');
    }
};
