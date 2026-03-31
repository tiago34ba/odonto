# 📅 Plano de Ação: De 83% para 95% em 8 Semanas

**Data de Início:** 2026-03-28  
**Data de Conclusão:** 2026-05-23  
**Score Target:** 95% de segurança  
**Ganho:** +12%

---

## 🎯 Resumo Executivo

**Objetivo:** Aumentar de 83% para 95% com implementações distribuídas ao longo de 8 semanas

**Estratégia:** 
- Semanas 1-2: Quick wins (security headers + rate limiting) → +2.5%
- Semanas 3-4: Core security (cookies + encryption) → +3%
- Semanas 5-6: Enterprise features (vault + audit) → +2.5%
- Semanas 7-8: Automation (testing + monitoring) → +2%
- Semanas 9+: Fine-tuning → +1.5%

---

## 📊 Timeline Visível

```
Semana 1-2  [████░░░░░░░░░░░░░░] 83% → 85.5%
Semana 3-4  [██████░░░░░░░░░░░░] 85.5% → 88.5%
Semana 5-6  [████████░░░░░░░░░░] 88.5% → 91%
Semana 7-8  [██████████░░░░░░░░] 91% → 93%
Semana 9+   [████████████░░░░░░] 93% → 95%+
```

---

## 📌 SEMANA 1-2: Quick Wins (Security Headers + Rate Limiting)

**Score: 83% → 85.5% (+2.5%)**  
**Esforço:** 40 horas | **Risco:** Baixo | **ROI:** Alto

### ✅ Segunda 1 - Sex 1 (5 dias)

**Seg-Qua (3 dias): Security Headers**
```
Sprint: Implement HSTS + CSP + X-Frame-Options
Tempo: 15 horas
Deliverable: app/Http/Middleware/SecurityHeadersMiddleware.php

Tarefas:
- [ ] 1.1 Criar middleware com todos headers
- [ ] 1.2 Registrar em routes/middleware.php
- [ ] 1.3 Testar headers com curl
- [ ] 1.4 Validar com securityheaders.com
- [ ] 1.5 Deploy em staging
```

**Code da Semana 1:**

```php
// app/Http/Middleware/SecurityHeadersMiddleware.php
<?php namespace App\Http\Middleware;

class SecurityHeadersMiddleware {
    public function handle($request, $next) {
        $response = $next($request);

        // HSTS - 1 ano
        $response->header('Strict-Transport-Security', 
            'max-age=31536000; includeSubDomains; preload');

        // CSP - Bloqueio de recursos não autorizados
        $response->header('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' fonts.googleapis.com",
            "font-src 'self' fonts.gstatic.com",
            "img-src 'self' data: https:",
            "connect-src 'self' api.mercadopago.com",
            "form-action 'self'",
            "frame-ancestors 'none'",
        ]));

        // Prevents MIME sniffing
        $response->header('X-Content-Type-Options', 'nosniff');

        // Clickjacking protection
        $response->header('X-Frame-Options', 'DENY');

        // XSS filter
        $response->header('X-XSS-Protection', '1; mode=block');

        // Referrer control
        $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Features disable
        $response->header('Permissions-Policy', 
            'geolocation=(), microphone=(), camera=(), payment=()');

        return $response;
    }
}
```

**Qua-Sex (2 dias): Rate Limiting Avançado**
```
Sprint: Implement per-endpoint rate limiting
Tempo: 10 horas
Deliverable: app/Services/RateLimitService.php

Tarefas:
- [ ] 2.1 Criar RateLimitService
- [ ] 2.2 Criar AdvancedRateLimiting middleware
- [ ] 2.3 Configure limits por endpoint
- [ ] 2.4 Add X-RateLimit headers
- [ ] 2.5 Test com ApacheBench (ab)
```

**Code da Semana 1 (Rate Limiting):**

