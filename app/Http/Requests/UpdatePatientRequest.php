<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Permite a atualização, ajuste conforme sua lógica de autenticação/autorização
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'convenio' => ['nullable', 'string', 'max:100'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'idade' => ['nullable', 'integer', 'min:0'],
            'data_nascimento' => ['nullable', 'date_format:d/m/Y'],
            'responsavel' => ['nullable', 'string', 'max:255'],
            'cpf_responsavel' => ['nullable', 'string', 'max:20'],
            'celular' => ['nullable', 'string', 'max:20'],
            'estado' => ['nullable', 'string', 'max:2'],
            'sexo' => ['nullable', 'string', 'max:20'],
            'profissao' => ['nullable', 'string', 'max:100'],
            'estado_civil' => ['nullable', 'string', 'max:20'],
            'tipo_sanguineo' => ['nullable', 'string', 'max:5'],
            'pessoa' => ['nullable', 'string', 'max:20'],
            'cpf_cnpj' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'cep' => ['nullable', 'string', 'max:10'],
            'rua' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:100'],
            'bairro' => ['nullable', 'string', 'max:100'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
