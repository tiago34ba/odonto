<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255', 'required_without:nome'],
            'nome' => ['nullable', 'string', 'max:255', 'required_without:name'],
            'sobrenome' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'clinica' => ['nullable', 'string', 'max:255'],
            'especialidade' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'nivel' => ['nullable', 'string', 'in:Secretaria,Secretária,Auxiliar Dentista,Dentista,Faxineiro,Financeiro,Administrador'],
            'grupo_acesso_id' => ['nullable', 'integer', 'exists:grupo_acessos,id', 'required_without:nivel'],
            'ativo' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('nome') && ! $this->has('name')) {
            $fullName = trim((string) $this->input('nome') . ' ' . (string) $this->input('sobrenome'));
            $this->merge(['name' => $fullName]);
        }
    }
}
