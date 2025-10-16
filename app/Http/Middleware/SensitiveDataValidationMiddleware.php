<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

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

        // Validação de CPF/CNPJ
        if (isset($data['cpf_cnpj']) && !empty($data['cpf_cnpj'])) {
            $this->validateCpfCnpj($data['cpf_cnpj']);
        }

        // Validação de CPF do responsável
        if (isset($data['cpf_responsavel']) && !empty($data['cpf_responsavel'])) {
            $this->validateCpf($data['cpf_responsavel']);
        }

        // Validação de email
        if (isset($data['email']) && !empty($data['email'])) {
            $this->validateEmail($data['email']);
        }

        // Validação de telefone
        if (isset($data['telefone']) && !empty($data['telefone'])) {
            $this->validatePhone($data['telefone']);
        }

        if (isset($data['celular']) && !empty($data['celular'])) {
            $this->validatePhone($data['celular']);
        }

        // Validação de CEP
        if (isset($data['cep']) && !empty($data['cep'])) {
            $this->validateCep($data['cep']);
        }
    }

    /**
     * Valida CPF ou CNPJ
     */
    protected function validateCpfCnpj(string $document): void
    {
        $document = preg_replace('/[^0-9]/', '', $document);
        
        if (strlen($document) === 11) {
            $this->validateCpf($document);
        } elseif (strlen($document) === 14) {
            $this->validateCnpj($document);
        } else {
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
            $sum += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $remainder = $sum % 11;
        $cnpj[12] = ($remainder < 2) ? 0 : 11 - $remainder;

        for ($i = 0, $j = 6, $sum = 0; $i < 13; $i++) {
            $sum += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $remainder = $sum % 11;
        $cnpj[13] = ($remainder < 2) ? 0 : 11 - $remainder;

        if ($cnpj[12] != $cnpj[12] || $cnpj[13] != $cnpj[13]) {
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
