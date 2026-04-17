# Relatório de Entrega — WhatsApp + Scheduler + Hardening de Agendamento

**Projeto:** Odonto — Backend Laravel  
**Data de entrega:** 2026-04-14  
**Responsável:** GitHub Copilot (Claude Sonnet 4.6)  
**Ambiente validado:** Local (espelho de produção)

---

## Resumo Executivo

Este relatório consolida as implementações realizadas em duas sessões consecutivas cobrindo quatro frentes técnicas e a operacionalização de go-live:

1. Lembretes multi-janela com deduplicação
2. Telemetria persistente e monitoramento de falhas WhatsApp
3. Hardening do fluxo administrativo de agendamento
4. Checklist de homologação e validação pós-deploy

---

## 1. Arquivos Criados

### 1.1 Migrations

| Arquivo | Batch | Status |
|---|---|---|
| `database/migrations/2026_04_15_000100_add_reminder_windows_to_schedulings_table.php` | 6 | ✅ Ran |
| `database/migrations/2026_04_15_000200_create_whatsapp_dispatch_logs_table.php` | 6 | ✅ Ran |

**`add_reminder_windows_to_schedulings_table`**  
Adiciona coluna `reminder_windows_sent_at` (JSON, nullable) à tabela `schedulings`. Armazena o registro de quais janelas de lembrete já foram enviadas para cada agendamento, permitindo deduplicação sem reenvio.

**`create_whatsapp_dispatch_logs_table`**  
Cria a tabela `whatsapp_dispatch_logs` com os campos:
- `scheduling_id` (FK nullable)
- `message_type`, `provider`, `destination`
- `status` — valores: `sent` | `failed` | `skipped`
- `reason`, `response_code`, `response_body`
- `context` (JSON), `dispatched_at`
- Índices operacionais: `(status, dispatched_at)`, `(message_type, dispatched_at)`

### 1.2 Models

**`app/Models/WhatsappDispatchLog.php`** *(novo)*  
Eloquent model para a tabela de telemetria. Campos em `fillable` e cast de `context` (array) e `dispatched_at` (datetime).

### 1.3 Console Commands

**`app/Console/Commands/SendPortalAppointmentReminders.php`** *(alterado)*  
- Assinatura evoluída: `--windows`, `--tolerance`, `--chunk`
- Envio em múltiplas janelas (ex.: 1440 min = 24h, 180 min = 3h, 60 min = 1h)
- Deduplicação por janela via campo `reminder_windows_sent_at`
- Saída com métricas: processados / enviados / falhas / ignorados

**`app/Console/Commands/SendPortalAppointmentConfirmations.php`** *(alterado)*  
- Lógica ajustada para horizonte de horário da consulta (`--hours`) em vez de `created_at`
- Saída inclui contagem de `ignoradas`

**`app/Console/Commands/MonitorWhatsappDelivery.php`** *(novo)*  
- Comando: `portal:monitor-whatsapp-delivery`
- Parâmetros: `--minutes` (janela de análise), `--min-attempts` (mínimo para acionar alerta), `--max-failure-rate` (threshold 0–1)
- Calcula taxa de falha a partir da tabela `whatsapp_dispatch_logs`
- Gera `Log::warning` se threshold excedido
- Envia e-mail de alerta (best-effort) para `WHATSAPP_ALERT_EMAIL`

### 1.4 Service

**`app/Services/WhatsappNotificationService.php`** *(alterado)*  
- Instrumentação de telemetria completa via método `logDispatch()`
- Registro em `whatsapp_dispatch_logs` para todos os caminhos: `sent`, `failed`, `skipped`
- `sendAppointmentReminder()` aceita `?int $windowMinutes`
- Método `buildReminderWindowText()` gera texto contextualizado:
  - 1440 min → "Lembrete da sua consulta de amanha."
  - 180/60 min → "Lembrete da sua consulta em X horas."
  - Outros → "…em X minutos."

### 1.5 Controller

**`app/Http/Controllers/SchedulingController.php`** *(alterado)*  
- Novos imports: `Dentista`, `DB`, `QueryException`
- Método `store` / `update` com:
  - Validação de slot contra a janela real de trabalho do dentista (`horarios_atendimento`)
  - Resolução de duração por procedimento quando ausente no payload
  - Transação DB + tratamento de erro de unicidade concorrente (`QueryException` / código 23000)
- `getAvailableSlots()` evoluído para aceitar `$excludeId` (necessário no update)
- Métodos auxiliares: `resolveDentistaFromEmployee()`, `resolveWorkingWindow()`, `resolveProcedureDuration()`

### 1.6 Scheduler

**`routes/console.php`** *(alterado)*  
```php
Schedule::command('portal:send-appointment-confirmations --hours=72 --chunk=100')
    ->everyTenMinutes()
    ->withoutOverlapping();

Schedule::command('portal:send-appointment-reminders --windows=1440,180,60 --tolerance=10 --chunk=200')
    ->everyTenMinutes()
    ->withoutOverlapping();

Schedule::command('portal:monitor-whatsapp-delivery --minutes=30 --min-attempts=10 --max-failure-rate=0.30')
    ->everyTenMinutes()
    ->withoutOverlapping();
```

