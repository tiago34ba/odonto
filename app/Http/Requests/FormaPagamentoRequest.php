<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FormaPagamentoRequest extends FormRequest
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
            'codigo'            => 'nullable|string|max:20',
            'nome'              => 'required|string|max:255',
            'tipo'              => 'nullable|string|max:100',
            'cor'               => 'nullable|string|max:7',
            'icone'             => 'nullable|string|max:100',
            'taxa'              => 'nullable|numeric|min:0',
            'taxa_juros'        => 'nullable|numeric|min:0',
            'parcelas_max'      => 'nullable|integer|min:1',
            'dias_vencimento'   => 'nullable|integer|min:0',
            'ativo'             => 'sometimes|boolean',
            'aceita_parcelamento' => 'sometimes|boolean',
        ];
    }
}
