<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chamados_categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 180);
            $table->string('codigo', 120)->unique();
            $table->string('cor', 30)->default('#2563eb');
            $table->string('icone', 60)->nullable();
            $table->text('descricao')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('sla_horas')->default(24);
            $table->string('equipe_responsavel', 180)->nullable();
            $table->boolean('auto_assign')->default(false);
            $table->boolean('notificar_gestor')->default(true);
            $table->string('prioridade_padrao', 30)->default('media');
            $table->json('emails_notificacao')->nullable();
            $table->string('status', 20)->default('ativo');
            $table->timestamps();
        });

        Schema::create('chamados_campos_personalizados', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 180);
            $table->string('codigo', 120)->unique();
            $table->string('label', 180);
            $table->string('tipo', 40)->default('text');
            $table->string('placeholder', 255)->nullable();
            $table->boolean('obrigatorio')->default(false);
            $table->boolean('visivel_cliente')->default(true);
            $table->boolean('visivel_tecnico')->default(true);
            $table->integer('posicao')->default(0);
            $table->json('opcoes')->nullable();
            $table->string('validacao_regex', 255)->nullable();
            $table->string('valor_padrao', 500)->nullable();
            $table->string('aplicado_em', 60)->default('chamados');
            $table->string('grupo', 120)->nullable();
            $table->string('status', 20)->default('ativo');
            $table->timestamps();
        });

        Schema::create('chamados_respostas_predefinidas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 180);
            $table->string('codigo', 120)->unique();
            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->longText('conteudo');
            $table->json('tags')->nullable();
            $table->integer('uso_count')->default(0);
            $table->timestamp('ultima_ativacao_at')->nullable();
            $table->string('visivel_para', 40)->default('todos');
            $table->string('atalho_teclado', 60)->nullable();
            $table->string('idioma', 10)->default('pt_BR');
            $table->boolean('incluir_anexo')->default(false);
            $table->string('status', 20)->default('ativo');
            $table->timestamps();
        });

        Schema::create('chamados_status_personalizados', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 180);
            $table->string('codigo', 120)->unique();
            $table->string('cor', 30)->default('#6b7280');
            $table->string('icone', 60)->nullable();
            $table->string('tipo', 40)->default('em_andamento');
            $table->integer('ordem')->default(0);
            $table->boolean('permite_reabertura')->default(false);
            $table->boolean('envia_notificacao')->default(true);
            $table->boolean('exige_resolucao')->default(false);
            $table->boolean('sla_pausado')->default(false);
            $table->boolean('publico')->default(true);
            $table->string('proximo_status_sugerido', 120)->nullable();
            $table->json('acoes_automaticas')->nullable();
            $table->string('status', 20)->default('ativo');
            $table->timestamps();
        });

        Schema::create('chamados_visualizacoes', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 180);
            $table->string('codigo', 120)->unique();
            $table->string('tipo', 30)->default('pessoal');
            $table->json('filtros')->nullable();
            $table->json('colunas')->nullable();
            $table->json('ordenacao')->nullable();
            $table->string('agrupamento', 60)->nullable();
            $table->boolean('compartilhado')->default(false);
            $table->string('usuario_criador', 180)->nullable();
            $table->string('equipe_id', 60)->nullable();
            $table->integer('uso_count')->default(0);
            $table->string('icone', 60)->nullable();
            $table->string('status', 20)->default('ativo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chamados_visualizacoes');
        Schema::dropIfExists('chamados_status_personalizados');
        Schema::dropIfExists('chamados_respostas_predefinidas');
        Schema::dropIfExists('chamados_campos_personalizados');
        Schema::dropIfExists('chamados_categorias');
    }
};
