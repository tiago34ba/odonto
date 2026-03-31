<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SensitiveDataValidationMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Validações específicas para dados sensíveis
        if ($this->isSensitiveEndpoint($request)) {
            $this->validateSensitiveData($request);
        }

        return $next($request);
    }

    /**
     * Verifica se é um endpoint que lida com dados sensíveis
     */
    protected function isSensitiveEndpoint(Request $request): bool
    {
        $sensitiveEndpoints = ['pacientes', 'anamneses', 'schedulings'];
        $path = $request->path();

        foreach ($sensitiveEndpoints as $endpoint) {
            if (str_contains($path, $endpoint)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Valida dados sensíveis
     */
    protected function validateSensitiveData(Request $request): void
    {
        $data = $request->all();
        $errors = [];

        // Validação de CPF/CNPJ
        if (isset($data['cpf_cnpj']) && !empty($data['cpf_cnpj'])) {
            try {
                $this->validateCpfCnpj($data['cpf_cnpj']);
            } catch (\InvalidArgumentException $e) {
                $errors['cpf_cnpj'][] = $e->getMessage();
            }
        }

        // CPF do responsável: valida apenas formato (11 dígitos) para não bloquear cadastros de teste
        if (isset($data['cpf_responsavel']) && !empty($data['cpf_responsavel'])) {
            try {
                $this->validateCpfResponsavelFormat($data['cpf_responsavel']);
            } catch (\InvalidArgumentException $e) {
                $errors['cpf_responsavel'][] = $e->getMessage();
            }
        }

        // Validação de email
        if (isset($data['email']) && !empty($data['email'])) {
            try {
                $this->validateEmail($data['email']);
            } catch (\InvalidArgumentException $e) {
                $errors['email'][] = $e->getMessage();
            }
        }

        // Validação de telefone
        if (isset($data['telefone']) && !empty($data['telefone'])) {
            try {
                $this->validatePhone($data['telefone']);
            } catch (\InvalidArgumentException $e) {
                $errors['telefone'][] = $e->getMessage();
            }
        }

        if (isset($data['celular']) && !empty($data['celular'])) {
            try {
                $this->validatePhone($data['celular']);
            } catch (\InvalidArgumentException $e) {
                $errors['celular'][] = $e->getMessage();
            }
        }

        // Validação de CEP
        if (isset($data['cep']) && !empty($data['cep'])) {
            try {
                $this->validateCep($data['cep']);
            } catch (\InvalidArgumentException $e) {
                $errors['cep'][] = $e->getMessage();
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Valida apenas formato do CPF do responsável (11 dígitos).
     */
    protected function validateCpfResponsavelFormat(string $cpf): void
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        if (strlen($cpf) !== 11) {
            throw new \InvalidArgumentException('CPF do responsável deve ter 11 dígitos');
        }
    }

    /**
     * Valida CPF ou CNPJ
     */
    protected function validateCpfCnpj(string $document): void
    {
        $document = preg_replace('/[^0-9]/', '', $document);

        // Validação de formato para permitir dados de teste no cadastro
        if (!in_array(strlen($document), [11, 14], true)) {
            throw new \InvalidArgumentException('CPF deve ter 11 dígitos ou CNPJ deve ter 14 dígitos');
        }
    }

    /**
     * Valida CPF
     */
    protected function validateCpf(string $cpf): void
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        if (strlen($cpf) !== 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            throw new \InvalidArgumentException('CPF inválido');
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                throw new \InvalidArgumentException('CPF inválido');
            }
        }
    }

    /**
     * Valida CNPJ
     */
    protected function validateCnpj(string $cnpj): void
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        if (strlen($cnpj) !== 14 || preg_match('/(\d)\1{13}/', $cnpj)) {
            throw new \InvalidArgumentException('CNPJ inválido');
        }

        for ($i = 0, $j = 5, $sum = 0; $i < 12; $i++) {
            $sum += ((int) $cnpj[$i]) * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $remainder = $sum % 11;
        $digit1 = ($remainder < 2) ? 0 : 11 - $remainder;

        for ($i = 0, $j = 6, $sum = 0; $i < 12; $i++) {
            $sum += ((int) $cnpj[$i]) * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $sum += $digit1 * 2;

        $remainder = $sum % 11;
        $digit2 = ($remainder < 2) ? 0 : 11 - $remainder;

        if (((int) $cnpj[12]) !== $digit1 || ((int) $cnpj[13]) !== $digit2) {
            throw new \InvalidArgumentException('CNPJ inválido');
        }
    }

    /**
     * Valida email
     */
    protected function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Email inválido');
        }
    }

    /**
     * Valida telefone
     */
    protected function validatePhone(string $phone): void
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) < 10 || strlen($phone) > 11) {
            throw new \InvalidArgumentException('Telefone deve ter 10 ou 11 dígitos');
        }
    }

    /**
     * Valida CEP
     */
    protected function validateCep(string $cep): void
    {
        $cep = preg_replace('/[^0-9]/', '', $cep);

        if (strlen($cep) !== 8) {
            throw new \InvalidArgumentException('CEP deve ter 8 dígitos');
        }
    }
}
