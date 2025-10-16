# Sistema Odonto - API RESTful Completa

## 🏥 Visão Geral

Sistema odontológico completo com API RESTful segura, desenvolvido em Laravel 12 com integração MySQL e frontend React. O sistema inclui funcionalidades avançadas de segurança, criptografia LGPD, auditoria de dados e dashboard interativo.

## ✨ Características Principais

### 🔒 Segurança Avançada
- **Criptografia LGPD**: Dados sensíveis são automaticamente criptografados
- **Auditoria Completa**: Logs detalhados de todas as operações
- **Rate Limiting**: Proteção contra ataques de força bruta
- **Validação Rigorosa**: Validação de CPF, CNPJ, email, telefone
- **Headers de Segurança**: CORS, XSS, CSRF e outras proteções
- **Mascaramento de Dados**: Dados sensíveis podem ser mascarados na exibição

### 📊 Funcionalidades
- **Gestão de Pacientes**: CRUD completo com criptografia
- **Dashboard Interativo**: Estatísticas e relatórios em tempo real
- **Busca Avançada**: Filtros múltiplos e busca por texto
- **Exportação de Dados**: Exportação segura com opção de mascaramento
- **Sistema de Auditoria**: Rastreamento completo de alterações
- **API RESTful**: Endpoints bem documentados e padronizados

## 🚀 Tecnologias Utilizadas

### Backend
- **Laravel 12** - Framework PHP
- **MySQL** - Banco de dados principal
- **Laravel Sanctum** - Autenticação API
- **PHP 8.2+** - Linguagem de programação

### Frontend
- **React 19** - Biblioteca JavaScript
- **TypeScript** - Tipagem estática
- **Material-UI** - Componentes de interface
- **Axios** - Cliente HTTP

### Segurança
- **Criptografia AES-256-CBC** - Criptografia de dados
- **Rate Limiting** - Proteção contra spam
- **CORS** - Controle de origem cruzada
- **Headers de Segurança** - Proteções adicionais

## 📁 Estrutura do Projeto

```
odonto/                          # Backend Laravel
├── app/
│   ├── Console/Commands/        # Comandos Artisan
│   ├── Http/
│   │   ├── Controllers/         # Controladores da API
│   │   └── Middleware/          # Middlewares de segurança
│   ├── Models/                  # Modelos Eloquent
│   └── Services/                # Serviços de negócio
├── config/                      # Configurações
├── database/
│   └── migrations/              # Migrações do banco
├── routes/
│   └── api.php                  # Rotas da API
└── storage/logs/                # Logs de auditoria

dashboard-odonto/                # Frontend React
├── src/
│   ├── components/              # Componentes React
│   ├── hooks/                   # Hooks personalizados
│   ├── services/                # Serviços de API
│   └── utils/                   # Utilitários
└── public/                      # Arquivos públicos
```

## ⚙️ Instalação e Configuração

### Pré-requisitos
- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js 18+
- npm ou yarn

### 1. Configurar Backend (Laravel)

```bash
# Navegar para o diretório do backend
cd odonto

# Instalar dependências
composer install

# Copiar arquivo de configuração
cp .env.example .env

# Gerar chave da aplicação
php artisan key:generate

# Configurar banco de dados no .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=odonto_db
DB_USERNAME=root
DB_PASSWORD=sua_senha

# Executar migrações
php artisan migrate

# Migrar dados do SQLite para MySQL (se necessário)
php artisan migrate:mysql --force

# Iniciar servidor
php artisan serve
```

### 2. Configurar Frontend (React)

```bash
# Navegar para o diretório do frontend
cd dashboard-odonto

# Instalar dependências
npm install

# Configurar variáveis de ambiente
echo "REACT_APP_API_URL=http://localhost:8000/api" > .env

# Iniciar aplicação
npm start
```

## 🔧 Configuração Avançada

### Variáveis de Ambiente Importantes

```env
# Segurança
ENCRYPTION_KEY=sua-chave-de-32-caracteres
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

### Configuração do Banco de Dados

1. **Criar banco de dados**:
```sql
CREATE DATABASE odonto_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. **Configurar usuário** (opcional):
```sql
CREATE USER 'odonto_user'@'localhost' IDENTIFIED BY 'senha_segura';
GRANT ALL PRIVILEGES ON odonto_db.* TO 'odonto_user'@'localhost';
FLUSH PRIVILEGES;
```

