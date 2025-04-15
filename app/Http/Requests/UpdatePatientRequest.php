<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Ou defina sua lógica de autorização aqui
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'convenio' => 'nullable|string|max:255',
            'telefone' => 'nullable|string|max:20',
            'idade' => 'nullable|integer|min:0',
            'data_nascimento' => 'nullable|date',
            'responsavel' => 'nullable|string|max:255',
            'cpf_responsavel' => 'nullable|string|max:14',
            'celular' => 'nullable|string|max:20',
            'estado' => 'nullable|string|max:2',
            'sexo' => 'nullable|string|max:20',
            'profissao' => 'nullable|string|max:255',
            'estado_civil' => 'nullable|string|max:20',
            'tipo_sanguineo' => 'nullable|string|max:3',
            'pessoa' => 'nullable|string|max:10',
            'cpf_cnpj' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'cep' => 'nullable|string|max:10',
            'rua' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:10',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
        ];
    }
}
