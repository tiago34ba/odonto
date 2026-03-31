<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\GrupoAcesso;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::with('grupoAcesso:id,nome,ativo,permissoes');

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nome', 'like', "%{$search}%")
                    ->orWhere('sobrenome', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('telefone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('grupo_acesso_id')) {
            $query->where('grupo_acesso_id', (int) $request->query('grupo_acesso_id'));
        }

        if ($request->filled('ativo')) {
            $query->where('ativo', filter_var($request->query('ativo'), FILTER_VALIDATE_BOOLEAN));
        }

        $perPage = min((int) $request->query('per_page', 15), 100);
        $users = $query->orderByDesc('id')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Usuarios listados com sucesso',
            'data' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (!empty($validated['nivel']) && empty($validated['grupo_acesso_id'])) {
            $validated['grupo_acesso_id'] = $this->resolveGrupoAcessoIdByNivel($validated['nivel']);
        }

        unset($validated['nivel']);

        if (empty($validated['name'])) {
            $validated['name'] = trim(((string) ($validated['nome'] ?? '')) . ' ' . ((string) ($validated['sobrenome'] ?? '')));
        }

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated)->load('grupoAcesso:id,nome,ativo,permissoes');

        return response()->json([
            'success' => true,
            'message' => 'Usuario criado com sucesso',
            'data' => $user,
        ], 201);
    }

    public function show(User $usuario): JsonResponse
    {
        $usuario->load('grupoAcesso:id,nome,ativo,permissoes');

        return response()->json([
            'success' => true,
            'data' => $usuario,
        ]);
    }

    public function update(UpdateUserRequest $request, User $usuario): JsonResponse
    {
        $validated = $request->validated();

        if (!empty($validated['nivel']) && empty($validated['grupo_acesso_id'])) {
            $validated['grupo_acesso_id'] = $this->resolveGrupoAcessoIdByNivel($validated['nivel']);
        }

        unset($validated['nivel']);

        if (array_key_exists('password', $validated)) {
            if (empty($validated['password'])) {
                unset($validated['password']);
            } else {
                $validated['password'] = Hash::make($validated['password']);
            }
        }

        if (empty($validated['name']) && (array_key_exists('nome', $validated) || array_key_exists('sobrenome', $validated))) {
            $validated['name'] = trim(((string) ($validated['nome'] ?? $usuario->nome ?? '')) . ' ' . ((string) ($validated['sobrenome'] ?? $usuario->sobrenome ?? '')));
        }

        $usuario->update($validated);
        $usuario->load('grupoAcesso:id,nome,ativo,permissoes');

        return response()->json([
            'success' => true,
            'message' => 'Usuario atualizado com sucesso',
            'data' => $usuario,
        ]);
    }

    public function destroy(User $usuario): JsonResponse
    {
        $usuario->delete();

        return response()->json([
            'success' => true,
            'message' => 'Usuario removido com sucesso',
        ]);
    }

    private function resolveGrupoAcessoIdByNivel(string $nivel): int
    {
        $normalized = match (trim($nivel)) {
            'Secretaria' => 'Secretária',
            default => trim($nivel),
        };

        $defaultPermissions = [
            // Padrao recomendado para clinica odontologica.
            'Secretária' => [
                'DASHBOARD_VIEW',
                'PATIENTS_VIEW',
                'PATIENTS_MANAGE',
                'SCHEDULINGS_VIEW',
                'SCHEDULINGS_MANAGE',
                'FINANCE_RECEIVABLE_VIEW',
            ],
            'Auxiliar Dentista' => [
                'DASHBOARD_VIEW',
                'PATIENTS_VIEW',
                'SCHEDULINGS_VIEW',
                'ODONTOGRAM_VIEW',
                'TREATMENTS_ASSIST',
            ],
            'Dentista' => [
                'DASHBOARD_VIEW',
                'PATIENTS_VIEW',
                'PATIENTS_MANAGE',
                'SCHEDULINGS_VIEW',
                'PROCEDURES_MANAGE',
                'ODONTOGRAM_VIEW',
                'ODONTOGRAM_MANAGE',
                'TREATMENTS_MANAGE',
            ],
            'Financeiro' => [
                'DASHBOARD_VIEW',
                'FINANCE_DASHBOARD_VIEW',
                'FINANCE_PAYABLE_VIEW',
                'FINANCE_PAYABLE_MANAGE',
                'FINANCE_RECEIVABLE_VIEW',
                'FINANCE_RECEIVABLE_MANAGE',
                'FINANCE_CASHFLOW_VIEW',
                'FINANCE_REPORTS_VIEW',
                'ORCAMENTOS_VIEW',
            ],
            'Faxineiro' => [
                'DASHBOARD_VIEW',
                'TASKS_VIEW',
            ],
            // Acesso total ao sistema.
            'Administrador' => ['*'],
        ];

        $permissions = $defaultPermissions[$normalized] ?? ['DASHBOARD_VIEW'];

        $grupo = GrupoAcesso::firstOrCreate(
            ['nome' => $normalized],
            [
                'descricao' => 'Grupo criado automaticamente pelo modulo Usuarios',
                'cor' => '#2563EB',
                'permissoes' => $permissions,
                'ativo' => true,
            ]
        );

        // Mantem grupos padrao sincronizados mesmo se ja existirem.
        $grupo->permissoes = $permissions;
        $grupo->descricao = 'Grupo criado automaticamente pelo modulo Usuarios';

        if (!$grupo->ativo) {
            $grupo->ativo = true;
        }

        $grupo->save();

        return (int) $grupo->id;
    }
}
