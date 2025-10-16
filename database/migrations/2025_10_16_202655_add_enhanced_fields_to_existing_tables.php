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
        // Adicionar campos ao schedulings
        Schema::table('schedulings', function (Blueprint $table) {
            $table->string('status')->default('scheduled')->after('obs');
            $table->datetime('scheduled_at')->nullable()->after('status');
            $table->integer('duration')->nullable()->after('scheduled_at'); // em minutos
            $table->datetime('confirmed_at')->nullable()->after('duration');
            $table->datetime('canceled_at')->nullable()->after('confirmed_at');
            $table->text('cancellation_reason')->nullable()->after('canceled_at');
        });

        // Adicionar campos ao procedures
        Schema::table('procedures', function (Blueprint $table) {
            $table->text('description')->nullable()->after('preparation');
            $table->string('category')->nullable()->after('description');
            $table->boolean('active')->default(true)->after('category');
            $table->boolean('requires_anesthesia')->default(false)->after('active');
            $table->integer('complexity_level')->default(1)->after('requires_anesthesia'); // 1-4
        });

        // Adicionar campos ao employees
        Schema::table('employees', function (Blueprint $table) {
            $table->string('cro')->nullable()->after('photo');
            $table->string('specialty')->nullable()->after('cro');
            $table->boolean('active')->default(true)->after('specialty');
            $table->date('hire_date')->nullable()->after('active');
            $table->date('birth_date')->nullable()->after('hire_date');
            $table->text('address')->nullable()->after('birth_date');
            $table->decimal('salary', 10, 2)->nullable()->after('address');
            $table->decimal('commission_rate', 5, 2)->nullable()->after('salary');
            $table->foreignId('user_id')->nullable()->constrained()->after('commission_rate');
        });

        // Criar tabela patient_procedures (pivot)
        Schema::create('patient_procedures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('procedure_id')->constrained('procedures')->onDelete('cascade');
            $table->foreignId('scheduling_id')->nullable()->constrained('schedulings')->onDelete('set null');
            $table->date('date');
            $table->string('status')->default('scheduled'); // scheduled, completed, canceled
            $table->decimal('value', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['paciente_id', 'procedure_id']);
        });

        // Adicionar campos de auditoria a algumas tabelas importantes
        Schema::table('pacientes', function (Blueprint $table) {
            $table->json('audit_trail')->nullable()->after('observacoes');
            $table->string('data_source')->default('manual')->after('audit_trail'); // manual, import, api
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedulings', function (Blueprint $table) {
            $table->dropColumn([
                'status', 'scheduled_at', 'duration', 'confirmed_at', 
                'canceled_at', 'cancellation_reason'
            ]);
        });

        Schema::table('procedures', function (Blueprint $table) {
            $table->dropColumn([
                'description', 'category', 'active', 'requires_anesthesia', 'complexity_level'
            ]);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'cro', 'specialty', 'active', 'hire_date', 'birth_date', 
                'address', 'salary', 'commission_rate', 'user_id'
            ]);
        });

        Schema::dropIfExists('patient_procedures');

        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropColumn(['audit_trail', 'data_source']);
        });
    }
};
