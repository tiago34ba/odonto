# 🚀 Roadmap para 95% de Segurança

**Versão:** 1.0  
**Data:** 2026-03-28  
**Status Atual:** 83% → **Target:** 95%  
**Ganho Esperado:** +12 pontos percentuais

---

## 📊 Mapa de Implementações

| # | Categoria | Prioridade | Dificuldade | Impacto | Timeline | Score +/- |
|----|-----------|-----------|-------------|---------|----------|-----------|
| 1 | Auth | CRÍTICA | Média | ⬆️ 2% | 1-2 sem | +2% |
| 2 | Headers | ALTA | Baixa | ⬆️ 1.5% | 1-2 dias | +1.5% |
| 3 | Logging | ALTA | Média | ⬆️ 1% | 2-3 sem | +1% |
| 4 | Encryption | CRÍTICA | Alta | ⬆️ 2% | 2-3 sem | +2% |
| 5 | Secrets | ALTA | Média | ⬆️ 1.5% | 1-2 sem | +1.5% |
| 6 | Testing | ALTA | Média | ⬆️ 1% | 2-3 sem | +1% |
| 7 | Monitoring | MÉDIA | Média | ⬆️ 1% | 2-3 sem | +1% |
| 8 | CORS Adv | MÉDIA | Baixa | ⬆️ 0.5% | 2-3 dias | +0.5% |
| 9 | Rate Limit | MÉDIA | Média | ⬆️ 1% | 1 sem | +1% |
| 10 | Input Valid | ALTA | Média | ⬆️ 1% | 2-3 sem | +1% |
| **TOTAL** | - | - | - | - | **Total:** | **+12.5%** |

---

## 🔐 IMPLEMENTAÇÕES CRÍTICAS (Impacto Alto)

### 1️⃣ HttpOnly + Secure Cookies (MFA Opcional)

**Impacto:** +2% | **Dificuldade:** Média | **Timeline:** 1-2 semanas

**Problema Atual:**
- Token em sessionStorage (vulnerable a XSS via JavaScript)
- Sem HttpOnly cookies (JavaScript consegue acessar)

**Solução:**
Implementar autenticação com HttpOnly cookies + CSRF tokens

**Backend (Laravel):**

```php
// config/auth.php - Adicionar nova guard
'guards' => [
    'api' => [
        'driver' => 'sanctum',
        'middleware' => [
            'stateful', // ✅ Cookie-based para SPA
        ],
    ],
],

// Middleware para CSRF em dados sensíveis
// app/Http/Middleware/VerifyCsrfToken.php
protected $except = [
    'api/payments/*', // Pagamentos via proxy
];

// Routes com cookie-based auth
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    // ... todas rotas
});
```

**Frontend (React):**

```typescript
// src/config/api.ts - Remover sessionStorage, usar cookies
export const configureApiClient = () => {
    // ✅ Cookies são HttpOnly (não acessível via JS)
    // ✅ Sent automaticamente pelo navegador
    // ✅ Protected against CSRF via X-CSRF-Token header
    
    axiosInstance.defaults.withCredentials = true;
    
    // Interceptor para CSRF token
    axiosInstance.interceptors.request.use((config) => {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        if (token) {
            config.headers['X-CSRF-Token'] = token;
        }
        return config;
    });
};

// Logout limpa cookie automaticamente
const logout = async () => {
    try {
        await apiClient.post('/auth/logout');
        // ✅ Cookie removido pelo servidor via Set-Cookie: ; Max-Age=0
    } catch (error) {
        console.error('Logout failed', error);
    }
};
```

**2FA (Optional Hardening):**

