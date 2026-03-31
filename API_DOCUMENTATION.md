# API RESTful - Sistema Odonto

## Visão Geral

Esta é uma API RESTful completa e segura para o sistema odontológico, desenvolvida com Laravel e integrada com MySQL. A API inclui funcionalidades de criptografia LGPD, auditoria de dados, rate limiting e validações de segurança.

## Características de Segurança

- ✅ **Criptografia LGPD**: Dados sensíveis são automaticamente criptografados
- ✅ **Auditoria Completa**: Logs de todas as operações sensíveis
- ✅ **Rate Limiting**: Proteção contra ataques de força bruta
- ✅ **Validação de Dados**: Validação rigorosa de CPF, CNPJ, email, telefone
- ✅ **Headers de Segurança**: CORS, XSS, CSRF e outras proteções
- ✅ **Mascaramento de Dados**: Dados sensíveis podem ser mascarados na exibição

## Configuração

### Variáveis de Ambiente

```env
# Banco de Dados
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=odonto_db
DB_USERNAME=root
DB_PASSWORD=

# Segurança
ENCRYPTION_KEY=your-encryption-key-here-32-chars
API_RATE_LIMIT=60
API_RATE_LIMIT_WINDOW=1

# LGPD
LGPD_ENCRYPTION_ENABLED=true
LGPD_AUDIT_LOG=true
LGPD_DATA_RETENTION_DAYS=2555

# Frontend
FRONTEND_URL=http://localhost:3000
DASHBOARD_URL=http://localhost:3001
```

### Instalação

1. **Instalar dependências**:
```bash
composer install
npm install
```

2. **Configurar banco de dados**:
```bash
php artisan migrate
```

3. **Migrar dados do SQLite para MySQL** (se necessário):
```bash
php artisan migrate:mysql --force
```

4. **Iniciar servidor**:
```bash
php artisan serve
```

## Endpoints da API

### Base URL
```
http://localhost:8000/api
```

### Autenticação
A API usa Laravel Sanctum para autenticação. Inclua o token no header:
```
Authorization: Bearer {token}
```

---

## 🏥 Pacientes

### Listar Pacientes
```http
GET /api/pessoas/pacientes
```

**Parâmetros de Query:**
- `per_page` (int): Número de itens por página (padrão: 15)
- `masked` (boolean): Retornar dados mascarados (padrão: false)
- `name` (string): Filtrar por nome
- `convenio` (string): Filtrar por convênio
- `min_age` (int): Idade mínima
- `max_age` (int): Idade máxima

