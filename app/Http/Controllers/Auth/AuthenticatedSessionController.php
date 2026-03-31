<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Redireciona para o dashboard externo (dashboard-odonto)
        $dashboardUrl = env('DASHBOARD_URL', 'http://localhost:3001');
        
        // Opcionalmente, você pode passar parâmetros de autenticação para o dashboard externo
        $user = $request->user();
        $authParams = http_build_query([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'auth_token' => $request->session()->getId(), // ou gerar um token específico
        ]);
        
        $redirectUrl = $dashboardUrl . '?' . $authParams;
        
        return redirect()->to($redirectUrl);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
