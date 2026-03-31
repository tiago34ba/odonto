# Relatorio de Niveis de Acesso

Data: 2026-03-26

## Fonte oficial usada
- Regras de permissoes por nivel: `app/Http/Controllers/UserController.php`
- Regras de exibicao de menu: `resources/js/src/components/layout/Sidebar/Sidebar.tsx`
- Regra espelhada no outro frontend: `dashboard-odonto/src/components/layout/Sidebar/Sidebar.tsx`

## Niveis e permissoes (backend)

### Secretaria
Permissoes:
- DASHBOARD_VIEW
- PATIENTS_VIEW
- PATIENTS_MANAGE
- SCHEDULINGS_VIEW
- SCHEDULINGS_MANAGE
- FINANCE_RECEIVABLE_VIEW

### Auxiliar Dentista
Permissoes:
- DASHBOARD_VIEW
- PATIENTS_VIEW
- SCHEDULINGS_VIEW
- ODONTOGRAM_VIEW
- TREATMENTS_ASSIST

### Dentista
Permissoes:
- DASHBOARD_VIEW
- PATIENTS_VIEW
- PATIENTS_MANAGE
- SCHEDULINGS_VIEW
- PROCEDURES_MANAGE
- ODONTOGRAM_VIEW
- ODONTOGRAM_MANAGE
- TREATMENTS_MANAGE

### Financeiro
Permissoes:
- DASHBOARD_VIEW
- FINANCE_DASHBOARD_VIEW
- FINANCE_PAYABLE_VIEW
- FINANCE_PAYABLE_MANAGE
- FINANCE_RECEIVABLE_VIEW
- FINANCE_RECEIVABLE_MANAGE
- FINANCE_CASHFLOW_VIEW
- FINANCE_REPORTS_VIEW
- ORCAMENTOS_VIEW

### Faxineiro
Permissoes:
- DASHBOARD_VIEW
- TASKS_VIEW

### Administrador
Permissoes:
- * (acesso total)

## O que cada nivel acessa no menu (com base nas regras atuais)

Observacao: a checagem do menu usa logica "OR" por item. Exemplo: se um item pede [A, B], ter A ja libera o item.

### Secretaria
Acessa por permissao:
- Dashboard
- Pessoas > Pacientes
- Agendamentos > Agendados
- Agendamentos > Relatorio de Agendamentos
- Agendamentos > Relatorio de Procedimentos
- Financeiro > Contas a Receber
- Financeiro > Recebimentos Convenio

Nao acessa por permissao:
- Pessoas > Usuarios
- Cadastros > Procedimentos
- Financeiro > Contas a Pagar
- Financeiro > Comissoes
- Odontogramas
- Tratamentos
- Tarefas / Agenda
- Relatorios (todos os relatorios financeiros)

### Auxiliar Dentista
Acessa por permissao:
- Dashboard
- Pessoas > Pacientes
- Agendamentos > Agendados
- Agendamentos > Relatorio de Agendamentos
- Agendamentos > Relatorio de Procedimentos
- Odontogramas
- Tratamentos

Nao acessa por permissao:
- Pessoas > Usuarios
- Cadastros > Procedimentos
- Financeiro (itens com permissao)
- Tarefas / Agenda
- Relatorios (todos os relatorios financeiros)

### Dentista
Acessa por permissao:
- Dashboard
- Pessoas > Pacientes
- Cadastros > Procedimentos
- Agendamentos > Agendados
- Agendamentos > Relatorio de Agendamentos
- Agendamentos > Relatorio de Procedimentos
- Odontogramas
- Tratamentos

Nao acessa por permissao:
- Pessoas > Usuarios
- Financeiro (itens com permissao)
- Tarefas / Agenda
- Relatorios (todos os relatorios financeiros)

### Financeiro
Acessa por permissao:
- Dashboard
- Financeiro > Contas a Pagar
- Financeiro > Contas a Receber
- Financeiro > Recebimentos Convenio
- Financeiro > Comissoes
- Financeiro > Consulta (apenas no frontend `resources/js`)
- Orcamentos
- Caixas (Aberto)
- Relatorios > Relatorio Financeiro
- Relatorios > Relatorio Sintetico Despesas
- Relatorios > Relatorio Sintetico Receber
- Relatorios > Relatorio Balanco Anual
- Relatorios > Relatorio Inadimplementes

Nao acessa por permissao:
- Pessoas > Pacientes
- Pessoas > Usuarios
- Cadastros > Procedimentos
- Agendamentos (itens com permissao)
- Odontogramas
- Tratamentos
- Tarefas / Agenda

### Faxineiro
Acessa por permissao:
- Dashboard
- Tarefas / Agenda

Nao acessa por permissao:
- Pessoas > Pacientes
- Pessoas > Usuarios
- Cadastros > Procedimentos
- Agendamentos (itens com permissao)
- Financeiro (itens com permissao)
- Odontogramas
- Tratamentos
- Relatorios (todos os relatorios financeiros)

### Administrador
Acessa por permissao:
- Todos os itens protegidos por permissao (devido ao `*`)

## Itens atualmente sem controle de permissao explicito no menu
Esses itens estao visiveis para qualquer usuario autenticado (porque nao possuem `requiredPermissions`):
- Cadastros > Grupo de Acessos (apenas no frontend `resources/js`)
- Cadastros > Acessos (apenas no frontend `resources/js`)

## Observacoes tecnicas importantes
- Se o usuario vier sem lista de permissoes (`permissoes`/`permissions` vazia), o menu nao bloqueia itens por compatibilidade.
- O nivel "Secretaria" (sem acento) e normalizado no backend para o grupo padrao de Secretaria.
- Recomendacao: adicionar `requiredPermissions` para todos os itens ainda abertos, e aplicar guardas de rota para evitar acesso direto por URL.
