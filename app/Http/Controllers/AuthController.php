<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private const ACCOUNT_MAX_ATTEMPTS = 5;
    private const IP_MAX_ATTEMPTS = 20;
    private const LOCK_SECONDS = 300;

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
            'device_name' => ['sometimes', 'string', 'max:255'],
        ]);

        $accountKey = strtolower($validated['login']) . '|' . $request->ip();
        $ipKey = 'ip:' . $request->ip();

        if (RateLimiter::tooManyAttempts($accountKey, self::ACCOUNT_MAX_ATTEMPTS) || RateLimiter::tooManyAttempts($ipKey, self::IP_MAX_ATTEMPTS)) {
            $retryAfter = max(
                RateLimiter::availableIn($accountKey),
                RateLimiter::availableIn($ipKey)
            );

            throw ValidationException::withMessages([
                'login' => ["Muitas tentativas. Tente novamente em {$retryAfter} segundos."],
            ]);
        }

        $user = User::with(['grupoAcesso' => function ($query) {
            $query->with(['acessos' => function ($acessosQuery) {
                $acessosQuery->where('ativo', true)->select('id', 'codigo', 'grupo_acesso_id');
            }]);
        }])
            ->where('email', $validated['login'])
            ->orWhere('username', $validated['login'])
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            RateLimiter::hit($accountKey, self::LOCK_SECONDS);
            RateLimiter::hit($ipKey, self::LOCK_SECONDS);
            return response()->json(['message' => 'Usuario ou senha incorretos'], 401);
        }

        if (! $user->ativo) {
            return response()->json(['message' => 'Usuario inativo'], 403);
        }

        $isPaciente = $user->tipo === 'paciente';
        $isSaasAdmin = $user->tipo === 'saas_admin';

        if (! $isPaciente && ! $isSaasAdmin && (! $user->grupoAcesso || ! $user->grupoAcesso->ativo)) {
            return response()->json(['message' => 'Usuario sem grupo de acesso ativo'], 403);
        }

        RateLimiter::clear($accountKey);
        RateLimiter::clear($ipKey);

        $user->ultimo_login_em = now();
        $user->save();

        $user->tokens()->delete();
        $token = $user->createToken($validated['device_name'] ?? 'web')->plainTextToken;

        $permissionCodes = [];

        if (! $isPaciente && ! $isSaasAdmin && $user->grupoAcesso) {
            $permissionCodes = $user->grupoAcesso->acessos
                ->pluck('codigo')
                ->filter()
                ->values()
                ->all();

            if (empty($permissionCodes)) {
                $permissionCodes = array_values(array_filter((array) $user->grupoAcesso->permissoes));
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Login realizado com sucesso',
            'token_type' => 'Bearer',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'tipo' => $user->tipo,
                'grupo_acesso_id' => $user->grupo_acesso_id,
                'grupo_acesso_nome' => $user->grupoAcesso?->nome,
                'permissoes' => $permissionCodes,
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['grupoAcesso' => function ($query) {
            $query->with(['acessos' => function ($acessosQuery) {
                $acessosQuery->where('ativo', true)->select('id', 'codigo', 'grupo_acesso_id');
            }]);
        }]);

        $permissionCodes = $user->grupoAcesso?->acessos
            ?->pluck('codigo')
            ->filter()
            ->values()
            ->all() ?? [];

        if (empty($permissionCodes) && $user->grupoAcesso) {
            $permissionCodes = array_values(array_filter((array) $user->grupoAcesso->permissoes));
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'tipo' => $user->tipo,
                'grupo_acesso_id' => $user->grupo_acesso_id,
                'grupo_acesso_nome' => $user->grupoAcesso?->nome,
                'permissoes' => $permissionCodes,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $tokenId = $user?->currentAccessToken()?->id;

        if ($user && $tokenId) {
            $user->tokens()->whereKey($tokenId)->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso',
        ]);
    }
}
