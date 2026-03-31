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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('razao_social')->nullable();
            $table->string('email');
            $table->string('phone');
            $table->string('cnpj');
            $table->string('tipo')->nullable();
            $table->string('categoria')->nullable();
            $table->string('contato')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->decimal('avaliacao', 3, 1)->default(0);
            $table->string('pix')->nullable();
            $table->string('pix_key_type')->nullable();
            $table->string('street');
            $table->string('number');
            $table->string('complement')->nullable();
            $table->string('neighborhood');
            $table->string('city');
            $table->string('state');
            $table->string('cep');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
