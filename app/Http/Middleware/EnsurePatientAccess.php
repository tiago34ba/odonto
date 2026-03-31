<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePatientAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        if ($user->tipo !== 'paciente') {
            return response()->json(['message' => 'Acesso restrito ao portal do paciente.'], 403);
        }

        if (! $user->ativo) {
            return response()->json(['message' => 'Conta inativa. Entre em contato com a clínica.'], 403);
        }

        return $next($request);
    }
}
