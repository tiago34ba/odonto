<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('helpdesk_areas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('codigo')->unique();
            $table->text('descricao')->nullable();
            $table->string('gestor_nome')->nullable();
            $table->string('gestor_email')->nullable();
            $table->integer('sla_padrao_horas')->default(24);
            $table->json('escalation_rules')->nullable();
            $table->string('status')->default('ativo');
            $table->timestamps();
        });

        Schema::create('helpdesk_priorities', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('codigo')->unique();
            $table->string('cor', 20)->default('#2563eb');
            $table->integer('ordem')->default(1);
            $table->integer('tempo_resposta_horas')->default(4);
            $table->integer('tempo_resolucao_horas')->default(24);
            $table->text('descricao')->nullable();
            $table->string('status')->default('ativo');
            $table->timestamps();
        });

        Schema::create('helpdesk_problem_types', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('codigo')->unique();
            $table->string('categoria')->nullable();
            $table->text('descricao')->nullable();
            $table->string('status')->default('ativo');
            $table->timestamps();
        });

        Schema::create('helpdesk_classes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('codigo')->unique();
            $table->text('descricao')->nullable();
            $table->json('regra_fila')->nullable();
            $table->string('status')->default('ativo');
            $table->timestamps();
        });

        Schema::create('helpdesk_users', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('codigo')->unique();
            $table->enum('tipo', ['tecnico', 'cliente', 'gestor'])->default('cliente');
            $table->string('email')->unique();
            $table->string('telefone')->nullable();
            $table->string('empresa')->nullable();
            $table->string('departamento')->nullable();
            $table->string('cargo')->nullable();
            $table->string('status')->default('ativo');
            $table->string('photo_url')->nullable();
            $table->string('senha_temporaria')->nullable();
            $table->timestamp('senha_enviada_em')->nullable();
            $table->boolean('password_reset_required')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('helpdesk_problems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->nullable()->constrained('helpdesk_areas')->nullOnDelete();
            $table->foreignId('problem_type_id')->nullable()->constrained('helpdesk_problem_types')->nullOnDelete();
            $table->string('nome');
            $table->string('codigo')->unique();
            $table->text('descricao')->nullable();
            $table->text('sintomas')->nullable();
            $table->string('impacto')->nullable();
            $table->text('workaround')->nullable();
            $table->string('status')->default('ativo');
            $table->timestamps();
        });

        Schema::create('helpdesk_solutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('problem_id')->nullable()->constrained('helpdesk_problems')->nullOnDelete();
            $table->string('nome');
            $table->string('codigo')->unique();
            $table->text('descricao')->nullable();
            $table->longText('passos')->nullable();
            $table->string('kb_article_url')->nullable();
            $table->integer('tempo_medio_min')->default(0);
            $table->decimal('efetividade_score', 5, 2)->default(0);
            $table->string('status')->default('ativo');
            $table->timestamps();
        });

        Schema::create('helpdesk_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->foreignId('cliente_id')->nullable()->constrained('helpdesk_users')->nullOnDelete();
            $table->foreignId('tecnico_id')->nullable()->constrained('helpdesk_users')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('helpdesk_areas')->nullOnDelete();
            $table->foreignId('priority_id')->nullable()->constrained('helpdesk_priorities')->nullOnDelete();
            $table->foreignId('problem_id')->nullable()->constrained('helpdesk_problems')->nullOnDelete();
            $table->foreignId('problem_type_id')->nullable()->constrained('helpdesk_problem_types')->nullOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('helpdesk_classes')->nullOnDelete();
            $table->string('codigo')->unique();
            $table->string('assunto');
            $table->longText('descricao')->nullable();
            $table->string('status')->default('aberto');
            $table->string('canal_origem')->default('portal');
            $table->timestamp('aberto_em')->nullable();
            $table->timestamp('prazo_em')->nullable();
            $table->timestamp('fechado_em')->nullable();
            $table->json('anexos')->nullable();
            $table->string('gestor_email')->nullable();
            $table->boolean('confirmado_cliente')->default(false);
            $table->boolean('confirmado_alteracao')->default(false);
            $table->unsignedBigInteger('copiado_de_ticket_id')->nullable();
            $table->json('export_metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('helpdesk_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('helpdesk_tickets')->cascadeOnDelete();
            $table->foreignId('tecnico_id')->nullable()->constrained('helpdesk_users')->nullOnDelete();
            $table->foreignId('solution_id')->nullable()->constrained('helpdesk_solutions')->nullOnDelete();
            $table->string('nome');
            $table->string('codigo')->unique();
            $table->string('tipo_atendimento')->default('analise');
            $table->text('descricao')->nullable();
            $table->string('status')->default('registrado');
            $table->timestamp('iniciado_em')->nullable();
            $table->timestamp('finalizado_em')->nullable();
            $table->decimal('horas_trabalhadas', 8, 2)->default(0);
            $table->decimal('custo', 12, 2)->default(0);
            $table->json('anexos')->nullable();
            $table->timestamps();
        });

        Schema::create('helpdesk_pre_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->nullable()->constrained('helpdesk_areas')->nullOnDelete();
            $table->string('nome');
            $table->string('codigo')->unique();
            $table->enum('tipo', ['tecnico', 'cliente'])->default('cliente');
            $table->string('email')->unique();
            $table->string('telefone')->nullable();
            $table->string('empresa')->nullable();
            $table->string('status')->default('pendente');
            $table->string('senha_temporaria')->nullable();
            $table->timestamp('senha_enviada_em')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });

        Schema::create('helpdesk_audit_trails', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('acao');
            $table->string('codigo')->unique();
            $table->string('nome');
            $table->string('actor_nome')->nullable();
            $table->string('actor_email')->nullable();
            $table->string('gestor_email')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('payload_before')->nullable();
            $table->json('payload_after')->nullable();
            $table->text('descricao')->nullable();
            $table->string('status')->default('registrado');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('helpdesk_audit_trails');
        Schema::dropIfExists('helpdesk_pre_registrations');
        Schema::dropIfExists('helpdesk_attendances');
        Schema::dropIfExists('helpdesk_tickets');
        Schema::dropIfExists('helpdesk_solutions');
        Schema::dropIfExists('helpdesk_problems');
        Schema::dropIfExists('helpdesk_users');
        Schema::dropIfExists('helpdesk_classes');
        Schema::dropIfExists('helpdesk_problem_types');
        Schema::dropIfExists('helpdesk_priorities');
        Schema::dropIfExists('helpdesk_areas');
    }
};
