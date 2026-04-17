<?php

namespace App\Http\Controllers\Helpdesk;

use App\Models\Helpdesk\HelpdeskPreRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class HelpdeskPreRegistrationController extends AbstractHelpdeskCrudController
{
    protected function modelClass(): string { return HelpdeskPreRegistration::class; }

    protected function searchableFields(): array
    {
        return ['nome', 'codigo', 'email', 'empresa'];
    }

    protected function filterableFields(): array
    {
        return ['status', 'tipo'];
    }

    protected function validationRules(bool $isUpdate = false, ?int $id = null): array
    {
        $required = $isUpdate ? 'sometimes' : 'required';
        return [
            'area_id' => ['nullable', 'integer', 'exists:helpdesk_areas,id'],
            'nome' => [$required, 'string', 'max:180'],
            'codigo' => [$required, 'string', 'max:120', 'unique:helpdesk_pre_registrations,codigo' . ($id ? ',' . $id : '')],
            'tipo' => [$required, 'string', 'in:tecnico,cliente'],
            'email' => [$required, 'email', 'max:180', 'unique:helpdesk_pre_registrations,email' . ($id ? ',' . $id : '')],
            'telefone' => ['nullable', 'string', 'max:30'],
            'empresa' => ['nullable', 'string', 'max:180'],
            'status' => ['nullable', 'string', 'in:pendente,enviado,ativado,cancelado'],
            'observacoes' => ['nullable', 'string'],
        ];
    }

    public function store(Request $request): JsonResponse
    {
        $request->merge([
            'senha_temporaria' => Str::random(10),
            'senha_enviada_em' => now(),
            'status' => 'enviado',
        ]);

        $response = parent::store($request);
        $record = HelpdeskPreRegistration::query()->latest('id')->first();

        if ($record) {
            try {
                Mail::raw("Seu acesso Helpdesk foi pré-cadastrado. Senha temporária: {$record->senha_temporaria}", static function ($message) use ($record): void {
                    $message->to($record->email)->subject('Helpdesk - Acesso temporário');
                });
            } catch (\Throwable) {
                // E-mail best-effort.
            }
        }

        return $response;
    }
}