```php
// app/Services/RateLimitService.php
<?php namespace App\Services;

use Illuminate\Cache\RateLimiter;

class RateLimitService {
    public function __construct(protected RateLimiter $limiter) {}

    public function checkEndpointLimit($request): bool {
        $key = "ep:{$request->path()}:{$request->ip()}";
        return $this->limiter->attempt($key, 100, 1);  // 100/min
    }

    public function checkUserLimit($request): bool {
        if (!auth()->check()) return true;
        $key = "usr:{$request->user()->id()}";
        return $this->limiter->attempt($key, 1000, 60);  // 1000/hora
    }

    public function checkActionLimit($request, $action): bool {
        $key = "act:{$action}:{$request->user()->id() ?? $request->ip()}";
        $limits = [
            'login' => ['max' => 5, 'decay' => 15],
            'payment' => ['max' => 10, 'decay' => 60],
        ];
        $limit = $limits[$action] ?? ['max' => 100, 'decay' => 1];
        return $this->limiter->attempt($key, $limit['max'], $limit['decay']);
    }
}

// routes/api.php
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle_action:login');
```

**Validação:**
```bash
# Testar headers
curl -I https://localhost:8000/api/dashboard

# Testar rate limit
for i in {1..101}; do curl http://localhost:8000/api/dashboard; done
# Esperado: 429 na 101ª requisição
```

**Impacto:** +2.5% = **85.5% total**

---

### ✅ Semana 2 - Consolidação + Testes

```
Tempo: 10 horas
Foco: QA + Deploy Staging

Tarefas:
- [ ] 2.6 Browser testing (Chrome DevTools)
- [ ] 2.7 CSP violation detection
- [ ] 2.8 Rate limit behavior verification
- [ ] 2.9 Documentation update
- [ ] 2.10 Deploy em staging
```

---

## 📌 SEMANA 3-4: Core Security (HttpOnly Cookies + Encryption)

**Score: 85.5% → 88.5% (+3%)**  
**Esforço:** 60 horas | **Risco:** Médio | **ROI:** Alto

### ✅ Semana 3 - HttpOnly Cookies (1.5 semanas)

```
Sprint: Migrate from sessionStorage to HttpOnly cookies
Tempo: 35 horas
Deliverable: Complete auth refactor

Tarefas:
- [ ] 3.1 Criar AuthenticationController com cookie-based auth
- [ ] 3.2 Setup CSRF token generation
- [ ] 3.3 Migrar frontend para withCredentials: true
- [ ] 3.4 Remove sessionStorage logic
- [ ] 3.5 Test logout flow
- [ ] 3.6 Test cross-link navigation (cookie persistence)
```

**Code - Backend:**

```php
// app/Http/Controllers/AuthController.php (refatorado)
class AuthController extends Controller {
    public function login(Request $request) {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!auth()->attempt($validated)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // ✅ Usar token com cookie (Sanctum)
        $token = auth()->user()->createToken('api')->plainTextToken;

        return response()->json(['user' => auth()->user()])
            ->cookie(
                'auth_token',           // name
                $token,                 // value
                60 * 24,                // minutes (1 day)
                path: '/',
                domain: env('SESSION_DOMAIN'),
                secure: true,           // HTTPS only
                httpOnly: true,         // JS cannot access
                sameSite: 'strict'      // CSRF protection
            );
    }

    public function logout(Request $request) {
        auth()->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out'])
            ->cookie('auth_token', '', 0);  // Delete cookie
    }
}

// routes/api.php
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');
```

**Code - Frontend:**

```typescript
// src/config/api.ts (refatorado)
import axios from 'axios';

const apiClient = axios.create({
    baseURL: process.env.REACT_APP_API_URL,
    withCredentials: true,  // ✅ Enviar cookies automaticamente
});

// Interceptor para CSRF token
apiClient.interceptors.request.use((config) => {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    if (token) {
        config.headers['X-CSRF-Token'] = token;
    }
    return config;
});

export default apiClient;

// src/pages/Auth/LoginPage.tsx
const handleLogin = async (email: string, password: string) => {
    try {
        const response = await apiClient.post('/login', { email, password });
        // ✅ Cookie enviado automaticamente pelo navegador
        navigate('/dashboard');
    } catch (error) {
        showError('Login failed');
    }
};

// src/pages/Auth/LogoutPage.tsx
const handleLogout = async () => {
    try {
        await apiClient.post('/logout');
        // ✅ Cookie deletado pelo servidor
        navigate('/login');
    } catch (error) {
        console.error('Logout failed', error);
    }
};
```

