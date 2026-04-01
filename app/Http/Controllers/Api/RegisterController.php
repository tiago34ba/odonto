<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GrupoAcesso;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $password = $request->input('password', $request->input('senha'));
        $nome = $request->input('nome', $request->input('name'));
        $sobrenome = $request->input('sobrenome', '');

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:8',
            'senha' => 'nullable|string|min:8',
            'name' => 'nullable|string|max:255',
            'nome' => 'nullable|string|max:255',
            'sobrenome' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (! $password) {
            return response()->json(['message' => 'Senha obrigatoria'], 422);
        }

        $name = trim((string) $request->input('name', trim($nome . ' ' . $sobrenome)));

        if ($name === '') {
            return response()->json(['message' => 'Nome obrigatorio'], 422);
        }

        $user = DB::transaction(function () use ($name, $nome, $sobrenome, $request, $password) {
            // Lock de leitura para evitar corrida e garantir apenas o primeiro cadastro como admin SaaS.
            $isFirstUser = ! User::query()->lockForUpdate()->exists();

            return User::create([
                'name' => $name,
                'nome' => $nome,
                'sobrenome' => $sobrenome,
                'email' => $request->email,
                'password' => Hash::make($password),
                'grupo_acesso_id' => GrupoAcesso::where('ativo', true)->value('id'),
                'tipo' => $isFirstUser ? 'saas_admin' : null,
                'ativo' => true,
            ]);
        });

        return response()->json(['success' => true, 'user' => $user], 201);
    }
}