### 1.7 Configuração

**`config/services.php`** *(alterado)*  
```php
'whatsapp' => [
    'provider'    => env('WHATSAPP_PROVIDER', 'webhook'),
    'enabled'     => env('WHATSAPP_ENABLED', false),
    'webhook_url' => env('WHATSAPP_WEBHOOK_URL', ''),
    'base_url'    => env('WHATSAPP_BASE_URL', ''),
    'instance'    => env('WHATSAPP_INSTANCE', ''),
    'token'       => env('WHATSAPP_TOKEN', ''),
    'timeout'     => env('WHATSAPP_TIMEOUT', 10),
    'alert_email' => env('WHATSAPP_ALERT_EMAIL', ''),   // ← novo
],
```

### 1.8 Testes

**`tests/Feature/Api/SchedulingApiTest.php`** *(alterado)*  
Novo teste adicionado:
```
test_store_blocks_scheduling_outside_dentist_working_window
```
Confirma que o endpoint administrativo retorna `422` ao tentar agendar fora da janela de trabalho do dentista.

### 1.9 Documentação

| Arquivo | Conteúdo |
|---|---|
| `CHECKLIST_HOMOLOGACAO_PRODUCAO_AGENDAMENTO_WHATSAPP.md` | Guia completo de deploy, homologação e go-live |
| `CHECKLIST_VALIDACAO_POS_DEPLOY.md` | Validação de 5 minutos: banco, env, scheduler, smoke test, telemetria |

---

## 2. Evidências de Validação (Ambiente Local)

### 2.1 Migrations aplicadas

```
2026_04_15_000100_add_reminder_windows_to_schedulings_table ... [6] Ran
2026_04_15_000200_create_whatsapp_dispatch_logs_table ......... [6] Ran
```

### 2.2 Schema verificado via tinker

```
[OK] coluna reminder_windows_sent_at
[OK] tabela whatsapp_dispatch_logs
0 registros em whatsapp_dispatch_logs   ← esperado (nenhum envio real ainda)
```

### 2.3 Variáveis de ambiente

```
[OK] enabled
[OK] provider=webhook
[OK] alert_email=saasadmin@odonto.local
```

### 2.4 Scheduler registrado

```
*/10 * * * *  portal:send-appointment-confirmations --hours=72 --chunk=100
*/10 * * * *  portal:send-appointment-reminders --windows=1440,180,60 --tolerance=10 --chunk=200
*/10 * * * *  portal:monitor-whatsapp-delivery --minutes=30 --min-attempts=10 --max-failure-rate=0.30
```

### 2.5 Testes de regressão (sessão anterior)

```
PortalPacienteFlowTest  ...  8 testes PASS
SchedulingApiTest       ...  13 testes PASS (inclui novo teste de janela administrativa)
TOTAL: 21 passed, 118 assertions
```

### 2.6 Lint / sintaxe PHP

Sem erros em todos os arquivos críticos alterados (verificado via `php -l`).

---

## 3. Variáveis de Ambiente — Guia Rápido para Produção

```dotenv
# Obrigatório
WHATSAPP_ENABLED=true
WHATSAPP_PROVIDER=webhook               # ou: evolution
WHATSAPP_TOKEN=sua_chave_secreta
WHATSAPP_ALERT_EMAIL=equipe@clinica.com.br

# Para provider webhook
WHATSAPP_WEBHOOK_URL=https://seu-endpoint-whatsapp.com/api/send

# Para provider Evolution API
WHATSAPP_BASE_URL=https://evolution.seudominio.com.br
WHATSAPP_INSTANCE=nome_da_instancia

# Opcional (padrão: 10s)
WHATSAPP_TIMEOUT=15
```

Após alterar: `php artisan config:cache`

---

## 4. Cron de Produção

Adicionar uma única entrada ao crontab do servidor:

```
* * * * * /usr/bin/php /var/www/odonto/artisan schedule:run >> /dev/null 2>&1
```

O scheduler do Laravel dispara automaticamente todas as rotinas registradas.

---

## 5. Rollback de Emergência

```bash
# Remove apenas as 2 migrations do batch 6 (WhatsApp)
php artisan migrate:rollback --step=1
```

Não afeta migrations anteriores (batch 1–5).

---

## 6. Pendências Pós-Entrega (ação humana necessária)

| # | Item | Responsável |
|---|------|-------------|
| 1 | Configurar `WHATSAPP_ALERT_EMAIL` com e-mail real da equipe em produção | DevOps / Admin |
| 2 | Configurar credenciais reais do provider WhatsApp (token, URL, instância) | DevOps |
| 3 | Instalar a entrada de cron `* * * * *` no servidor de produção | DevOps |
| 4 | Executar `php artisan migrate --force` no servidor de produção | DevOps |
| 5 | Executar smoke test e validar `whatsapp_dispatch_logs` após primeiro ciclo | QA / Dev |
| 6 | Rotacionar `WHATSAPP_TOKEN` para valor seguro (não usar token de teste) | Segurança |
