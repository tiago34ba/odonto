<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Nao autenticado.'], 401);
        }

        if (($user->tipo ?? null) === 'saas_admin' && (bool) ($user->ativo ?? false)) {
            return $next($request);
        }

        if (! $user->ativo || ! $user->grupoAcesso || ! $user->grupoAcesso->ativo) {
            return response()->json(['message' => 'Usuario sem perfil ativo.'], 403);
        }

        $codes = $this->resolvePermissionCodes($user);
        if (in_array('*', $codes, true)) {
            return $next($request);
        }

        $moduleKeywords = $this->resolveModuleKeywords($request);
        if ($moduleKeywords === []) {
            return $next($request);
        }

        $hasModulePermission = $this->hasAnyModulePermission($codes, $moduleKeywords);

        // Regra de negocio: garantir visibilidade da agenda para perfis operacionais.
        $isSchedulingRead = $this->isSchedulingReadRequest($request);
        if (! $hasModulePermission && $isSchedulingRead && $this->isAllowedSchedulingRole($user->tipo)) {
            return $next($request);
        }

        // Escrita exige permissao explicita do modulo.
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD') && ! $request->isMethod('OPTIONS')) {
            if (! $hasModulePermission) {
                return response()->json(['message' => 'Permissao insuficiente para alterar este modulo.'], 403);
            }

            return $next($request);
        }

        // Leitura exige ao menos permissao geral ou do modulo.
        if (! $hasModulePermission && empty($codes)) {
            return response()->json(['message' => 'Permissao insuficiente para acessar este modulo.'], 403);
        }

        return $next($request);
    }

    private function isSchedulingReadRequest(Request $request): bool
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD') && ! $request->isMethod('OPTIONS')) {
            return false;
        }

        $path = preg_replace('#^api/#', '', (string) $request->path());
        return str_starts_with($path, 'schedulings');
    }

    private function isAllowedSchedulingRole(?string $tipo): bool
    {
        if (! $tipo) {
            return false;
        }

        $normalized = mb_strtolower(trim($tipo));

        return in_array($normalized, [
            'secretaria.odonto',
            'admin',
            'dentista',
            'auxiliar de dentista',
        ], true);
    }

    /**
     * @return array<int, string>
     */
    private function resolvePermissionCodes($user): array
    {
        $accessCodes = $user->grupoAcesso
            ?->acessos()
            ->where('ativo', true)
            ->pluck('codigo')
            ->filter()
            ->map(fn ($code) => mb_strtolower((string) $code))
            ->values()
            ->all() ?? [];

        $legacyPermissions = collect((array) ($user->grupoAcesso?->permissoes ?? []))
            ->filter()
            ->map(fn ($perm) => mb_strtolower((string) $perm))
            ->values()
            ->all();

        return array_values(array_unique(array_merge($accessCodes, $legacyPermissions)));
    }

    /**
     * @return array<int, string>
     */
    private function resolveModuleKeywords(Request $request): array
    {
        $path = preg_replace('#^api/#', '', (string) $request->path());

        $map = [
            'dashboard' => ['dashboard'],
            'pessoas/pacientes' => ['paciente', 'patient'],
            'pessoas/employees' => ['employee', 'funcionario'],
            'pessoas/funcionarios' => ['funcionario', 'employee'],
            'pessoas/usuarios' => ['usuario', 'user'],
            'anamneses' => ['anamnese'],
            'procedures' => ['procedimento', 'procedure'],
            'treatment-plans' => ['orcamento', 'treatment'],
            'schedulings' => ['agendamento', 'scheduling'],
            'agreements' => ['convenio', 'agreement'],
            'groups-anamnese' => ['anamnese', 'grupo'],
            'suppliers' => ['fornecedor', 'supplier'],
            'formas-pagamentos' => ['pagamento', 'payment'],
            'cargos' => ['cargo'],
            'acessos' => ['acesso'],
            'grupos-acesso' => ['grupo', 'acesso'],
            'odontograma' => ['odontograma'],
            'reports' => ['relatorio', 'report'],
            'financeiro' => ['financeiro', 'conta', 'fluxo'],
            'payments' => ['pagamento', 'payment', 'pix', 'cartao'],
        ];

        foreach ($map as $prefix => $keywords) {
            if (str_starts_with($path, $prefix)) {
                return $keywords;
            }
        }

        return [];
    }

    /**
     * @param array<int, string> $codes
     * @param array<int, string> $moduleKeywords
     */
    private function hasAnyModulePermission(array $codes, array $moduleKeywords): bool
    {
        foreach ($codes as $code) {
            foreach ($moduleKeywords as $keyword) {
                if (str_contains($code, $keyword)) {
                    return true;
                }
            }
        }

        return false;
    }
}