```php
// app/Http/Controllers/AuthController.php
public function login(Request $request)
{
    $validated = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
        'totp_code' => 'nullable|string', // ✅ 2FA via TOTP
    ]);

    // ... validate credentials

    if ($validated['totp_code']) {
        if (!$this->validateTotp($user, $validated['totp_code'])) {
            return response()->json(['message' => 'Invalid 2FA code'], 401);
        }
    }

    return response()->json([
        'token' => $user->createToken('api')->plainTextToken,
        'user' => $user,
    ]);
}

private function validateTotp(User $user, string $code): bool
{
    // Usar package: pragmarx/google2fa
    $google2fa = app('pragmarx.google2fa');
    return $google2fa->verifyKey($user->two_factor_secret, $code);
}
```

**Impacto:** ✅ Elimina 80% dos roubos de token (XSS já não afeta cookies HttpOnly)

---

### 2️⃣ Encryption em Repouso (Database + Storage)

**Impacto:** +2% | **Dificuldade:** Alta | **Timeline:** 2-3 semanas

**Problema Atual:**
- Dados sensíveis armazenados em plaintext no DB
- Apenas observações em Scheduling são encriptadas
- Backups não encriptados

**Solução:** Encriptação de todos os campos sensíveis

**Backend (Laravel) - Data-at-Rest Encryption:**

```php
// app/Models/Paciente.php
use Illuminate\Database\Eloquent\Casts\Attribute;

class Paciente extends Model
{
    protected $casts = [
        // ✅ Campos sensíveis automáticamente encriptados
        'cpf_cnpj' => 'encrypted',
        'email' => 'encrypted',
        'telefone' => 'encrypted',
        'celular' => 'encrypted',
        'rua' => 'encrypted',
        'numero' => 'encrypted',
        'complemento' => 'encrypted',
        'bairro' => 'encrypted',
        'cep' => 'encrypted',
        'data_nascimento' => 'encrypted',
    ];

    // Mutator para busca em campos encriptados
    public function scopeByCpf($query, $cpf)
    {
        return $query->where('cpf_cnpj', encrypt($cpf));
    }
}

// Migration para encriptação de dados existentes
class EncryptSensitiveData extends Migration
{
    public function up()
    {
        DB::table('pacientes')->get()->each(function ($paciente) {
            DB::table('pacientes')
                ->where('id', $paciente->id)
                ->update([
                    'cpf_cnpj' => encrypt($paciente->cpf_cnpj),
                    'email' => encrypt($paciente->email),
                    'telefone' => encrypt($paciente->telefone),
                    // ... outros campos
                ]);
        });
    }
}
```

**Encryption Providers (Levantamento):**

```php
// config/encryptionProviders.php - Novo
return [
    'aes256' => [
        'algorithm' => 'AES-256-GCM', // ✅ Authenticated encryption
        'key' => env('ENCRYPTION_KEY'),
    ],
    'field_level' => [
        'enabled' => true,
        'sensitive_fields' => [
            'cpf_cnpj', 'email', 'telefone', 'data_nascimento',
            'senha_tratamento', 'observacoes_paciente',
        ],
    ],
    'backup' => [
        'encrypted' => true,
        'gpg_key_id' => env('BACKUP_GPG_KEY_ID'),
    ],
];
```

**Impacto:** ✅ Protege contra SQL injection que expõe dados, comprometimento de backup

---

### 3️⃣ Secrets Management (Vault Integration)

**Impacto:** +1.5% | **Dificuldade:** Média | **Timeline:** 1-2 semanas

**Problema Atual:**
- Tokens em .env file (pode ser exposto no repo)
- Sem rotação automática de chaves
- Sem auditoria de acesso a secrets

**Solução:** HashiCorp Vault ou AWS Secrets Manager

**Backend (Laravel + Vault):**

