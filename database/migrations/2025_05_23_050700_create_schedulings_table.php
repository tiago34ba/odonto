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
        Schema::create('schedulings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('professional_id');
            $table->unsignedBigInteger('procedure_id');
            $table->date('date');
            $table->string('time', 5); // HH:MM
            $table->boolean('return')->default(false);
            $table->text('obs')->nullable();
            $table->timestamps();
            
            $table->index(['date', 'professional_id']);
            $table->index(['patient_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedulings');
    }
};
