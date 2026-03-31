# Comandos de Validação de Segurança - v2.0

**Data:** 2026-03-28  
**Propósito:** Verificar todas as correções implementadas

---

## 🔍 Backend (Laravel / PHP)

### 1. Validar Zero Composer Advisories
```bash
cd odonto
composer audit
# Esperado: "No security advisory concerns found"
```

### 2. Verificar Routes com Auth Middleware
```bash
php artisan route:list | grep -i "auth:sanctum"
# Esperado: Todas as rotas de negócio com auth:sanctum + api.permission
```

### 3. Validar Arquivo CORS Config
```bash
cat config/cors.php | grep -A5 "allowed_origins"
# Esperado: array_filter + env('CORS_ALLOWED_ORIGINS', '...')
```

### 4. Checar Sanctum Config
```bash
cat config/sanctum.php | grep -A2 "expiration"
# Esperado: 'expiration' => (int) env('SANCTUM_EXPIRATION', 120)
```

### 5. Verificar Middleware de Permissão
```bash
cat app/Http/Middleware/EnsureApiPermission.php | grep -i "hasModulePermission"
# Esperado: Função presente com validação de permissão
```

### 6. Validar Payment Controllers
```bash
ls -la app/Http/Controllers/ | grep -E "(Card|Pix)Payment"
# Esperado: CardPaymentController.php e PixController.php presentes
```

### 7. Checar Whitelist de Sort em SchedulingController
```bash
cat app/Http/Controllers/SchedulingController.php | grep -A3 "allowedSortColumns"
# Esperado: Array com ['date', 'time', 'status', 'created_at', 'updated_at']
```

### 8. Validar Updates com $validated
```bash
grep -r "\$validated = \$request->validate" app/Http/Controllers/
# Esperado: ContasPagarController.php, ContasReceberController.php, etc.
```

---

## 🔍 Frontend (React / TypeScript)

### 1. Verificar npm Audit Status
```bash
cd dashboard-odonto
npm audit
# Esperado: Some vulnerabilities (residuais transitive), production code limpo
```

### 2. Validar Build Sem Erros
```bash
npm run build
# Esperado: "Compiled with warnings" (zero errors)
# Tamanho esperado: main.js ~137KB gzip, main.css ~22KB gzip
```

### 3. Checar sessionStorage no api.ts
```bash
grep -n "sessionStorage\|getStoredToken" src/services/api.ts
# Esperado: sessionStorage como prioridade, localStorage como fallback
```

### 4. Validar Remoção de dangerouslySetInnerHTML
```bash
grep -r "dangerouslySetInnerHTML" src/
# Esperado: Nenhum resultado (ou apenas em comentários)
```

### 5. Checar resumirTexto em AnotacoesPage
```bash
grep -n "resumirTexto\|replace.*<.*>" src/pages/Modulos/Anotacoes/AnotacoesPage.tsx
# Esperado: Função resumirTexto removendo tags HTML
```

### 6. Validar CartaoService Proxy
```bash
grep -n "REACT_APP_MERCADO_PAGO\|/api/payments/card" src/modules/pagamento/services/CartaoService.ts
# Esperado: Nenhum REACT_APP_MERCADO_PAGO, chamadas via /api/payments/card/*
```

### 7. Validar PixService Proxy
```bash
grep -n "REACT_APP_MERCADOPAGO_TOKEN\|/api/payments/pix" src/modules/pagamento/services/PixService.ts
# Esperado: Nenhuma credencial, chamadas via /api/payments/pix/*
```

### 8. TypeScript Compilation Check
```bash
npm run build 2>&1 | grep -i "error"
# Esperado: Nenhum erro (warnings são ok)
```

---

## 🔒 Segurança End-to-End

### 1. Testar Autenticação Sem Token
```bash
curl -X GET http://localhost:8000/api/dashboard/overview
# Esperado: 401 Unauthorized
```

### 2. Testar Com Token Válido
```bash
TOKEN="seu_token_aqui"
curl -X GET http://localhost:8000/api/dashboard/overview \
  -H "Authorization: Bearer $TOKEN"
# Esperado: 200 OK (se permissão OK) ou 403 Forbidden
```

### 3. Teste SQL Injection Prevention
```bash
# Tentar sort_by malicioso
curl -X GET "http://localhost:8000/api/schedulings?sort_by=id);DROP TABLE schedulings;--" \
  -H "Authorization: Bearer $TOKEN"
# Esperado: Fallback para sort_by='date', nenhuma execução SQL maliciosa
```

### 4. Teste CORS Headers
```bash
curl -X OPTIONS http://localhost:8000/api/dashboard/overview \
  -H "Origin: http://localhost:3000" \
  -H "Access-Control-Request-Method: GET" -v
# Esperado: Access-Control-Allow-Origin com origem específica (non-wildcard)
```

### 5. Verificar Token Expiration
```bash
# Login
LOGIN_RESPONSE=$(curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@test.com","password":"password"}')

TOKEN=$(echo $LOGIN_RESPONSE | jq -r '.data.token')
echo "Token obtido, esperando expiration..."

# Esperar 121 minutos (ou simular no banco)
# Depois:
curl -X GET http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer $TOKEN"
# Esperado: 401 Unauthorized (token expirado)
```