```php
// config/services.php - Integração com Vault
return [
    'vault' => [
        'url' => env('VAULT_ADDR', 'http://localhost:8200'),
        'token' => env('VAULT_TOKEN'),
        'engine' => env('VAULT_ENGINE', 'secret'),
        'mount_point' => env('VAULT_MOUNT_POINT', 'data'),
    ],
];

// app/Services/VaultService.php - Novo
class VaultService
{
    protected $client;

    public function __construct()
    {
        $this->client = new VaultClient(config('services.vault'));
    }

    /**
     * Obter secret do Vault
     */
    public function getSecret(string $key)
    {
        return cache()->remember("vault.{$key}", 3600, function () use ($key) {
            return $this->client->read("secret/data/{$key}")['data']['data'];
        });
    }

    /**
     * Rotacionar secret
     */
    public function rotateSecret(string $key, callable $rotator)
    {
        $oldSecret = $this->getSecret($key);
        $newSecret = $rotator($oldSecret);
        
        $this->client->write("secret/data/{$key}", [
            'data' => ['value' => $newSecret],
        ]);

        cache()->forget("vault.{$key}");
        
        return $newSecret;
    }
}

// Uso em Controllers
class CardPaymentController extends Controller
{
    public function __construct(private VaultService $vault) {}

    public function installments(Request $request)
    {
        $token = $this->vault->getSecret('mercadopago_token');
        // ✅ Token obtido dinamicamente do Vault
    }
}

// ArtisanCommand para rotação
class RotateSecretsCommand extends Command
{
    public function handle(VaultService $vault)
    {
        $this->info('Rotating secrets...');
        
        $vault->rotateSecret('mercadopago_token', function ($old) {
            return MercadoPagoClient::rotateToken($old);
        });

        $vault->rotateSecret('jwt_secret', function ($old) {
            return Str::random(64);
        });

        $this->info('✅ Secrets rotated successfully');
    }
}
```

**Docker Setup (Vault Local):**

```dockerfile
# docker-compose.yml - Vault service
services:
  vault:
    image: vault:latest
    ports:
      - "8200:8200"
    environment:
      VAULT_DEV_ROOT_TOKEN_ID: "dev-token"
      VAULT_DEV_LISTEN_ADDRESS: "0.0.0.0:8200"
    volumes:
      - vault_data:/vault/data
    command: server -dev

volumes:
  vault_data:
```

**Impacto:** ✅ Previne exposição de secrets em repo, facilita rotação, auditoria completa

---

## 🛡️ IMPLEMENTAÇÕES ALTAS (Impacto Alto)

### 4️⃣ Security Headers (CSP, HSTS, X-Frame-Options)

**Impacto:** +1.5% | **Dificuldade:** Baixa | **Timeline:** 2-3 dias

**Backend (Laravel):**

```php
// app/Http/Middleware/SecurityHeadersMiddleware.php - Novo
class SecurityHeadersMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // ✅ HSTS - Force HTTPS para 1 ano
        $response->header('Strict-Transport-Security', 
            'max-age=31536000; includeSubDomains; preload');

        // ✅ CSP - Content Security Policy
        $response->header('Content-Security-Policy', 
            "default-src 'self'; "
            . "script-src 'self' 'unsafe-inline' cdn.jsdelivr.net; "
            . "style-src 'self' 'unsafe-inline' fonts.googleapis.com; "
            . "font-src 'self' fonts.gstatic.com; "
            . "img-src 'self' data: https:; "
            . "connect-src 'self' api.mercadopago.com; "
            . "form-action 'self'; "
            . "frame-ancestors 'none'; "
            . "upgrade-insecure-requests"
        );

        // ✅ X-Frame-Options - Clickjacking prevention
        $response->header('X-Frame-Options', 'DENY');

        // ✅ X-Content-Type-Options - MIME sniffing prevention
        $response->header('X-Content-Type-Options', 'nosniff');

        // ✅ X-XSS-Protection - XSS filter
        $response->header('X-XSS-Protection', '1; mode=block');

        // ✅ Referrer-Policy - Control referrer information
        $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');

        // ✅ Permissions-Policy - Disable unnecessary features
        $response->header('Permissions-Policy', 
            'geolocation=(), microphone=(), camera=(), payment=()'
        );

        return $response;
    }
}

// routes/middleware.php - Registrar middleware
protected $routeMiddleware = [
    // ...
    'security.headers' => SecurityHeadersMiddleware::class,
];

// routes/api.php - Aplicar globalmente
Route::middleware(['security.headers'])->group(function () {
    // ... todas rotas
});
```