**Exemplo de Resposta:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "João Silva",
      "cpf_cnpj": "123.456.789-00",
      "email": "joao@email.com",
      "telefone": "(11) 99999-9999",
      "convenio": "Unimed",
      "idade": 30,
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  },
  "meta": {
    "masked": false,
    "audit_hash": ["abc123..."]
  }
}
```

### Criar Paciente
```http
POST /api/pessoas/pacientes
```

**Body:**
```json
{
  "name": "João Silva",
  "cpf_cnpj": "123.456.789-00",
  "email": "joao@email.com",
  "telefone": "(11) 99999-9999",
  "convenio": "Unimed",
  "idade": 30,
  "data_nascimento": "1994-01-01",
  "sexo": "Masculino",
  "estado": "SP",
  "rua": "Rua das Flores, 123",
  "bairro": "Centro",
  "cidade": "São Paulo",
  "cep": "01234-567"
}
```

### Buscar Paciente
```http
GET /api/pessoas/pacientes/{id}
```

### Dados Mascarados
```http
GET /api/pessoas/pacientes/{id}/masked
```

**Exemplo de Resposta:**
```json
{
  "data": {
    "id": 1,
    "name": "João Silva",
    "cpf_cnpj": "123.***.***-00",
    "email": "jo***@email.com",
    "telefone": "(11) ****-9999"
  },
  "audit_hash": "abc123...",
  "meta": {
    "masked": true,
    "retrieved_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

### Busca Avançada
```http
GET /api/pessoas/pacientes/search
```

**Parâmetros:**
- `search` (string): Termo de busca
- `convenio` (string): Filtrar por convênio
- `sexo` (string): Filtrar por sexo
- `min_age` (int): Idade mínima
- `max_age` (int): Idade máxima
- `estado` (string): Filtrar por estado

### Estatísticas de Pacientes
```http
GET /api/pessoas/pacientes/statistics
```

### Exportar Dados
```http
GET /api/pessoas/pacientes/export
```

**Parâmetros:**
- `format` (string): Formato de exportação (json, csv)
- `masked` (boolean): Exportar dados mascarados

### Dados de Referência

#### Convênios
```http
GET /api/pessoas/pacientes/reference/convenios
```

#### Estados
```http
GET /api/pessoas/pacientes/reference/estados
```

#### Sexos
```http
GET /api/pessoas/pacientes/reference/sexos
```

#### Estados Civis
```http
GET /api/pessoas/pacientes/reference/estados-civis
```

#### Tipos Sanguíneos
```http
GET /api/pessoas/pacientes/reference/tipos-sanguineos
```

---

## 📊 Dashboard

### Visão Geral
```http
GET /api/dashboard/overview
```

### Estatísticas de Pacientes
```http
GET /api/dashboard/patients-stats
```

### Estatísticas de Consultas
```http
GET /api/dashboard/appointments-stats
```

### Estatísticas de Procedimentos
```http
GET /api/dashboard/procedures-stats
```

### Atividades Recentes
```http
GET /api/dashboard/recent-activities
```

### Saúde do Sistema
```http
GET /api/dashboard/system-health
```

---

## 🔒 Segurança

### Rate Limiting
- **Limite**: 60 requisições por minuto por IP
- **Headers de Resposta**: Incluem informações sobre limites
- **Código de Erro**: 429 (Too Many Requests)

### Headers de Segurança
A API inclui automaticamente os seguintes headers:
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `X-XSS-Protection: 1; mode=block`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: geolocation=(), microphone=(), camera=()`

### CORS
Configurado para permitir requisições dos domínios:
- `http://localhost:3000` (Frontend)
- `http://localhost:3001` (Dashboard)

### Auditoria
Todos os logs de auditoria são salvos em:
- `storage/logs/audit.log`
- `storage/logs/security.log`

---

## 📝 Códigos de Status HTTP

| Código | Descrição |
|--------|-----------|
| 200 | Sucesso |
| 201 | Criado com sucesso |
| 400 | Dados inválidos |
| 401 | Não autorizado |
| 403 | Acesso negado |
| 404 | Não encontrado |
| 422 | Erro de validação |
| 429 | Muitas requisições |
| 500 | Erro interno do servidor |

---

## 🔧 Comandos Úteis

### Migrar para MySQL
```bash
php artisan migrate:mysql --force
```

### Limpar Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Gerar Chave de Aplicação
```bash
php artisan key:generate
```

### Executar Migrações
```bash
php artisan migrate
```

### Criar Usuário
```bash
php artisan tinker
>>> User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => bcrypt('password')])
```

---

## 🚀 Integração com Frontend

### Exemplo de Requisição (JavaScript)
```javascript
const apiUrl = 'http://localhost:8000/api';

// Listar pacientes
const response = await fetch(`${apiUrl}/pessoas/pacientes?masked=true`, {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  }
});

const data = await response.json();
console.log(data);
```

### Exemplo de Requisição (React)
```jsx
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Authorization': `Bearer ${token}`
  }
});

// Buscar pacientes
const fetchPatients = async () => {
  try {
    const response = await api.get('/pessoas/pacientes', {
      params: { masked: true, per_page: 20 }
    });
    return response.data;
  } catch (error) {
    console.error('Erro ao buscar pacientes:', error);
  }
};
```

---

## 📋 Checklist de Implementação

- [x] API RESTful completa
- [x] Criptografia LGPD
- [x] Sistema de auditoria
- [x] Rate limiting
- [x] Validações de segurança
- [x] Headers de segurança
- [x] CORS configurado
- [x] Documentação da API
- [x] Endpoints de dashboard
- [x] Comandos de migração
- [x] Logs estruturados
- [x] Mascaramento de dados
- [x] Estatísticas e relatórios

---

## 🆘 Suporte

Para dúvidas ou problemas:
1. Verifique os logs em `storage/logs/`
2. Consulte a documentação do Laravel
3. Verifique as configurações de banco de dados
4. Confirme se todas as dependências estão instaladas

---

**Versão**: 1.0.0  
**Última Atualização**: Janeiro 2024  
**Desenvolvido com**: Laravel 12, MySQL, PHP 8.2+
