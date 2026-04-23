<?php

namespace App\Http\Controllers\Helpdesk;

use App\Models\Helpdesk\HelpdeskAuditTrail;
use App\Models\Helpdesk\HelpdeskTicket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class HelpdeskTicketController extends AbstractHelpdeskCrudController
{
    protected function modelClass(): string { return HelpdeskTicket::class; }

    protected function searchableFields(): array
    {
        return ['numero', 'codigo', 'assunto', 'descricao', 'gestor_email'];
    }

    protected function filterableFields(): array
    {
        return ['status', 'cliente_id', 'problem_type_id'];
    }

    protected function buildQuery(Request $request): Builder
    {
        $query = parent::buildQuery($request);

        if ($periodoInicio = $request->input('periodo_inicio')) {
            $query->whereDate('created_at', '>=', $periodoInicio);
        }

        if ($periodoFim = $request->input('periodo_fim')) {
            $query->whereDate('created_at', '<=', $periodoFim);
        }

        if ($chamado = $request->input('chamado')) {
            $query->where('numero', 'like', "%{$chamado}%");
        }

        // Filtro de fila — usado pelas views de Chamados do painel
        $fila = (string) $request->input('fila', '');
        if ($fila === 'meus') {
            // Chamados ativos (abertos ou em andamento)
            $query->whereIn('status', ['aberto', 'em_atendimento', 'pendente']);
        } elseif ($fila === 'atribuidos') {
            // Chamados que possuem tecnico designado e nao estao arquivados
            $query->whereNotNull('tecnico_id')
                  ->whereNotIn('status', ['fechado', 'cancelado']);
        } elseif ($fila === 'arquivados') {
            // Chamados encerrados ou cancelados
            $query->whereIn('status', ['fechado', 'cancelado']);
        }
        // fila=todos: sem filtro extra

        return $query;
    }

    protected function validationRules(bool $isUpdate = false, ?int $id = null): array
    {
        $required = $isUpdate ? 'sometimes' : 'required';
        return [
            'numero' => [$isUpdate ? 'sometimes' : 'nullable', 'string', 'max:80', 'unique:helpdesk_tickets,numero' . ($id ? ',' . $id : '')],
            'cliente_id' => ['nullable', 'integer', 'exists:helpdesk_users,id'],
            'tecnico_id' => ['nullable', 'integer', 'exists:helpdesk_users,id'],
            'area_id' => ['nullable', 'integer', 'exists:helpdesk_areas,id'],
            'priority_id' => ['nullable', 'integer', 'exists:helpdesk_priorities,id'],
            'problem_id' => ['nullable', 'integer', 'exists:helpdesk_problems,id'],
            'problem_type_id' => ['nullable', 'integer', 'exists:helpdesk_problem_types,id'],
            'class_id' => ['nullable', 'integer', 'exists:helpdesk_classes,id'],
            'codigo' => [$required, 'string', 'max:120', 'unique:helpdesk_tickets,codigo' . ($id ? ',' . $id : '')],
            'assunto' => [$required, 'string', 'max:180'],
            'descricao' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:aberto,em_atendimento,pendente,resolvido,fechado,cancelado'],
            'canal_origem' => ['nullable', 'string', 'max:60'],
            'aberto_em' => ['nullable', 'date'],
            'prazo_em' => ['nullable', 'date'],
            'fechado_em' => ['nullable', 'date'],
            'anexos' => ['nullable', 'array'],
            'gestor_email' => ['nullable', 'email', 'max:180'],
            'confirmado_cliente' => ['nullable', 'boolean'],
            'confirmado_alteracao' => ['nullable', 'boolean'],
            'copiado_de_ticket_id' => ['nullable', 'integer'],
            'export_metadata' => ['nullable', 'array'],
        ];
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->all();
        if (empty($payload['numero'])) {
            $payload['numero'] = 'OS-' . now()->format('YmdHis');
            $request->merge(['numero' => $payload['numero']]);
        }
        if (empty($payload['aberto_em'])) {
            $request->merge(['aberto_em' => now()]);
        }

        $response = parent::store($request);
        $ticket = HelpdeskTicket::query()->latest('id')->first();
        if ($ticket) {
            $this->auditAndNotify('create', null, $ticket, $request);
        }
        return $response;
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $before = HelpdeskTicket::query()->findOrFail($id)->toArray();
        $response = parent::update($request, $id);
        $after = HelpdeskTicket::query()->findOrFail($id);
        $this->auditAndNotify('update', $before, $after, $request);
        return $response;
    }

    public function destroy(int $id): JsonResponse
    {
        $ticket = HelpdeskTicket::query()->findOrFail($id);
        $before = $ticket->toArray();
        $response = parent::destroy($id);
        $this->auditAndNotify('delete', $before, null, request());
        return $response;
    }

    public function copy(int $id): JsonResponse
    {
        $original = HelpdeskTicket::query()->findOrFail($id);
        $clone = $original->replicate();
        $clone->numero = 'OS-' . now()->format('YmdHis');
        $clone->codigo = $original->codigo . '-COPY-' . Str::upper(Str::random(4));
        $clone->status = 'aberto';
        $clone->copiado_de_ticket_id = $original->id;
        $clone->confirmado_alteracao = false;
        $clone->save();

        $this->auditAndNotify('copy', $original->toArray(), $clone, request());

        return response()->json(['success' => true, 'message' => 'Chamado copiado com sucesso.', 'data' => $clone]);
    }

    public function confirm(int $id): JsonResponse
    {
        $ticket = HelpdeskTicket::query()->findOrFail($id);
        $ticket->confirmado_cliente = true;
        $ticket->confirmado_alteracao = true;
        $ticket->save();
        $this->auditAndNotify('confirm', null, $ticket, request());

        return response()->json(['success' => true, 'message' => 'Chamado confirmado com sucesso.', 'data' => $ticket]);
    }

    private function auditAndNotify(string $action, ?array $before, $ticket, Request $request): void
    {
        $ticketId = is_object($ticket) ? $ticket->id : ($before['id'] ?? null);
        $ticketName = is_object($ticket) ? $ticket->assunto : ($before['assunto'] ?? 'Chamado');
        $managerEmail = is_object($ticket) ? $ticket->gestor_email : ($before['gestor_email'] ?? null);

        HelpdeskAuditTrail::create([
            'entity_type' => 'helpdesk_ticket',
            'entity_id' => $ticketId,
            'acao' => $action,
            'codigo' => 'AUD-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
            'nome' => $ticketName,
            'actor_nome' => optional($request->user())->name,
            'actor_email' => optional($request->user())->email,
            'gestor_email' => $managerEmail,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'payload_before' => $before,
            'payload_after' => is_object($ticket) ? $ticket->toArray() : null,
            'descricao' => 'Workflow automatico do chamado.',
            'status' => 'registrado',
        ]);

        if ($managerEmail) {
            try {
                Mail::raw("Workflow Helpdesk: ação {$action} executada no chamado {$ticketName}.", static function ($message) use ($managerEmail, $ticketName): void {
                    $message->to($managerEmail)->subject("Helpdesk - Workflow de {$ticketName}");
                });
            } catch (\Throwable) {
                // Fluxo best-effort: auditoria permanece mesmo sem e-mail.
            }
        }
    }
}