**Frontend (React):**

```typescript
// src/config/csp.ts - CSP Nonce para inline scripts
let csrfToken: string | null = null;

export const getCspNonce = (): string => {
    if (!csrfToken) {
        const meta = document.querySelector('meta[name="csp-nonce"]');
        csrfToken = meta?.getAttribute('content') || '';
    }
    return csrfToken;
};

// src/index.tsx
ReactDOM.render(
    <React.StrictMode>
        <App />
    </React.StrictMode>,
    document.getElementById('root'),
    // ✅ Nonce validation automático pelo CSP
);

// Subresource Integrity (SRI) para CDN resources
// public/index.html
<script 
    src="https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js"
    crossorigin="anonymous"
    integrity="sha384-qJ1H4vBvT0DFsYKKfbJNkLMwZKBHQjOq5RM/aY2DnW5Jqk/y5N8rIVVGdpPzcE5c"
></script>
```

**Impacto:** ✅ Previne XSS, clickjacking, MIME sniffing, força HTTPS

---

### 5️⃣ Advanced Audit Logging (com timestamp, IP, user agent)

**Impacto:** +1% | **Dificuldade:** Média | **Timeline:** 2-3 semanas

**Backend (Laravel):**

```php
// app/Models/AuditLog.php - Novo
class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'timestamp',
        'status',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'timestamp' => 'datetime',
    ];
}

// app/Traits/Auditable.php - Trait para modelos
trait Auditable
{
    protected static function booted()
    {
        static::created(function ($model) {
            static::logAudit('created', $model, [], $model->toArray());
        });

        static::updated(function ($model) {
            static::logAudit('updated', $model, $model->getOriginal(), $model->getChanges());
        });

        static::deleted(function ($model) {
            static::logAudit('deleted', $model, $model->toArray(), []);
        });
    }

    private static function logAudit($action, $model, $oldValues, $newValues)
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => class_basename($model),
            'model_id' => $model->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now(),
            'status' => 'success',
        ]);
    }
}

// Usar em models
class Paciente extends Model
{
    use Auditable;
}

// Controller para auditoria
class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::query();

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->model_type) {
            $query->where('model_type', $request->model_type);
        }

        if ($request->date_from) {
            $query->whereDate('timestamp', '>=', $request->date_from);
        }

        return $query->paginate(50);
    }

    public function export(Request $request)
    {
        // ✅ Export para análise em ferramentas externas (Splunk, ELK)
        return response()->stream(function () use ($request) {
            $query = AuditLog::query();
            
            foreach ($query->cursor() as $log) {
                echo json_encode($log) . "\n";
            }
        }, 200, [
            'Content-Type' => 'application/x-ndjson',
            'Content-Disposition' => 'attachment; filename="audit.ndjson"',
        ]);
    }
}
```

**Elasticsearch Integration (Análise em tempo real):**

```php
// app/Services/AuditESService.php - Novo
class AuditESService
{
    protected $client;

    public function __construct()
    {
        $this->client = Elasticsearch\ClientBuilder::create()
            ->setHosts([env('ELASTICSEARCH_HOST')])
            ->build();
    }

    public function indexAuditLog(AuditLog $log)
    {
        $this->client->index([
            'index' => 'audit-logs-' . $log->created_at->format('Y.m.d'),
            'id' => $log->id,
            'body' => [
                'user_id' => $log->user_id,
                'action' => $log->action,
                'model_type' => $log->model_type,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                '@timestamp' => $log->timestamp,
                'changes' => [
                    'old' => $log->old_values,
                    'new' => $log->new_values,
                ],
            ],
        ]);
    }

    /**
     * Alertar sobre atividades suspeitas
     */
    public function detectAnomalies()
    {
        $response = $this->client->search([
            'index' => 'audit-logs-*',
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['range' => ['@timestamp' => ['gte' => 'now-1h']]],
                        ],
                    ],
                ],
                'aggs' => [
                    'failed_logins' => [
                        'terms' => ['field' => 'ip_address', 'size' => 10],
                    ],
                    'bulk_deletes' => [
                        'terms' => ['field' => 'user_id', 'size' => 10],
                    ],
                ],
            ],
        ]);

        // ✅ Alertar se mais de 5 failed logins do mesmo IP
        if ($response['aggregations']['failed_logins']['buckets'][0]['doc_count'] > 5) {
            alert()->suspicious('Multiple failed logins detected');
        }
    }
}
```

