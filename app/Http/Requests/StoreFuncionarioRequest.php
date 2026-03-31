<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFuncionarioRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $normalizeDate = static function (?string $value): ?string {
            if (!$value) {
                return $value;
            }
            $value = trim($value);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return $value;
            }
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $value, $matches)) {
                return sprintf('%s-%s-%s', $matches[3], $matches[2], $matches[1]);
            }
            return $value;
        };

        $this->merge([
            'telefone'    => preg_replace('/\D/', '', (string) ($this->input('telefone') ?? $this->input('phone') ?? '')),
            'cep'         => preg_replace('/\D/', '', (string) ($this->input('cep') ?? '')),
            'data_cadastro' => $normalizeDate($this->input('data_cadastro') ?? $this->input('dataCadastro')),
            'cargo_id'    => $this->input('cargo_id') ?? $this->input('cargoId') ?? null,
            'intervalo'   => $this->input('intervalo') !== null ? (int) $this->input('intervalo') : null,
            'comissao'    => $this->input('comissao') !== null ? (float) $this->input('comissao') : null,
            'status'      => filter_var($this->input('status', true), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rawFuncionario = $this->route('funcionario');
        $funcionarioId = is_object($rawFuncionario) && isset($rawFuncionario->id)
            ? (int) $rawFuncionario->id
            : (is_numeric($rawFuncionario) ? (int) $rawFuncionario : null);

        return [
            // Dados pessoais
            'name'         => 'required|string|max:255',
            'telefone'     => 'nullable|string|max:20',
            'phone'        => 'nullable|string|max:20',
            'email'        => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('funcionarios', 'email')->ignore($funcionarioId),
            ],

            // Cargo
            'cargo_id'     => 'nullable|integer|exists:cargos,id',
            'cargoId'      => 'nullable|integer',

            // Data de cadastro
            'data_cadastro'  => 'nullable|date',
            'dataCadastro'   => 'nullable|date',

            // Foto
            'foto'         => 'nullable|string|max:500',

            // Endereço
            'cep'          => 'nullable|string|max:10',
            'rua'          => 'nullable|string|max:255',
            'numero'       => 'nullable|string|max:20',
            'complemento'  => 'nullable|string|max:255',
            'bairro'       => 'nullable|string|max:255',
            'cidade'       => 'nullable|string|max:255',
            'estado'       => 'nullable|string|max:2',

            // Configurações profissionais
            'cro'          => 'nullable|string|max:20',
            'intervalo'    => 'nullable|integer|min:1|max:480',
            'comissao'     => 'nullable|numeric|min:0|max:100',
            'chave_pix'    => 'nullable|string|max:255',
            'chavePix'     => 'nullable|string|max:255',

            // Status
            'status'       => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'O nome do funcionário é obrigatório.',
            'email.unique'     => 'E-mail já cadastrado. Use outro e-mail ou edite o funcionário existente.',
            'email.email'      => 'Informe um e-mail válido.',
            'cargo_id.exists'  => 'Cargo selecionado não existe.',
            'intervalo.min'    => 'O intervalo mínimo é 1 minuto.',
            'intervalo.max'    => 'O intervalo máximo é 480 minutos (8 horas).',
            'comissao.min'     => 'A comissão não pode ser negativa.',
            'comissao.max'     => 'A comissão não pode ultrapassar 100%.',
        ];
    }
}
