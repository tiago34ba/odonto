<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
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
            'name'         => 'required|string|max:255',
            'razao_social' => 'nullable|string|max:255',
            'email'        => 'required|email|max:255',
            'phone'        => 'required|string|max:20',
            'cnpj'         => 'required|string|max:20',
            'tipo'         => 'nullable|string|max:100',
            'categoria'    => 'nullable|string|max:150',
            'contato'      => 'nullable|string|max:255',
            'status'       => 'nullable|integer|in:0,1,2',
            'avaliacao'    => 'nullable|numeric|min:0|max:5',
            'pix'          => 'nullable|string|max:100',
            'pix_key_type' => 'nullable|string|max:50',
            'street'       => 'required|string|max:255',
            'number'       => 'required|string|max:20',
            'complement'   => 'nullable|string|max:100',
            'neighborhood' => 'required|string|max:100',
            'city'         => 'required|string|max:100',
            'state'        => 'required|string|max:50',
            'cep'          => 'required|string|max:20',
        ];
    }

    protected function prepareForValidation(): void
    {
        $status = $this->input('status');

        if (is_string($status)) {
            $statusMap = [
                'ativo' => 1,
                'inativo' => 0,
                'pendente' => 2,
            ];

            $normalizedStatus = $statusMap[strtolower(trim($status))] ?? $status;
            $this->merge(['status' => $normalizedStatus]);
        }

        if ($this->has('avaliacao')) {
            $avaliacao = $this->input('avaliacao');
            if ($avaliacao === '' || $avaliacao === null) {
                $this->merge(['avaliacao' => 0]);
            }
        }
    }
}
