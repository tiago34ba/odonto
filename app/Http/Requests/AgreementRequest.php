<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgreementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'codigo'              => 'nullable|string|max:20',
            'name'                => 'required|string|max:255',
            'tipo'                => 'nullable|string|max:100',
            'cnpj'                => 'nullable|string|max:18',
            'phone'               => 'nullable|string|max:20',
            'email'               => 'nullable|email|max:255',
            'endereco'            => 'nullable|string|max:255',
            'numero'              => 'nullable|string|max:20',
            'complemento'         => 'nullable|string|max:100',
            'bairro'              => 'nullable|string|max:100',
            'cidade'              => 'nullable|string|max:100',
            'uf'                  => 'nullable|string|max:2',
            'cep'                 => 'nullable|string|max:9',
            'contato_responsavel' => 'nullable|string|max:255',
            'clinic_commission'   => 'nullable|numeric|min:0|max:100',
            'desconto_percentual' => 'nullable|numeric|min:0|max:100',
            'ativo'               => 'sometimes|boolean',
            'observacoes'         => 'nullable|string',
        ];
    }
}
