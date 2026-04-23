# Estudo de Varredura Frontend + Backend e Propostas de Diferenciacao

Data: 2026-04-15
Escopo: analisar o sistema odontologico atual (Laravel + React) e propor melhorias/modulos novos para superar os principais concorrentes em valor percebido, retencao e receita por paciente.

---

## 1) Resumo Executivo

O sistema ja possui base robusta de operacao clinica e financeira:
- Cadastros completos (pacientes, funcionarios, dentistas, convenios, procedimentos).
- Agenda com regras de conflito e janela de trabalho do dentista.
- Financeiro com contas a pagar/receber, fluxo de caixa e dashboards.
- Portal do paciente com autoagendamento.
- Camada SaaS/Helpdesk ja iniciada no backend.

O principal gap competitivo nao e "falta de CRUD"; e falta de camadas de inteligencia comercial e operacao assistida por dados em cima da base existente.

Direcao recomendada para diferenciar:
1. IA de Agenda e No-Show (previsao + encaixe inteligente)
2. CRM de Conversao de Orcamentos (pipeline com automacoes)
3. Fidelizacao e LTV (recall, reativacao e campanhas segmentadas)
4. Torre de Controle da Clinica (indicadores em tempo real com alertas acionaveis)

---

## 2) Leitura da Base Atual (Varredura)

## 2.1 Backend

Pontos fortes identificados:
- Cobertura ampla de APIs clinicas e administrativas.
- Regras de agendamento evoluidas (slot, duracao, janela do dentista, protecao concorrencial).
- Financeiro estruturado com endpoints por dominio.
- Telemetria WhatsApp e scheduler operacional.
- Bloco SaaS/Helpdesk com CRUDs completos no backend.

Lacunas principais:
- Pouca inteligencia preditiva (agenda, cancelamento, no-show, carga operacional).
- Ausencia de "motor de jornada" para orcamentos e reativacao.
- Falta de camada de recomendacao (next best action por paciente).
- Falta de KPIs de unidade economica por dentista/procedimento/canal.

## 2.2 Frontend

Pontos fortes identificados:
- Grande cobertura de telas operacionais para clinica.
- Dashboard e relatorios disponiveis.
- Roteamento amplo para modulos administrativos.

Lacunas principais:
- Muitos modulos de alto valor no backend sem experiencia equivalente no frontend (especialmente SaaS/Helpdesk e inteligencia).
- UX orientada a operacao, mas pouco orientada a resultado (receita, ocupacao, conversao, LTV).
- Ausencia de cockpit de decisao com alertas e priorizacao do dia.

---

## 3) Propostas de Modulos para Diferenciacao

## Modulo A - IA de Agenda e No-Show

Problema de mercado:
- Concorrentes tratam agenda de forma estatica, sem previsao de risco por paciente/horario.

Diferencial proposto:
- Score de no-show por agendamento.
- Sugestao automatica de overbooking controlado por risco.
- Lista inteligente de encaixe para preencher cancelamentos em minutos.

Escopo MVP:
- Backend:
  - tabela: scheduling_risk_scores
  - endpoint: GET /schedulings/risk-dashboard
  - job: recalculo diario de risco
- Frontend:
  - "Semaforo de agenda" na tela de agendamentos
  - botao "preencher buraco" com ranking de pacientes aptos

Impacto esperado (90 dias):
- Reducao de no-show entre 20% e 35%.
- Aumento de ocupacao de agenda entre 10% e 20%.

## Modulo B - CRM de Conversao de Orcamentos

Problema de mercado:
- Orcamentos sem follow-up estruturado viram receita perdida.

Diferencial proposto:
- Pipeline visual: enviado -> negociacao -> aprovado -> iniciado.
- Automacao de follow-up (WhatsApp/email) com templates por etapa.
- Score de probabilidade de fechamento.

Escopo MVP:
- Backend:
  - tabela: budget_pipeline_events
  - endpoint: POST /treatment-plans/{id}/advance-stage
  - automacoes por scheduler
- Frontend:
  - kanban de orcamentos
  - painel "top oportunidades da semana"

Impacto esperado (90 dias):
- Aumento de conversao de orcamentos entre 15% e 30%.

## Modulo C - Fidelizacao e LTV

Problema de mercado:
- Sistemas focam em atendimento atual, nao em recorrencia do paciente.

Diferencial proposto:
- Cohorts de retorno por perfil de tratamento.
- Campanhas de recall inteligente (limpeza, revisao, manutencao ortodontica).
- Motor de reativacao de inativos com prioridade por potencial de receita.

Escopo MVP:
- Backend:
  - tabela: patient_ltv_metrics
  - endpoint: GET /patients/reativacao-priorizada
  - campanhas com segmentos dinamicos
- Frontend:
  - fila de reativacao diaria
  - metas por recepcionista/dentista

Impacto esperado (180 dias):
- Aumento de retorno de inativos em 20%.
- Aumento de receita por paciente ativo em 10% a 15%.

## Modulo D - Torre de Controle da Clinica (Command Center)

Problema de mercado:
- Dashboards passivos nao viram acao operacional.

Diferencial proposto:
- Visao unica em tempo real: agenda, financeiro, equipe, risco e atendimento.
- Alertas acionaveis com dono e prazo (ex.: "5 confirmacoes pendentes ate 14h").
- Rotina "inicio de turno" e "fechamento de turno" com checklist operacional.

Escopo MVP:
- Backend:
  - endpoint agregado: GET /dashboard/command-center
  - regras de alerta (thresholds configuraveis)
- Frontend:
  - pagina dedicada Command Center
  - cards acionaveis com CTA direto

Impacto esperado:
- Menor tempo de resposta operacional.
- Menos perda por falha de processo humano.