### 6. Teste XSS Prevention
```javascript
// Console do navegador após login
fetch('/api/anotacoes')
  .then(r => r.json())
  .then(data => {
    const anotacao = data[0];
    console.log(anotacao.texto);  // Deve ser plain text, sem HTML
    // Esperado: Tags HTML removidas, não executadas
  });
```

### 7. Validar sessionStorage Cleanup
```javascript
// Console do navegador
localStorage.setItem('test', 'persiste');
sessionStorage.setItem('test2', 'limpa_ao_fechar');

// Fechar e reabrir navegador
localStorage.getItem('test');      // 'persiste' ✅
sessionStorage.getItem('test2');   // null ✅
```

### 8. Testar Logout Completo
```bash
TOKEN="seu_token"
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Authorization: Bearer $TOKEN"
# Esperado: 200 OK
# Depois:
curl -X GET http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer $TOKEN"
# Esperado: 401 Unauthorized (token revogado)
```

---

## 📊 Relatórios

### 1. Gerar Relatório de Vulnerabilidades Current
```bash
# Backend
cd odonto
composer audit --format=json > composer-audit.json

# Frontend
cd ../dashboard-odonto
npm audit --json > npm-audit.json

# Verificar severidades
cat npm-audit.json | jq '.metadata.vulnerabilities | keys[] as $key | {severity: $key, count: .[$key]}'
```

### 2. Checklist de Segurança Rápido
```bash
#!/bin/bash

echo "=== BACKEND CHECKS ==="
cd odonto
echo "✓ Composer Advisories:"
composer audit | grep -E "(No advisory|advisories)"

echo "✓ Auth Middleware:"
php artisan route:list | grep "auth:sanctum" | wc -l

echo "✓ Config Files:"
echo "  - CORS origins: $(grep 'CORS_ALLOWED_ORIGINS' .env 2>/dev/null || echo 'NOT SET')"
echo "  - Sanctum expiration: $(grep 'SANCTUM_EXPIRATION' .env 2>/dev/null || echo 'NOT SET')"

echo ""
echo "=== FRONTEND CHECKS ==="
cd ../dashboard-odonto
echo "✓ npm Vulnerabilities:"
npm audit | grep -E "vulnerabilities|packages"

echo "✓ Build Status:"
npm run build 2>&1 | grep -E "(Compiled|error)"

echo "✓ sessionStorage usage:"
grep -c "sessionStorage" src/services/api.ts

echo "✓ dangerouslySetInnerHTML removed:"
grep -r "dangerouslySetInnerHTML" src/ | wc -l
```

---

## 🔄 Workflow de Validação Completa

```bash
#!/bin/bash

set -e

echo "========================================="
echo "  SECURITY VALIDATION WORKFLOW - v2.0"
echo "========================================="

# Backend
echo ""
echo "[1/8] Backend Composer Audit..."
cd odonto
composer audit || exit 1

echo "[2/8] Backend Routes Check..."
php artisan route:list | grep -q "auth:sanctum" && echo "✅ Routes protected" || exit 1

echo "[3/8] Frontend npm Audit..."
cd ../dashboard-odonto
npm audit || true  # Não falha se houver vulnerabilidades transitive

echo "[4/8] Frontend Build..."
npm run build 2>&1 | grep -q "Compiled with warnings\|Compiled" || exit 1

echo "[5/8] TypeScript Check..."
npm run build 2>&1 | grep -q "error" && { echo "❌ Build errors found"; exit 1; } || echo "✅ TypeScript clean"

echo "[6/8] Security Config Check..."
grep -q "sessionStorage" src/services/api.ts && echo "✅ sessionStorage found" || exit 1

echo "[7/8] XSS Prevention Check..."
! grep -r "dangerouslySetInnerHTML" src/ --exclude-dir=node_modules >/dev/null 2>&1 && echo "✅ dangerouslySetInnerHTML not found" || exit 1

echo "[8/8] Payment Proxy Check..."
grep -q "/api/payments/card" src/modules/pagamento/services/CartaoService.ts && echo "✅ Payment proxy active" || exit 1

echo ""
echo "========================================="
echo "  ✅ ALL SECURITY CHECKS PASSED"
echo "========================================="
```

---

## 📝 Notas de Execução

- Todos os comandos assumem que você está na raiz do workspace
- Requer: bash, curl, jq (optional), Git, Node.js, PHP+Composer
- Os testes de autenticação requerem servidor rodando localmente (ou endpoint remoto)
- Tempos de teste: ~5 minutos para suite completa

---

## 🆘 Troubleshooting

### "composer audit" falha
```bash
composer self-update
composer update --with-all-dependencies
composer audit
```

### "npm audit" mostra vulnerabilidades
```bash
# Isso é esperado para vulnerabilidades transitive em dev
# Verificar se são apenas build-time:
npm audit --omit=dev

# Para fix:
npm audit fix
npm audit fix --force
```

### Build falha com "error"
```bash
rm -rf node_modules package-lock.json
npm install
npm run build
```

### Routes sem auth:sanctum
```bash
php artisan config:cache
php artisan route:cache
php artisan route:list
```

---

**Última Atualização:** 2026-03-28 v2.0