**Validação:**
```bash
# Testar cookie HttpOnly
curl -v http://localhost:8000/api/login \
  -d '{"email":"test@test.com","password":"pass"}' \
  | grep "Set-Cookie"
# Esperado: "Set-Cookie: auth_token=...; HttpOnly; Secure"

# Testar acesso com cookie
curl -b "auth_token=YOUR_TOKEN" http://localhost:8000/api/auth/me
# Esperado: 200 OK com dados do usuário
```

### ✅ Semana 4 - Encryption em Repouso

```
Sprint: Encrypt sensitive fields in database
Tempo: 25 horas
Deliverable: Encrypted fields + migration

Tarefas:
- [ ] 4.1 Usar Laravel encrypted casts
- [ ] 4.2 Update Paciente model
- [ ] 4.3 Create migration para encryptData
- [ ] 4.4 Test encrypt/decrypt
- [ ] 4.5 Backup anterior
- [ ] 4.6 Run migration em staging
```

**Code - Encryption:**

```php
// app/Models/Paciente.php (refatorado)
class Paciente extends Model {
    protected $casts = [
        // ✅ Automáticamente encriptado/decriptado
        'cpf_cnpj' => 'encrypted',
        'email' => 'encrypted',
        'telefone' => 'encrypted',
        'celular' => 'encrypted',
        'data_nascimento' => 'encrypted',
        'rua' => 'encrypted',
        'numero' => 'encrypted',
        'complemento' => 'encrypted',
        'bairro' => 'encrypted',
        'cep' => 'encrypted',
    ];

    // Scope para busca em campo encriptado
    public function scopeByCpf($query, $cpf) {
        return $query->where('cpf_cnpj', encrypt($cpf));
    }
}

// database/migrations/2026_03_28_encrypt_sensitive_fields.php
class EncryptSensitiveFields extends Migration {
    public function up() {
        DB::table('pacientes')->get()->each(function ($paciente) {
            DB::table('pacientes')
                ->where('id', $paciente->id)
                ->update([
                    'cpf_cnpj' => encrypt($paciente->cpf_cnpj),
                    'email' => encrypt($paciente->email),
                    'telefone' => encrypt($paciente->telefone),
                ]);
        });
    }

    public function down() {
        // Reverse encryption (for rollback)
        DB::table('pacientes')->get()->each(function ($paciente) {
            try {
                DB::table('pacientes')
                    ->where('id', $paciente->id)
                    ->update([
                        'cpf_cnpj' => decrypt($paciente->cpf_cnpj),
                        'email' => decrypt($paciente->email),
                        'telefone' => decrypt($paciente->telefone),
                    ]);
            } catch (\Exception $e) {
                // Already decrypted
            }
        });
    }
}
```

**Impacto:** +3% = **88.5% total**

---

## 📌 SEMANA 5-6: Enterprise Features (Vault + Audit)

**Score: 88.5% → 91% (+2.5%)**  
**Esforço:** 50 horas | **Risco:** Médio | **ROI:** Médio-Alto

### Semana 5 - Secrets Management (Vault)

```
Sprint: Setup HashiCorp Vault
Tempo: 25 horas
Deliverable: Vault integration + secret rotation

Tarefas:
- [ ] 5.1 Docker Vault setup
- [ ] 5.2 VaultService implementation
- [ ] 5.3 Migrate .env secrets → Vault
- [ ] 5.4 Implement secret rotation
- [ ] 5.5 Add Vault SDK to composer
```

### Semana 6 - Advanced Audit Logging

