<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            // Login realizado com sucesso
            return redirect()->intended('/dashboard');
        } else {
            // Email ou senha incorretos
            return response()->json(['message' => 'Email ou senha incorretos'], 401);
        }
    }
}
