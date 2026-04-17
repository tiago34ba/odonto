<?php

namespace App\Services;

use App\Models\Dentista;
use App\Models\Employee;
use App\Models\Funcionario;

class PortalDentistSyncService
{
    /**
     * @return array{synced:int,deactivated:int}
     */
    public function syncAll(int $chunkSize = 300): array
    {
        $synced = 0;
        $deactivated = 0;

        Funcionario::query()
            ->with('cargo')
            ->chunkById($chunkSize, function ($funcionarios) use (&$synced, &$deactivated): void {
                foreach ($funcionarios as $funcionario) {
                    $result = $this->syncFuncionario($funcionario);
                    if ($result === 'synced') {
                        $synced++;
                    }
                    if ($result === 'deactivated') {
                        $deactivated++;
                    }
                }
            });

        return [
            'synced' => $synced,
            'deactivated' => $deactivated,
        ];
    }

    public function syncFuncionario(Funcionario $funcionario, ?string $originalEmail = null): string
    {
        $funcionario->loadMissing('cargo');

        $currentEmail = $funcionario->email;
        $lookupEmail = $originalEmail ?: $currentEmail;
        $portalEmployee = null;

        if ($lookupEmail) {
            $portalEmployee = Employee::query()->where('email', $lookupEmail)->first();
        }

        if (! $portalEmployee && $currentEmail) {
            $portalEmployee = Employee::query()->where('email', $currentEmail)->first();
        }

        if (! $this->shouldBeVisibleInPortal($funcionario)) {
            if ($portalEmployee) {
                $portalEmployee->update(['active' => false]);
                return 'deactivated';
            }

            return 'ignored';
        }

        $payload = [
            'name' => $funcionario->name,
            'phone' => $funcionario->telefone ?: '00000000000',
            'email' => $currentEmail,
            'role' => 'dentist',
            'photo' => $funcionario->foto,
            'cro' => $funcionario->cro,
            'specialty' => $this->resolvePortalSpecialty($funcionario->cargo?->nome),
            'active' => (bool) $funcionario->status,
            'address' => $this->formatAddress($funcionario),
            'commission_rate' => $funcionario->comissao,
            'hire_date' => $funcionario->data_cadastro,
        ];

        if ($portalEmployee) {
            $portalEmployee->update($payload);
            return 'synced';
        }

        Employee::create($payload);
        return 'synced';
    }

    public function deactivateByEmail(?string $email): bool
    {
        if (! $email) {
            return false;
        }

        return Employee::query()->where('email', $email)->update(['active' => false]) > 0;
    }

    public function syncDentista(Dentista $dentista, ?string $originalEmail = null, ?string $originalCro = null): string
    {
        $currentEmail = $this->resolveDentistaPortalEmail($dentista);
        $lookupEmail = $originalEmail ?: $currentEmail;
        $lookupCro = trim((string) ($originalCro ?: $dentista->cro));

        $portalEmployee = null;

        if ($lookupEmail !== '') {
            $portalEmployee = Employee::query()->where('email', $lookupEmail)->first();
        }

        if (! $portalEmployee && $lookupCro !== '') {
            $portalEmployee = Employee::query()->where('cro', $lookupCro)->first();
        }

        if (! $portalEmployee && trim((string) $dentista->email) !== '') {
            $portalEmployee = Employee::query()->where('email', trim((string) $dentista->email))->first();
        }

        $payload = [
            'name' => $dentista->name,
            'phone' => $dentista->celular ?: ($dentista->telefone ?: '00000000000'),
            'email' => $currentEmail,
            'role' => 'dentist',
            'photo' => null,
            'cro' => $dentista->cro,
            'specialty' => $dentista->especialidade ?: 'Clínico Geral',
            'active' => (bool) $dentista->status,
            'address' => $this->formatAddressDentista($dentista),
            'hire_date' => $dentista->data_cadastro,
        ];

        if ($portalEmployee) {
            $portalEmployee->update($payload);
            return 'synced';
        }

        Employee::create($payload);
        return 'synced';
    }

    public function deactivateDentista(Dentista $dentista): bool
    {
        $email = trim((string) $dentista->email);
        $cro = trim((string) $dentista->cro);

        $query = Employee::query()->where('role', 'dentist');

        if ($email !== '') {
            $query->where('email', $email);
        } elseif ($cro !== '') {
            $query->where('cro', $cro);
        } else {
            $query->where('name', $dentista->name);
        }

        return $query->update(['active' => false]) > 0;
    }

    private function shouldBeVisibleInPortal(Funcionario $funcionario): bool
    {
        if (! $funcionario->status || ! $funcionario->email) {
            return false;
        }

        $cargoNome = mb_strtolower(trim((string) $funcionario->cargo?->nome));
        $hasCro = trim((string) $funcionario->cro) !== '';

        if ($hasCro) {
            return true;
        }

        return str_contains($cargoNome, 'dent')
            || str_contains($cargoNome, 'odont')
            || str_contains($cargoNome, 'ortodont')
            || str_contains($cargoNome, 'endodont')
            || str_contains($cargoNome, 'periodont')
            || str_contains($cargoNome, 'implantodont')
            || str_contains($cargoNome, 'bucomaxilo');
    }

    private function resolvePortalSpecialty(?string $cargoNome): string
    {
        $normalized = mb_strtolower(trim((string) $cargoNome));

        if ($normalized === '') {
            return 'Clínico Geral';
        }

        return match (true) {
            str_contains($normalized, 'ortodont') => 'Ortodontia',
            str_contains($normalized, 'endodont') => 'Endodontia',
            str_contains($normalized, 'periodont') => 'Periodontia',
            str_contains($normalized, 'implantodont') => 'Implantodontia',
            str_contains($normalized, 'bucomaxilo'), str_contains($normalized, 'cirurg') => 'Cirurgia Oral',
            str_contains($normalized, 'odontopedi') => 'Odontopediatria',
            str_contains($normalized, 'prótese'), str_contains($normalized, 'protese') => 'Prótese',
            default => 'Clínico Geral',
        };
    }

    private function formatAddress(Funcionario $funcionario): ?string
    {
        $parts = array_filter([
            $funcionario->rua,
            $funcionario->numero,
            $funcionario->complemento,
            $funcionario->bairro,
            $funcionario->cidade,
            $funcionario->estado,
            $funcionario->cep,
        ], static fn ($value) => filled($value));

        return $parts === [] ? null : implode(', ', $parts);
    }

    private function formatAddressDentista(Dentista $dentista): ?string
    {
        $parts = array_filter([
            $dentista->rua,
            $dentista->numero,
            $dentista->complemento,
            $dentista->bairro,
            $dentista->cidade,
            $dentista->estado,
            $dentista->cep,
        ], static fn ($value) => filled($value));

        return $parts === [] ? null : implode(', ', $parts);
    }

    private function resolveDentistaPortalEmail(Dentista $dentista): string
    {
        $email = trim((string) $dentista->email);
        if ($email !== '') {
            return $email;
        }

        $croSlug = mb_strtolower(preg_replace('/[^a-zA-Z0-9]+/', '.', (string) $dentista->cro) ?? 'dentista');
        $croSlug = trim($croSlug, '.');
        if ($croSlug === '') {
            $croSlug = 'dentista.' . $dentista->id;
        }

        return $croSlug . '@portal.odonto.local';
    }
}