```
Sprint: Elasticsearch + Audit Logs
Tempo: 25 horas
Deliverable: Real-time audit trail + anomaly detection

Tarefas:
- [ ] 6.1 Setup Elasticsearch
- [ ] 6.2 Create AuditLog model + Auditable trait
- [ ] 6.3 Configure log streaming to ES
- [ ] 6.4 Build anomaly detection
- [ ] 6.5 Create dashboarding queries
```

**Impacto:** +2.5% = **91% total**

---

## 📌 SEMANA 7-8: Automation (Testing + Monitoring)

**Score: 91% → 93% (+2%)**  
**Esforço:** 40 horas | **Risco:** Baixo | **ROI:** Médio

### Semana 7 - Automated Security Testing

```
Sprint: Setup SAST + DAST in CI/CD
Tempo: 20 horas

Tarefas:
- [ ] 7.1 GitHub Actions workflows
- [ ] 7.2 Psalm (PHP static analysis)
- [ ] 7.3 OWASP ZAP scanning
- [ ] 7.4 npm audit in CI
- [ ] 7.5 Dependency-check integration
```

### Semana 8 - Monitoring + Alerting

```
Sprint: Real-time security monitoring
Tempo: 20 horas

Tarefas:
- [ ] 8.1 SecurityMonitoringService
- [ ] 8.2 Anomaly detection rules
- [ ] 8.3 Slack/email alerts
- [ ] 8.4 Grafana dashboards
- [ ] 8.5 Incident response procedures
```

**Impacto:** +2% = **93% total**

---

## 📌 SEMANA 9+: Fine-Tuning (95%)

```
Score: 93% → 95%+ (+2%+)

Tarefas opcionais:
- [ ] Input validation avançado
- [ ] CORS subdomain isolation
- [ ] API versioning + deprecation
- [ ] Additional compliance (SOC2)
- [ ] Pen testing prep
```

---

## 🎯 Score Progression Chart

```
┌─────────────────────────────────────────────────────────┐
│ Security Score Progression (83% → 95%)                 │
├─────────────────────────────────────────────────────────┤
│ Baseline (atual):              83% ████████░░░         │
│ +Sem 1-2 (Headers+RateLimit):  85% ████████░░░         │
│ +Sem 3-4 (Cookies+Encrypt):    88% ██████████░░        │
│ +Sem 5-6 (Vault+Audit):        91% ███████████░        │
│ +Sem 7-8 (Testing+Monitor):    93% ███████████░        │
│ +Sem 9 (Fine-tuning):          95% ████████████░       │
└─────────────────────────────────────────────────────────┘
```

---

## 📋 Sprint Checklist Template

Use este template para cada semana:

```markdown
## [SEMANA N] - [TÍTULO]
**Score:** X% → Y% (+Z%)
**Status:** 🟢 In Progress / 🟡 At Risk / 🔴 Blocked

### Tarefas Completadas
- [x] Task 1
- [x] Task 2
- [ ] Task 3

### Bloqueadores
- [ ] None

### Próximos Passos
- Task 4
- Task 5

### Links
- PR: #123
- Docs: link
```

---

## 🚨 Risk Management

| Risco | Mitigation | Timeline |
|-------|-----------|----------|
| Breaking changes no auth | A/B test antes de migrate | Sem 3 do staging |
| Encryption migration falha | Backup completo primeiro | Sem 4 early |
| Vault outage | Fallback para .env em emergência | Always active |
| Performance degradation | Load testing antes de prod | Cada release |

---

## 📞 Escalation Path

```
Bloqueador → Tech Lead (24h)
   └─ CTO (48h)
      └─ CEO/Board (72h)
```

---

## ✅ Final Checklist (95%)

- [ ] All 10 implementations completed
- [ ] Security audit passed
- [ ] Pen testing approved
- [ ] Compliance verified (LGPD/GDPR)
- [ ] Documentation complete
- [ ] Team trained
- [ ] Runbooks prepared
- [ ] Incident response tested

---

**Início:** 2026-03-28  
**Meta:** 2026-05-23  
**Score Target:** 95%  
**Status:** 🟢 Ready to start

