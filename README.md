# Odonto Backend (Laravel)

Guia objetivo para instalar e executar o backend odontologico, no mesmo formato pratico usado no frontend.

## Visao Geral

- Projeto: API backend em Laravel 12
- Pasta: odonto
- URL local padrao: http://127.0.0.1:8000
- Integracao com frontend: dashboard-odonto em http://localhost:3000

## Pre-requisitos

- PHP 8.2+
- Composer 2+
- MySQL 8+ (ou outro banco compativel configurado no .env)
- Node.js 18+ e npm (para assets frontend do Laravel quando necessario)
- Git

## 1) Clonar o repositorio

```bash
git clone https://github.com/tiago34ba/odonto.git
cd odonto
```

## 2) Instalar dependencias

```bash
composer install
npm install
```

## 3) Configurar ambiente

### Windows (PowerShell)

```powershell
# Se .env.example existir e .env nao existir, cria o .env
if (!(Test-Path .env) -and (Test-Path .env.example)) { Copy-Item .env.example .env }

# Se .env.example nao existir, mantenha o .env atual e apenas revise os dados do banco
php artisan key:generate
```

### Linux/macOS

```bash
if [ ! -f .env ] && [ -f .env.example ]; then cp .env.example .env; fi
php artisan key:generate
```

## 4) Configurar banco de dados

Crie o banco no MySQL:

```sql
CREATE DATABASE odonto_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Edite o arquivo .env e ajuste, no minimo:

```env
APP_NAME=Odonto
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=odonto_db
DB_USERNAME=root
DB_PASSWORD=sua_senha

FRONTEND_URL=http://localhost:3000
```

## 5) Migrar banco e subir API

```bash
php artisan migrate
php artisan serve --host=127.0.0.1 --port=8000
```

Opcional (quando necessario no projeto):

```bash
php artisan migrate:mysql --force
```

## 6) Integracao com o frontend

No projeto dashboard-odonto, o proxy de desenvolvimento usa /api apontando para http://127.0.0.1:8000.

No frontend, garanta variavel equivalente a:

```env
REACT_APP_API_BASE_URL=http://127.0.0.1:8000/api
```

## 7) Validacao rapida

1. API online em http://127.0.0.1:8000
2. Frontend online em http://localhost:3000
3. Fluxos que usam API respondendo sem erro de CORS

## Comandos uteis

```bash
php artisan route:list
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
php artisan pail
```

## Solucao de problemas

- Erro de banco: confirme DB_DATABASE, DB_USERNAME e DB_PASSWORD no .env
- Erro de chave: rode php artisan key:generate
- Erro de CORS: confira FRONTEND_URL e origem do frontend
- Erro de dependencia: rode composer install novamente

## Documentacao complementar

- [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
- [COMANDOS_VALIDACAO_SEGURANCA.md](COMANDOS_VALIDACAO_SEGURANCA.md)
- [README_SEGURANCA_95_PERCENT.md](README_SEGURANCA_95_PERCENT.md)