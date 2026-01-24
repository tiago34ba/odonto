<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'sobrenome' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'telefone' => 'nullable|string|max:20',
            'clinica' => 'nullable|string|max:255',
            'especialidade' => 'nullable|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        // Garante que os campos correspondem à migration
        $user = User::create([
            'nome' => $validated['nome'],
            'sobrenome' => $validated['sobrenome'],
            'email' => $validated['email'],
            'telefone' => $validated['telefone'] ?? null,
            'clinica' => $validated['clinica'] ?? null,
            'especialidade' => $validated['especialidade'] ?? null,
            'password' => $validated['password'],
        ]);

        return response()->json(['message' => 'Usuário criado com sucesso!', 'user' => $user], 201);
    }
}