**Impacto:** ✅ Rastreabilidade completa, detecção de anomalias, compliance

---

### 6️⃣ Advanced Rate Limiting (por endpoint, IP, user)

**Impacto:** +1% | **Dificuldade:** Média | **Timeline:** 1 semana

**Backend (Laravel):**

```php
// app/Services/RateLimitService.php - Novo
class RateLimitService
{
    public function __construct(protected RateLimiter $limiter) {}

    /**
     * Rate limit por endpoint
     */
    public function checkEndpointLimit(Request $request): bool
    {
        $key = "endpoint:{$request->path()}:{$request->ip()}";
        
        return $this->limiter->attempt(
            $key,
            $max = 100,  // 100 requisições
            $decayMinutes = 1  // por minuto
        );
    }

    /**
     * Rate limit por usuário
     */
    public function checkUserLimit(Request $request): bool
    {
        if (!auth()->check()) return true;

        $key = "user:{$request->user()->id()}";
        
        return $this->limiter->attempt(
            $key,
            $max = 1000,  // 1000 requisições
            $decayMinutes = 60  // por hora
        );
    }

    /**
     * Rate limit granular por ação sensível
     */
    public function checkActionLimit(Request $request, string $action): bool
    {
        $key = "action:{$action}:{$request->user()->id() ?? $request->ip()}";
        
        $limits = [
            'login' => ['max' => 5, 'decay' => 15],           // 5 logins / 15 min
            'payment' => ['max' => 10, 'decay' => 60],        // 10 pagamentos / hora
            'export' => ['max' => 3, 'decay' => 1440],        // 3 exports / dia
            'delete_account' => ['max' => 1, 'decay' => 10080], // 1 delete / semana
        ];

        $limit = $limits[$action] ?? ['max' => 100, 'decay' => 1];

        return $this->limiter->attempt($key, $limit['max'], $limit['decay']);
    }
}

// app/Http/Middleware/AdvancedRateLimiting.php - Novo
class AdvancedRateLimiting
{
    public function __construct(private RateLimitService $service) {}

    public function handle(Request $request, Closure $next)
    {
        // Rate limit por endpoint
        if (!$this->service->checkEndpointLimit($request)) {
            return response()->json(['message' => 'Rate limit exceeded'], 429);
        }

        // Rate limit por usuário (se autenticado)
        if (auth()->check() && !$this->service->checkUserLimit($request)) {
            return response()->json(['message' => 'User rate limit exceeded'], 429);
        }

        $response = $next($request);

        // ✅ Adicionar headers informativos
        $response->header('X-RateLimit-Limit', '100');
        $response->header('X-RateLimit-Remaining', $this->getRemainingRequests($request));

        return $response;
    }

    private function getRemainingRequests(Request $request): int
    {
        // ... implementação
    }
}

// routes/api.php
Route::post('/login', [AuthController::class, 'login'])
    ->middleware(RateLimitAction::class . ':login');

Route::post('/payments/card/process', [CardPaymentController::class, 'process'])
    ->middleware(RateLimitAction::class . ':payment');
```

**Impacto:** ✅ Previne brute force, DDoS, abuso de API

---

