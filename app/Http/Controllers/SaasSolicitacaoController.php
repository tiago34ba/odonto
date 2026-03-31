<?php

namespace App\Http\Controllers;

use App\Models\SaasSolicitacao;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SaasSolicitacaoController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // Listagem com filtros, paginação e summary
    // ─────────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = SaasSolicitacao::query()->orderBy('created_at', 'desc');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nome_clinica', 'like', "%{$search}%")
                  ->orWhere('responsavel', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('cnpj', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($plano = $request->input('plano_id')) {
            $query->where('plano_id', $plano);
        }

        $perPage = (int) $request->input('per_page', 10);
        $paginated = $query->paginate($perPage);

        // Summary geral (sem filtro de status para mostrar visão completa)
        $summary = [
            'total'                  => SaasSolicitacao::count(),
            'total_pendentes'        => SaasSolicitacao::where('status', 'pendente')->count(),
            'total_aguardando'       => SaasSolicitacao::where('status', 'aguardando_pagamento')->count(),
            'total_aprovadas'        => SaasSolicitacao::where('status', 'aprovada')->count(),
            'total_rejeitadas'       => SaasSolicitacao::where('status', 'rejeitada')->count(),
            'aprovadas_hoje'         => SaasSolicitacao::where('status', 'aprovada')
                                            ->whereDate('aprovado_em', Carbon::today())
                                            ->count(),
        ];

        $planos = SaasSolicitacao::planosDisponiveis();

        $data = $paginated->map(function (SaasSolicitacao $sol) use ($planos) {
            return [
                'id'                    => $sol->id,
                'nome_clinica'          => $sol->nome_clinica,
                'responsavel'           => $sol->responsavel,
                'email'                 => $sol->email,
                'telefone'              => $sol->telefone,
                'cnpj'                  => $sol->cnpj,
                'cidade'                => $sol->cidade,
                'estado'                => $sol->estado,
                'plano_id'              => $sol->plano_id,
                'plano_nome'            => $planos[$sol->plano_id]['label'] ?? ucfirst($sol->plano_id),
                'valor_mensal'          => $planos[$sol->plano_id]['valor'] ?? 0,
                'status'                => $sol->status,
                'pagamento_confirmado'  => $sol->pagamento_confirmado,
                'data_pagamento'        => $sol->data_pagamento?->toISOString(),
                'observacoes'           => $sol->observacoes,
                'motivo_rejeicao'       => $sol->motivo_rejeicao,
                'aprovado_em'           => $sol->aprovado_em?->toISOString(),
                'rejeitado_em'          => $sol->rejeitado_em?->toISOString(),
                'solicitado_em'         => $sol->created_at?->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'data'    => $data,
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Aprovar solicitação → cria acesso ao sistema
    // ─────────────────────────────────────────────────────────────
    public function aprovar(Request $request, int $id): JsonResponse
    {
        $sol = SaasSolicitacao::findOrFail($id);

        if ($sol->isAprovada()) {
            return response()->json(['success' => false, 'message' => 'Solicitacao ja aprovada.'], 422);
        }

        if ($sol->isRejeitada()) {
            return response()->json(['success' => false, 'message' => 'Solicitacao rejeitada nao pode ser aprovada.'], 422);
        }

        $sol->update([
            'status'       => 'aprovada',
            'aprovado_por' => Auth::id(),
            'aprovado_em'  => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Clinica \"{$sol->nome_clinica}\" aprovada com sucesso.",
            'data'    => ['id' => $sol->id, 'status' => $sol->status],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Rejeitar solicitação
    // ─────────────────────────────────────────────────────────────
    public function rejeitar(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'motivo' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $sol = SaasSolicitacao::findOrFail($id);

        if ($sol->isAprovada()) {
            return response()->json(['success' => false, 'message' => 'Solicitacao ja aprovada nao pode ser rejeitada.'], 422);
        }

        $sol->update([
            'status'           => 'rejeitada',
            'motivo_rejeicao'  => $request->input('motivo'),
            'rejeitado_por'    => Auth::id(),
            'rejeitado_em'     => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Solicitacao de \"{$sol->nome_clinica}\" rejeitada.",
            'data'    => ['id' => $sol->id, 'status' => $sol->status],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Confirmar pagamento manualmente
    // ─────────────────────────────────────────────────────────────
    public function confirmarPagamento(int $id): JsonResponse
    {
        $sol = SaasSolicitacao::findOrFail($id);

        $sol->update([
            'pagamento_confirmado' => true,
            'data_pagamento'       => Carbon::now(),
            'status'               => 'pendente', // volta para triagem após pagamento
        ]);

        return response()->json([
            'success' => true,
            'message' => "Pagamento de \"{$sol->nome_clinica}\" confirmado. Solicitacao retornou para triagem.",
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Criar nova solicitação (formulário público ou via admin)
    // ─────────────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nome_clinica' => 'required|string|max:255',
            'responsavel'  => 'required|string|max:255',
            'email'        => 'required|email|unique:saas_solicitacoes,email',
            'telefone'     => 'nullable|string|max:20',
            'cnpj'         => 'nullable|string|max:20',
            'cidade'       => 'nullable|string|max:100',
            'estado'       => 'nullable|string|max:2',
            'plano_id'     => 'required|in:basico,profissional,premium',
            'observacoes'  => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $sol = SaasSolicitacao::create([
            'nome_clinica' => $request->input('nome_clinica'),
            'responsavel'  => $request->input('responsavel'),
            'email'        => strtolower(trim($request->input('email'))),
            'telefone'     => $request->input('telefone'),
            'cnpj'         => $request->input('cnpj'),
            'cidade'       => $request->input('cidade'),
            'estado'       => $request->input('estado'),
            'plano_id'     => $request->input('plano_id'),
            'observacoes'  => $request->input('observacoes'),
            'status'       => 'pendente',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Solicitacao registrada com sucesso. Aguarde a analise.',
            'data'    => ['id' => $sol->id, 'status' => $sol->status],
        ], 201);
    }
}
