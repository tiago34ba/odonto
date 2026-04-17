<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDentistaRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $normalizeDate = static function (?string $value): ?string {
            if (! $value) {
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

        $horarios = $this->input('horarios_atendimento', []);
        if (! is_array($horarios)) {
            $horarios = [];
        }

        $normalizedHorarios = array_map(static function ($item) {
            if (! is_array($item)) {
                return [];
            }

            return [
                'dia_semana' => strtolower((string) ($item['dia_semana'] ?? '')),
                'ativo' => filter_var($item['ativo'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'hora_inicio' => (string) ($item['hora_inicio'] ?? ''),
                'hora_fim' => (string) ($item['hora_fim'] ?? ''),
            ];
        }, $horarios);

        $this->merge([
            'telefone' => preg_replace('/\D/', '', (string) ($this->input('telefone') ?? '')),
            'celular' => preg_replace('/\D/', '', (string) ($this->input('celular') ?? '')),
            'cpf' => preg_replace('/\D/', '', (string) ($this->input('cpf') ?? '')),
            'cep' => preg_replace('/\D/', '', (string) ($this->input('cep') ?? '')),
            'cro_uf' => strtoupper((string) ($this->input('cro_uf') ?? '')),
            'estado' => strtoupper((string) ($this->input('estado') ?? '')),
            'data_nascimento' => $normalizeDate($this->input('data_nascimento')),
            'data_cadastro' => $normalizeDate($this->input('data_cadastro')),
            'intervalo_consulta' => $this->input('intervalo_consulta') !== null
                ? (int) $this->input('intervalo_consulta')
                : 30,
            'horarios_atendimento' => $normalizedHorarios,
            'status' => filter_var($this->input('status', true), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rawDentista = $this->route('dentista');
        $dentistaId = is_object($rawDentista) && isset($rawDentista->id)
            ? (int) $rawDentista->id
            : (is_numeric($rawDentista) ? (int) $rawDentista : null);

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('dentistas', 'email')->ignore($dentistaId),
            ],
            'telefone' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'cpf' => [
                'nullable',
                'string',
                'max:14',
                Rule::unique('dentistas', 'cpf')->ignore($dentistaId),
            ],
            'cro' => [
                'required',
                'string',
                'max:20',
                Rule::unique('dentistas', 'cro')->ignore($dentistaId),
            ],
            'cro_uf' => 'required|string|size:2',
            'especialidade' => 'required|string|max:120',
            'data_nascimento' => 'nullable|date',
            'data_cadastro' => 'nullable|date',
            'intervalo_consulta' => 'nullable|integer|min:5|max:240',
            'horarios_atendimento' => 'nullable|array',
            'horarios_atendimento.*.dia_semana' => 'required_with:horarios_atendimento|string|in:segunda,terca,quarta,quinta,sexta,sabado,domingo',
            'horarios_atendimento.*.ativo' => 'required_with:horarios_atendimento|boolean',
            'horarios_atendimento.*.hora_inicio' => 'required_with:horarios_atendimento|string|date_format:H:i',
            'horarios_atendimento.*.hora_fim' => 'required_with:horarios_atendimento|string|date_format:H:i',
            'chave_pix' => 'nullable|string|max:255',
            'cep' => 'nullable|string|max:10',
            'rua' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|size:2',
            'status' => 'nullable|boolean',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $horarios = $this->input('horarios_atendimento', []);

            if (! is_array($horarios)) {
                return;
            }

            foreach ($horarios as $index => $horario) {
                if (! is_array($horario)) {
                    continue;
                }

                $ativo = filter_var($horario['ativo'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $horaInicio = (string) ($horario['hora_inicio'] ?? '');
                $horaFim = (string) ($horario['hora_fim'] ?? '');

                if (! $ativo) {
                    continue;
                }

                if ($horaInicio === '' || $horaFim === '') {
                    $validator->errors()->add(
                        "horarios_atendimento.$index.hora_inicio",
                        'Informe horario inicial e final para os dias ativos.'
                    );
                    continue;
                }

                if ($horaFim <= $horaInicio) {
                    $validator->errors()->add(
                        "horarios_atendimento.$index.hora_fim",
                        'A hora final deve ser maior que a hora inicial para dias ativos.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do dentista e obrigatorio.',
            'cro.required' => 'O CRO e obrigatorio.',
            'cro.unique' => 'O CRO informado ja esta cadastrado.',
            'email.unique' => 'O e-mail informado ja esta cadastrado.',
            'cpf.unique' => 'O CPF informado ja esta cadastrado.',
            'horarios_atendimento.*.hora_inicio.required_with' => 'A hora inicial e obrigatoria.',
            'horarios_atendimento.*.hora_fim.required_with' => 'A hora final e obrigatoria.',
        ];
    }
}