## 📚 Uso da API

### Autenticação
```javascript
// Configurar token de autenticação
apiService.setToken('seu-token-aqui');
```

### Exemplos de Uso

#### Listar Pacientes
```javascript
// Buscar pacientes com filtros
const pacientes = await apiService.getPacientes({
  per_page: 20,
  masked: true,
  convenio: 'Unimed'
});
```

#### Criar Paciente
```javascript
// Criar novo paciente (dados são criptografados automaticamente)
const novoPaciente = await apiService.createPaciente({
  name: 'João Silva',
  cpf_cnpj: '123.456.789-00',
  email: 'joao@email.com',
  telefone: '(11) 99999-9999'
});
```

#### Dashboard
```javascript
// Obter estatísticas do dashboard
const stats = await apiService.getDashboardOverview();
```

## 🔍 Endpoints da API

### Pacientes
- `GET /api/pessoas/pacientes` - Listar pacientes
- `POST /api/pessoas/pacientes` - Criar paciente
- `GET /api/pessoas/pacientes/{id}` - Buscar paciente
- `PUT /api/pessoas/pacientes/{id}` - Atualizar paciente
- `DELETE /api/pessoas/pacientes/{id}` - Deletar paciente
- `GET /api/pessoas/pacientes/search` - Busca avançada
- `GET /api/pessoas/pacientes/statistics` - Estatísticas
- `GET /api/pessoas/pacientes/export` - Exportar dados

### Dashboard
- `GET /api/dashboard/overview` - Visão geral
- `GET /api/dashboard/patients-stats` - Estatísticas de pacientes
- `GET /api/dashboard/appointments-stats` - Estatísticas de consultas
- `GET /api/dashboard/system-health` - Saúde do sistema

## 🛡️ Segurança e LGPD

### Criptografia Automática
Dados sensíveis são automaticamente criptografados:
- CPF/CNPJ
- Telefones
- Emails
- Endereços
- Dados do responsável

### Auditoria
Todos os logs são salvos em:
- `storage/logs/audit.log` - Logs de auditoria
- `storage/logs/security.log` - Logs de segurança

### Mascaramento
Dados podem ser exibidos mascarados:
```javascript
// CPF: 123.456.789-00 → 123.***.***-00
// Email: joao@email.com → jo***@email.com
// Telefone: (11) 99999-9999 → (11) ****-9999
```

## 📊 Monitoramento

### Logs de Sistema
```bash
# Ver logs em tempo real
php artisan pail

# Ver logs de auditoria
tail -f storage/logs/audit.log

# Ver logs de segurança
tail -f storage/logs/security.log
```

### Saúde do Sistema
```bash
# Verificar saúde da API
curl http://localhost:8000/api/dashboard/system-health
```

## 🚀 Deploy

### Produção
1. **Configurar servidor web** (Nginx/Apache)
2. **Configurar SSL/TLS**
3. **Configurar banco de dados de produção**
4. **Configurar variáveis de ambiente**
5. **Executar migrações**
6. **Configurar backup automático**

### Docker (Opcional)
```dockerfile
# Dockerfile para Laravel
FROM php:8.2-fpm
# ... configurações do container
```

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature
3. Commit suas mudanças
4. Push para a branch
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.

## 🆘 Suporte

Para dúvidas ou problemas:
1. Verifique os logs em `storage/logs/`
2. Consulte a documentação da API
3. Verifique as configurações de banco
4. Confirme se todas as dependências estão instaladas

## 📈 Roadmap

- [ ] Sistema de notificações
- [ ] Integração com WhatsApp
- [ ] Relatórios em PDF
- [ ] Backup automático
- [ ] Monitoramento de performance
- [ ] Testes automatizados
- [ ] Documentação Swagger/OpenAPI

---

**Desenvolvido com ❤️ para clínicas odontológicas**

**Versão**: 1.0.0  
**Última Atualização**: Janeiro 2024