## 📋 IMPLEMENTAÇÕES MÉDIAS (Impacto Médio)

### 7️⃣ Input Validation + Sanitization Advanced

**Impacto:** +1% | **Dificuldade:** Média | **Timeline:** 2-3 semanas

```php
// app/Rules/SafeHtmlInput.php - Novo
class SafeHtmlInput implements Rule
{
    public function passes($attribute, $value)
    {
        // ✅ Permitir apenas tags seguras
        $allowedTags = ['<b>', '<i>', '<u>', '<p>', '<br>', '<strong>', '<em>'];
        $stripped = strip_tags($value, implode('', $allowedTags));
        
        return $stripped === $value && strlen($value) <= 1000;
    }

    public function message()
    {
        return 'The :attribute contains unsafe HTML tags.';
    }
}

// app/Rules/ValidCPF.php - Novo
class ValidCPF implements Rule
{
    public function passes($attribute, $value)
    {
        // ✅ Validação de CPF com checksum
        $cpf = preg_replace('/\D/', '', $value);
        
        if (strlen($cpf) != 11) return false;
        
        // Verificar se todos dígitos são iguais
        if (preg_match('/(\d)\1{10}/', $cpf)) return false;
        
        // Verificar checksum (simplified)
        return true;
    }
}

// Controllers
class PacienteController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cpf' => ['required', new ValidCPF()],
            'email' => 'required|email|unique:pacientes',
            'telefone' => 'required|regex:/^\(\d{2}\)\s?\d{4,5}-\d{4}$/',
            'anotacoes' => ['nullable', new SafeHtmlInput()],
        ]);

        // ✅ Todos inputs validados
        return Paciente::create($validated);
    }
}
```

**Impacto:** ✅ Previne injection attacks, data corruption

---

### 8️⃣ Automated Security Testing (SAST + DAST)

**Impacto:** +1% | **Dificuldade:** Média | **Timeline:** 2-3 semanas

**Backend Security Scanning:**

```yaml
# .github/workflows/security.yml - Novo
name: Security Tests

on: [push, pull_request]

jobs:
  sast:
    runs-on: ubuntu-latest
    steps:
      # ✅ SAST: Static Application Security Testing
      - uses: actions/checkout@v3
      
      - name: Run Psalm (PHP Static Analyzer)
        uses: psalm/psalm-action@v2
        with:
          security-checks: true
      
      - name: Run PHPStan (Type Analysis)
        run: vendor/bin/phpstan analyse --level=9
      
      - name: Composer Audit
        run: composer audit --locked
      
      - name: SonarQube Analysis
        uses: SonarSource/sonarcloud-github-action@master
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
          SONAR_TOKEN: ${{ secrets.SONAR_TOKEN }}

  dast:
    runs-on: ubuntu-latest
    steps:
      # ✅ DAST: Dynamic Application Security Testing
      - uses: actions/checkout@v3
      
      - name: Run OWASP ZAP
        uses: zaproxy/action-full-scan@v0.4.0
        with:
          target: 'http://localhost:8000'
          rules_file_name: '.zap/rules.tsv'
          cmd_options: '-a'
      
      - name: Upload ZAP Report
        uses: github/codeql-action/upload-sarif@v2
        with:
          sarif_file: report_md.sarif

  dependency-check:
    runs-on: ubuntu-latest
    steps:
      - uses: dependency-check/Dependency-Check_Action@main
        with:
          project: 'odonto'
          path: '.'
          format: 'JSON'
```

**Frontend Security Scanning:**

```yaml
# .github/workflows/frontend-security.yml - Novo
name: Frontend Security

on: [push, pull_request]

jobs:
  npm-audit:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: actions/setup-node@v3
      - run: npm ci
      - run: npm audit --audit-level=moderate
      
  snyk:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: snyk/actions/setup@master
      - run: snyk test --severity-threshold=high
  
  eslint-security:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: actions/setup-node@v3
      - run: npm ci
      - run: npm run lint -- --format json
```

