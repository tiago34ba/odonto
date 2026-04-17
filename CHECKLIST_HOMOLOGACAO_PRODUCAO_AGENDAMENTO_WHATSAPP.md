# Checklist de Homologacao e Producao - Agendamento + WhatsApp

## Escopo entregue

- Confirmacoes pendentes de agendamento com rotina automatica recorrente.
- Lembretes em multiplas janelas (24h, 3h e 1h) com deduplicacao por janela.
- Telemetria persistente de disparos WhatsApp em tabela dedicada.
- Monitoramento de taxa de falha com alerta operacional.
- Hardening do fluxo administrativo de agendamento (validacao de slot real, concorrencia e duplicidade).

## 1) Pre-requisitos de ambiente

- Variaveis de WhatsApp configuradas no backend:
  - `WHATSAPP_ENABLED`
  - `WHATSAPP_PROVIDER`
  - `WHATSAPP_WEBHOOK_URL` ou `WHATSAPP_BASE_URL` + `WHATSAPP_INSTANCE`
  - `WHATSAPP_TOKEN`
  - `WHATSAPP_TIMEOUT`
  - `WHATSAPP_ALERT_EMAIL` (opcional)
- Scheduler do Laravel ativo no servidor.
- Permissao de escrita no banco e logs.

## 2) Migracoes

Executar no backend:

```bash
php artisan migrate
```

Validar criacao das estruturas:

- Coluna `schedulings.reminder_windows_sent_at`
- Tabela `whatsapp_dispatch_logs`

## 3) Validacao funcional em homologacao

### 3.1 Portal - confirmacao pendente

1. Criar agendamento via portal.
2. Executar manualmente:

```bash
php artisan portal:send-appointment-confirmations --hours=72 --chunk=100
```

3. Confirmar:
- envio WhatsApp realizado;
- `schedulings.whatsapp_notification_sent_at` preenchido;
- registro em `whatsapp_dispatch_logs` com status `sent`.

### 3.2 Portal - lembretes multi-janela

1. Criar agendamento futuro para cobrir janela configurada.
2. Executar manualmente:

```bash
php artisan portal:send-appointment-reminders --windows=1440,180,60 --tolerance=10 --chunk=200
```

3. Confirmar:
- envio na janela correta;
- sem duplicacao na mesma janela;
- `schedulings.reminder_windows_sent_at` atualizado;
- `schedulings.reminder_sent_at` atualizado.

### 3.3 Fluxo administrativo

1. Tentar criar agendamento fora da janela do dentista.
2. Esperado: erro `422` com mensagem de horario fora da agenda.
3. Tentar concorrencia simulada para o mesmo horario.
4. Esperado: um sucesso e um bloqueio com mensagem amigavel de slot reservado.

## 4) Monitoramento e alertas

Executar manualmente:

```bash
php artisan portal:monitor-whatsapp-delivery --minutes=30 --min-attempts=10 --max-failure-rate=0.30
```

Validar:

- saida com metricas de tentativa/sucesso/falha;
- `Log::warning` quando taxa de falha ultrapassar limite;
- envio de email de alerta quando `WHATSAPP_ALERT_EMAIL` estiver preenchido.

## 5) Scheduler em producao

Rotinas esperadas no `routes/console.php`:

- `portal:send-appointment-confirmations --hours=72 --chunk=100` a cada 10 minutos
- `portal:send-appointment-reminders --windows=1440,180,60 --tolerance=10 --chunk=200` a cada 10 minutos
- `portal:monitor-whatsapp-delivery --minutes=30 --min-attempts=10 --max-failure-rate=0.30` a cada 10 minutos

Garantir `withoutOverlapping()` ativo para evitar concorrencia de jobs.

## 6) Testes automatizados recomendados

Executar:

```bash
php artisan test tests/Feature/Api/PortalPacienteFlowTest.php
php artisan test tests/Feature/Api/SchedulingApiTest.php
```

## 7) Go-live

- Aplicar migracoes em janela controlada.
- Habilitar scheduler e observar 1 ciclo completo.
- Monitorar logs por 24h.
- Validar taxa de falha real e ajustar limites do monitoramento se necessario.
