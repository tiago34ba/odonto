# Checklist de Validação Pós-Deploy — WhatsApp + Scheduler

**Ambiente:** Produção  
**Duração estimada:** 5–8 minutos  
**Data de aplicação das migrations:** 2026-04-14 (batch 6)

---

## 1. Banco de dados — Schemas aplicados

Execute no servidor:

```bash
php artisan migrate:status | grep -E '2026_04_15'
```

**Esperado:**
```
2026_04_15_000100_add_reminder_windows_to_schedulings_table ... [?] Ran
2026_04_15_000200_create_whatsapp_dispatch_logs_table ......... [?] Ran
```

Verificar colunas existentes:

```bash
php artisan tinker --execute="echo Schema::hasColumn('schedulings','reminder_windows_sent_at') ? 'OK' : 'FALTA';"
php artisan tinker --execute="echo Schema::hasTable('whatsapp_dispatch_logs') ? 'OK' : 'FALTA';"
```

---

## 2. Variáveis de ambiente WhatsApp

Execute no servidor:

```bash
php artisan tinker --execute="
  echo config('services.whatsapp.enabled')   ? '[OK] enabled'  : '[X] disabled'; echo PHP_EOL;
  echo config('services.whatsapp.provider')  ? '[OK] provider' : '[X] provider'; echo PHP_EOL;
  echo config('services.whatsapp.alert_email') ? '[OK] alert_email' : '[X] alert_email FALTA';
"
```

**Esperado:**
```
[OK] enabled
[OK] provider
[OK] alert_email
```

Se `alert_email` faltar, adicione ao `.env` de produção:

```dotenv
WHATSAPP_ALERT_EMAIL=email-da-equipe@seudominio.com.br
```

Para Evolution API adicione também:

```dotenv
WHATSAPP_PROVIDER=evolution
WHATSAPP_BASE_URL=https://sua-instancia.evolution.com.br
WHATSAPP_INSTANCE=nome_da_instancia
WHATSAPP_TOKEN=sua_api_key
```

Depois: `php artisan config:cache`

---

## 3. Scheduler — Cron do sistema operacional

Confirme que o cron está instalado:

```bash
crontab -l | grep artisan
```

**Esperado:**
```
* * * * * php /caminho/do/projeto/artisan schedule:run >> /dev/null 2>&1
```

Se não existir, adicione com `crontab -e`:

```
* * * * * /usr/bin/php /var/www/odonto/artisan schedule:run >> /dev/null 2>&1
```

Verifique que os comandos estão registrados:

```bash
php artisan schedule:list
```

**Esperado (3 linhas):**
```
*/10 * * * *  portal:send-appointment-confirmations --hours=72 --chunk=100
*/10 * * * *  portal:send-appointment-reminders --windows=1440,180,60 --tolerance=10 --chunk=200
*/10 * * * *  portal:monitor-whatsapp-delivery --minutes=30 --min-attempts=10 --max-failure-rate=0.30
```

---

## 4. Disparo manual de smoke test

Simule um ciclo completo sem realmente enviar:

```bash
# Confirmações — modo dry-run (sem alterar DB)
php artisan portal:send-appointment-confirmations --hours=72 --chunk=5

# Lembretes multi-janela
php artisan portal:send-appointment-reminders --windows=1440,180,60 --tolerance=10 --chunk=5

# Monitor WhatsApp (deve retornar: [OK] ou warning se taxa de falha alta)
php artisan portal:monitor-whatsapp-delivery --minutes=60 --min-attempts=1 --max-failure-rate=1.0
```

Verifique o log para confirmar que não há erros críticos:

```bash
tail -n 50 storage/logs/laravel.log | grep -E 'ERROR|WhatsApp|reminder'
```

---

## 5. Verificar telemetria gravando

Após 10 minutos do primeiro ciclo automático:

```bash
php artisan tinker --execute="
  \$total = \App\Models\WhatsappDispatchLog::count();
  \$sent  = \App\Models\WhatsappDispatchLog::where('status','sent')->count();
  \$fail  = \App\Models\WhatsappDispatchLog::where('status','failed')->count();
  echo \"Total: \$total | Enviados: \$sent | Falhas: \$fail\";
"
```

**Saudável:** `Total > 0`, `Falhas` < 30% do total.

---

## 6. Checklist final — assinale antes de liberar o ambiente

- [ ] Migrations `2026_04_15_*` com status `Ran`
- [ ] `WHATSAPP_ENABLED=true` no `.env` de produção
- [ ] `WHATSAPP_ALERT_EMAIL` configurado com e-mail real da equipe
- [ ] Provider correto (`webhook` ou `evolution`) com credenciais válidas
- [ ] Cron `* * * * *` instalado no servidor
- [ ] `php artisan schedule:list` mostra as 3 rotinas de portal
- [ ] Smoke test sem erros `ERROR` no log
- [ ] `whatsapp_dispatch_logs` recebendo registros após primeiro ciclo

---

## Rollback de emergência (se necessário)

```bash
# Desfazer as 2 migrations do pacote WhatsApp
php artisan migrate:rollback --step=1
# confirma que remove apenas o batch 6 (os 2 arquivos 2026_04_15_*)
```

> As demais migrations (batch 1–5) não são afetadas.