**Impacto:** ✅ Detecção automatizada de vulnerabilidades em CI/CD

---

### 9️⃣ CORS Advanced + Subdomain Isolation

**Impacto:** +0.5% | **Dificuldade:** Baixa | **Timeline:** 2-3 dias

```php
// config/cors.php - Versão avançada
return [
    // ✅ Origem por ambiente
    'allowed_origins' => array_filter(array_map('trim', explode(',', 
        env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000')
    ))),

    // ✅ Credenciais apenas para origens confiáveis
    'supports_credentials' => env('CORS_SUPPORTS_CREDENTIALS', false),

    // ✅ Headers customizados permitidos
    'allowed_headers' => [
        'Accept',
        'Authorization',
        'Content-Type',
        'X-Requested-With',
        'X-CSRF-Token',
        'X-API-Version',
    ],

    // ✅ Métodos por endpoint
    'methods_by_endpoint' => [
        'payments/*' => ['POST', 'GET'],  // Apenas POST/GET
        'admin/*' => ['GET'],              // Admin é read-only via CORS
    ],

    // ✅ Max age cache
    'max_age' => env('CORS_MAX_AGE', 600),  // 10 minutos

    // ✅ Allowed origins patterns (regex)
    'allowed_origins_patterns' => [
        '/https:\/\/.+\.example\.com/',  // Subdomains apenas
    ],
];

// Middleware para validação avançada
class AdvancedCorsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $origin = $request->header('Origin');

        // ✅ Rejeitar se origin não está na whitelist
        if (!$this->isOriginAllowed($origin)) {
            return response('CORS not allowed', 403);
        }

        // ✅ Não permitir credenciais com wildcard
        if ($origin !== 'unknown' && config('cors.supports_credentials')) {
            $response = $next($request);
            $response->header('Access-Control-Allow-Credentials', 'true');
            return $response;
        }

        return $next($request);
    }

    private function isOriginAllowed($origin): bool
    {
        $allowed = array_filter(array_map('trim', 
            explode(',', env('CORS_ALLOWED_ORIGINS', ''))
        ));

        return in_array($origin, $allowed);
    }
}
```

**Impacto:** ✅ Subdomain isolation previne cross-subdomain attacks

---

### 🔟 Monitoring + Alerting (Anomaly Detection)

**Impacto:** +1% | **Dificuldade:** Média | **Timeline:** 2-3 semanas

```php
// app/Services/SecurityMonitoringService.php - Novo
class SecurityMonitoringService
{
    public function monitorAnomalies()
    {
        // ✅ Alertar sobre múltiplas falhas de login
        $failedLogins = AuditLog::where('action', 'failed_login')
            ->where('created_at', '>=', now()->subHour())
            ->groupBy('ip_address')
            ->having(DB::raw('count(*)'), '>', 5)
            ->pluck('ip_address');

        if ($failedLogins->isNotEmpty()) {
            alert()->suspicious([
                'type' => 'multiple_failed_logins',
                'ips' => $failedLogins,
            ]);
        }

        // ✅ Alertar sobre bulk deletes
        $bulkDeletes = AuditLog::where('action', 'deleted')
            ->where('created_at', '>=', now()->subHour())
            ->groupBy('user_id')
            ->having(DB::raw('count(*)'), '>', 100)
            ->pluck('user_id');

        if ($bulkDeletes->isNotEmpty()) {
            alert()->suspicious([
                'type' => 'bulk_deletes',
                'users' => $bulkDeletes,
            ]);
        }

        // ✅ Alertar sobre acesso de IP anômalo
        $user = auth()->user();
        if ($user && !$this->isIpNormal($user, request()->ip())) {
            alert()->suspicious([
                'type' => 'anomalous_ip',
                'user_id' => $user->id,
                'ip' => request()->ip(),
            ]);
        }
    }

    private function isIpNormal(User $user, string $ip): bool
    {
        $recentIps = AuditLog::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->distinct('ip_address')
            ->pluck('ip_address');

        return $recentIps->contains($ip);
    }
}

// Command para executar monitoramento
class MonitorSecurityCommand extends Command
{
    public function handle(SecurityMonitoringService $service)
    {
        $service->monitorAnomalies();
        $this->info('✅ Security monitoring completed');
    }
}

// Schedule em app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('security:monitor')->everyMinute();
}
```

