<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('usuario')?->id ?? $this->route('usuario');

        return [
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'nome' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sobrenome' => ['sometimes', 'nullable', 'string', 'max:255'],
            'username' => ['sometimes', 'nullable', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($userId)],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'telefone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'clinica' => ['sometimes', 'nullable', 'string', 'max:255'],
            'especialidade' => ['sometimes', 'nullable', 'string', 'max:255'],
            'password' => ['sometimes', 'nullable', 'string', 'min:8'],
            'nivel' => ['sometimes', 'nullable', 'string', 'in:Secretaria,Secretária,Auxiliar Dentista,Dentista,Faxineiro,Financeiro,Administrador'],
            'grupo_acesso_id' => ['sometimes', 'nullable', 'integer', 'exists:grupo_acessos,id'],
            'ativo' => ['sometimes', 'boolean'],
        ];
    }
}
