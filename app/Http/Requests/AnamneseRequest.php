<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnamneseRequest extends FormRequest
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
            'codigo'          => 'nullable|string|max:20',
            'name'            => 'required|string|max:255',
            'group'           => 'required|string|max:255',
            'description'     => 'nullable|string|max:500',
            'tipo_resposta'   => 'nullable|string|max:100',
            'obrigatorio'     => 'sometimes|boolean',
            'opcoes_resposta' => 'nullable',
            'ativo'           => 'sometimes|boolean',
        ];
    }
}
