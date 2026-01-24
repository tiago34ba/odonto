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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // system, patients, appointments, financial, staff
            $table->text('description');
            $table->json('data');
            $table->string('period_start');
            $table->string('period_end');
            $table->string('generated_by')->nullable();
            $table->boolean('is_scheduled')->default(false);
            $table->string('schedule_frequency')->nullable(); // daily, weekly, monthly
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