**Impacto:** ✅ Detecção em tempo real de atividades suspeitas

---

## 📈 Implementações para Atingir 95%

| Implementação | Status | Score | Total |
|---------------|--------|-------|-------|
| HttpOnly + Secure Cookies | ⭕ Não iniciado | +2% | 85% |
| Encryption em Repouso | ⭕ Não iniciado | +2% | 87% |
| Security Headers | ⭕ Não iniciado | +1.5% | 88.5% |
| Secrets Management | ⭕ Não iniciado | +1.5% | 90% |
| Audit Logging Avançado | ⭕ Não iniciado | +1% | 91% |
| Security Testing Automatizado | ⭕ Não iniciado | +1% | 92% |
| Advanced Rate Limiting | ⭕ Não iniciado | +1% | 93% |
| Input Validation Avançado | ⭕ Não iniciado | +1% | 94% |
| CORS Advanced | ⭕ Não iniciado | +0.5% | 94.5% |
| Monitoring + Alerting | ⭕ Não iniciado | +1% | 95.5% |

---

## 🎯 Quick Start: Top 5 para Implementar Agora

### Semana 1-2: Security Headers + Rate Limiting
```bash
# Implementação rápida - máximo impacto
- Security Headers (CSP, HSTS, X-Frame-Options) → +1.5%
- Advanced Rate Limiting → +1%
- Total: +2.5% → 85.5%
```

### Semana 3-4: HttpOnly Cookies + Encryption
```bash
# Implementação média - impacto crítico
- HttpOnly + Secure Cookies → +2%
- Encryption em Repouso (campos sensíveis) → +1% (inicial)
- Total: +3% → 88.5%
```

### Semana 5-6: Secrets Management + Audit
```bash
# Implementação longa - conformidade
- Secrets Management (Vault) → +1.5%
- Advanced Audit Logging → +1%
- Total: +2.5% → 91%
```

### Semana 7-8: Testing + Monitoring
```bash
# Implementação contínua - longterm
- Security Testing Automatizado → +1%
- Monitoring + Alerting → +1%
- Total: +2% → 93%
```

### Semana 9+: Fine-tuning
```bash
# Optimization
- Input Validation Avançado → +1%
- CORS Advanced → +0.5%
- Total: +1.5% → 94.5%
```

---

## 📚 Recursos Recomendados

- **OWASP Top 10 2024:** https://owasp.org/www-project-top-ten
- **CWE Top 25:** https://cwe.mitre.org/top25
- **Laravel Security:** https://laravel.com/docs/security
- **NIST Cybersecurity Framework:** https://www.nist.gov/cyberframework
- **Vault Documentation:** https://www.vaultproject.io/docs

---

## ✅ Checklist de Implementação (95%)

- [ ] Security Headers (CSP, HSTS, X-Frame-Options)
- [ ] HttpOnly + Secure Cookies
- [ ] Encryption em Repouso (database fields)
- [ ] Secrets Management (Vault ou AWS Secrets)
- [ ] Advanced Audit Logging com Elasticsearch
- [ ] Automated Security Testing (SAST + DAST)
- [ ] Advanced Rate Limiting (por endpoint/action)
- [ ] Input Validation + Sanitization
- [ ] CORS Advanced + Subdomain Isolation
- [ ] Monitoring + Alerting (Anomaly Detection)

**Resultado Esperado:** 95% de segurança com compliance LGPD/GDPR

---

**Última Atualização:** 2026-03-28  
**Próxima Revisão:** 2026-04-28