## Modulo E - Assistente Clinico de Plano (Anamnese + Odontograma)

Problema de mercado:
- O prontuario existe, mas o sistema nao "co-pilota" decisao clinica.

Diferencial proposto:
- Sugestao de trilhas de tratamento com base em padroes internos.
- Alertas de inconsistencias (anamnese x procedimento x risco).
- Checklist clinico por tipo de tratamento.

Escopo MVP:
- Backend:
  - regras de inferencia por protocolo
  - trilha de auditoria de sugestoes
- Frontend:
  - painel "sugestoes para este caso"
  - validacao humana obrigatoria (assistivo, nao autonomo)

Impacto esperado:
- Padronizacao clinica e ganho de produtividade por consulta.

## Modulo F - Motor de Precificacao e Rentabilidade

Problema de mercado:
- Tabela de preco sem visao de margem por procedimento/cadeira/dentista.

Diferencial proposto:
- Margem real por procedimento (considerando custo, comissao e no-show).
- Simulador de impacto de desconto na margem.
- Preco recomendado por perfil de convenio e unidade.

Escopo MVP:
- Backend:
  - tabela: procedure_profitability_snapshots
  - endpoint: GET /financeiro/procedimentos/rentabilidade
- Frontend:
  - painel de margem por procedimento
  - simulador rapido de desconto

Impacto esperado:
- Aumento de margem sem depender apenas de reajuste geral de preco.

## Modulo G - Jornada Omnichannel (WhatsApp + Voz + Email)

Problema de mercado:
- Comunicacao fragmentada e sem historico unico por paciente.

Diferencial proposto:
- Inbox unificada por paciente.
- Historico de contatos e respostas no prontuario operacional.
- Regra de melhor canal por perfil (entregabilidade e resposta).

Escopo MVP:
- Backend:
  - message_threads + message_events
  - webhooks de provedores
- Frontend:
  - "Central de comunicacao" por paciente
  - filtros por SLA de resposta

Impacto esperado:
- Mais conversao em confirmacoes e menos perda de follow-up.

## Modulo H - BI de Performance de Equipe com Gamificacao

Problema de mercado:
- Indicador sem acao individual nao muda comportamento.

Diferencial proposto:
- Metas por papel (recepcao, dentista, financeiro).
- Ranking por KPI com metas semanais.
- Mecanismo de reconhecimento interno com regras transparentes.

Escopo MVP:
- Backend:
  - team_kpi_snapshots
  - regra de metas por perfil
- Frontend:
  - painel por colaborador
  - quadro semanal de metas/resultado

Impacto esperado:
- Aumento de disciplina operacional e previsibilidade de resultados.

---

## 4) Melhorias Tecnicas de Suporte aos Novos Modulos

1. Camada de eventos de dominio
- Publicar eventos: agendamento_criado, agendamento_cancelado, orcamento_aprovado, pagamento_recebido.
- Beneficio: desacoplar automacoes e analytics do CRUD principal.

2. Filas e reprocessamento robusto
- Padronizar jobs com retry, dead-letter e idempotencia.
- Beneficio: confiabilidade de automacoes criticas.

3. Observabilidade funcional
- Dashboards de negocio e tecnicos no mesmo painel.
- Beneficio: detectar impacto antes do cliente sentir.

4. Testes por jornada de receita
- Cobrir fluxo fim-a-fim: lead -> orcamento -> agendamento -> comparecimento -> recebimento.
- Beneficio: reduzir regressao em funis de alto valor.

5. Contratos de API versionados
- Definir /api/v1 para modulos novos com OpenAPI.
- Beneficio: evolucao segura do frontend e integracoes futuras.

---

## 5) Priorizacao Recomendada (90 dias)

Fase 1 (0-30 dias): valor rapido
1. Command Center (versao inicial)
2. CRM de Orcamentos (pipeline + follow-up basico)
3. KPIs de agenda/no-show

Fase 2 (31-60 dias): inteligencia operacional
1. IA de Agenda e no-show score
2. Reativacao de pacientes (LTV)
3. Inbox omnichannel minima

Fase 3 (61-90 dias): ganho de margem e escala
1. Motor de rentabilidade por procedimento
2. Metas e performance de equipe
3. Primeira versao do assistente clinico

---

## 6) KPIs de Diferenciacao (metas de referencia)

1. No-show
- Meta: reduzir 20% em 90 dias.

2. Conversao de orcamento
- Meta: aumentar 15% em 90 dias.

3. Reativacao de inativos
- Meta: aumentar 10% em 90 dias.

4. Receita por paciente ativo
- Meta: aumentar 8% em 90 dias.

5. Ocupacao de agenda
- Meta: aumentar 12% em 90 dias.

6. Tempo medio de resposta operacional
- Meta: reduzir 30% em 60 dias no turno.

---

## 7) Backlog Inicial Sugerido (objetivo e pratico)

Sprint 1
- Endpoint agregado do Command Center
- Cards de alertas acionaveis na home
- Pipeline de orcamentos com 4 estagios

Sprint 2
- Jobs de follow-up automatico por etapa do pipeline
- Painel de risco de agenda (score inicial por regras)
- Fila de pacientes para encaixe

Sprint 3
- Segmentacao de reativacao e recall
- Dashboard de performance por colaborador
- Primeiras visoes de margem por procedimento

---

## 8) Conclusao

O sistema ja possui fundamentos fortes de operacao clinica. A vantagem competitiva de mercado virá ao transformar dados operacionais em motores de crescimento de receita e eficiencia: agenda inteligente, conversao comercial, fidelizacao/LTV e comando operacional em tempo real.

Essa combinacao posiciona o produto acima de suites que apenas "gerenciam consultas" e aproxima o sistema de uma plataforma de performance para clinicas odontologicas.
