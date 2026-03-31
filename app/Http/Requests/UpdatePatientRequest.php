<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientRequest extends FormRequest
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

        $normalized = [];

        if ($this->hasAny(['telefone', 'phone'])) {
            $normalized['telefone'] = preg_replace('/\D/', '', (string) ($this->input('telefone') ?? $this->input('phone') ?? ''));
        }

        if ($this->hasAny(['celular', 'telefone2'])) {
            $normalized['celular'] = preg_replace('/\D/', '', (string) ($this->input('celular') ?? $this->input('telefone2') ?? ''));
        }

        if ($this->hasAny(['cpf_cnpj', 'cpfCnpj'])) {
            $normalized['cpf_cnpj'] = preg_replace('/\D/', '', (string) ($this->input('cpf_cnpj') ?? $this->input('cpfCnpj') ?? ''));
        }

        if ($this->hasAny(['cpf_responsavel', 'cpfResponsavel'])) {
            $normalized['cpf_responsavel'] = preg_replace('/\D/', '', (string) ($this->input('cpf_responsavel') ?? $this->input('cpfResponsavel') ?? ''));
        }

        if ($this->has('cep')) {
            $normalized['cep'] = preg_replace('/\D/', '', (string) $this->input('cep'));
        }

        if ($this->hasAny(['convenio', 'insurance'])) {
            $normalized['convenio'] = $this->input('convenio') ?? $this->input('insurance');
        }

        if ($this->hasAny(['idade', 'age'])) {
            $normalized['idade'] = $this->input('idade') ?? $this->input('age');
        }

        if ($this->hasAny(['data_nascimento', 'nascimento'])) {
            $normalized['data_nascimento'] = $normalizeDate($this->input('data_nascimento') ?? $this->input('nascimento'));
        }

        if ($this->hasAny(['estado_civil', 'estadoCivil'])) {
            $normalized['estado_civil'] = $this->input('estado_civil') ?? $this->input('estadoCivil');
        }

        if ($this->hasAny(['tipo_sanguineo', 'tipoSanguineo'])) {
            $normalized['tipo_sanguineo'] = $this->input('tipo_sanguineo') ?? $this->input('tipoSanguineo');
        }

        if ($this->has('pessoa')) {
            $normalized['pessoa'] = str_replace(['Juridica', 'Fisica'], ['Jurídica', 'Física'], (string) $this->input('pessoa'));
        }

        if (!empty($normalized)) {
            $this->merge($normalized);
        }
    }

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
        $routeParameters = $this->route()?->parameters() ?? [];
        $rawPatient = $routeParameters['patient']
            ?? $routeParameters['paciente']
            ?? reset($routeParameters)
            ?? null;

        $patientId = null;
        if (is_object($rawPatient) && isset($rawPatient->id)) {
            $patientId = (int) $rawPatient->id;
        } elseif (is_numeric($rawPatient)) {
            $patientId = (int) $rawPatient;
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'convenio' => ['nullable', 'string', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'idade' => ['nullable', 'integer', 'min:0'],
            'data_nascimento' => ['nullable', 'date'],
            'responsavel' => ['nullable', 'string', 'max:255'],
            'cpf_responsavel' => ['nullable', 'string', 'max:14'],
            'celular' => ['nullable', 'string', 'max:20'],
            'estado' => ['nullable', 'string', 'max:2'],
            'sexo' => ['nullable', 'string', Rule::in(['Masculino', 'Feminino', 'Outro'])],
            'profissao' => ['nullable', 'string', 'max:255'],
            'estado_civil' => ['nullable', 'string', Rule::in(['Solteiro(a)', 'Casado(a)', 'Divorciado(a)', 'Viúvo(a)', 'Outro'])],
            'tipo_sanguineo' => ['nullable', 'string', 'max:5'],
            'pessoa' => ['nullable', 'string', Rule::in(['Física', 'Jurídica', 'Fisica', 'Juridica'])],
            'cpf_cnpj' => ['nullable', 'string', 'max:18', Rule::unique('pacientes', 'cpf_cnpj')->ignore($patientId)],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('pacientes', 'email')->ignore($patientId)],
            'cep' => ['nullable', 'string', 'max:9'],
            'rua' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:10'],
            'complemento' => ['nullable', 'string', 'max:255'],
            'bairro' => ['nullable', 'string', 'max:255'],
            'cidade' => ['nullable', 'string', 'max:255'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
