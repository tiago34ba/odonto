<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
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
            'telefone' => preg_replace('/\D/', '', (string) ($this->input('telefone') ?? $this->input('phone') ?? '')),
            'celular' => preg_replace('/\D/', '', (string) ($this->input('celular') ?? $this->input('telefone2') ?? '')),
            'cpf_cnpj' => preg_replace('/\D/', '', (string) ($this->input('cpf_cnpj') ?? $this->input('cpfCnpj') ?? '')),
            'cpf_responsavel' => preg_replace('/\D/', '', (string) ($this->input('cpf_responsavel') ?? $this->input('cpfResponsavel') ?? '')),
            'cep' => preg_replace('/\D/', '', (string) ($this->input('cep') ?? '')),
            'convenio' => $this->input('convenio') ?? $this->input('insurance'),
            'idade' => $this->input('idade') ?? $this->input('age'),
            'data_nascimento' => $normalizeDate($this->input('data_nascimento') ?? $this->input('nascimento')),
            'estado_civil' => $this->input('estado_civil') ?? $this->input('estadoCivil'),
            'tipo_sanguineo' => $this->input('tipo_sanguineo') ?? $this->input('tipoSanguineo'),
            'pessoa' => str_replace(['Juridica', 'Fisica'], ['Jurídica', 'Física'], (string) ($this->input('pessoa') ?? '')),
        ]);
    }

    public function authorize(): bool
    {
        return true; // Permitir todas as requisições
    }

    public function rules(): array
    {
        return [
            'id' => 'nullable|integer',
            'name' => 'required|string|max:255',

            // Compatibilidade com payload antigo e atual
            'phone' => 'nullable|string|max:20',
            'telefone' => 'nullable|string|max:20',
            'insurance' => 'nullable|string|max:255',
            'convenio' => 'nullable|string|max:255',
            'age' => 'nullable|integer|min:0',
            'idade' => 'nullable|integer|min:0',
            'nascimento' => 'nullable|date',
            'data_nascimento' => 'nullable|date',

            'responsavel' => 'nullable|string|max:255',
            'cpfResponsavel' => 'nullable|string|max:14',
            'cpf_responsavel' => 'nullable|string|max:14',
            'pessoa' => 'nullable|string|in:Física,Jurídica,Fisica,Juridica',
            'cpfCnpj' => 'nullable|string|max:18',
            'cpf_cnpj' => 'nullable|string|max:18|unique:pacientes,cpf_cnpj',
            'email' => 'nullable|email|max:255|unique:pacientes,email',
            'cep' => 'nullable|string|max:9',
            'rua' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:10',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:2',
            'tipoSanguineo' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'tipo_sanguineo' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'sexo' => 'nullable|string|in:Masculino,Feminino,Outro',
            'profissao' => 'nullable|string|max:255',
            'estadoCivil' => 'nullable|string|in:Solteiro(a),Casado(a),Divorciado(a),Viúvo(a),Outro',
            'estado_civil' => 'nullable|string|in:Solteiro(a),Casado(a),Divorciado(a),Viúvo(a),Outro',
            'telefone2' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'observacoes' => 'nullable|string|max:1000',
        ];
    }
}
