<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EncryptionService
{
    /**
     * Chave de criptografia para dados sensíveis
     */
    private string $encryptionKey;

    public function __construct()
    {
        $this->encryptionKey = config('app.encryption_key', 'default-key-change-in-production');
    }

    /**
     * Criptografa dados sensíveis - DESABILITADO
     */
    public function encryptSensitiveData(string $data): string
    {
        if (empty($data)) {
            return '';
        }

        // Retorna os dados sem criptografia
        return $data;

        /* CÓDIGO ORIGINAL COMENTADO
        try {
            return Crypt::encryptString($data);
        } catch (\Exception $e) {
            Log::error('Erro ao criptografar dados sensíveis: ' . $e->getMessage());
            return $data; // Retorna o dado original em caso de erro
        }
        */
    }

    /**
     * Descriptografa dados sensíveis - DESABILITADO
     */
    public function decryptSensitiveData(string $encryptedData): string
    {
        if (empty($encryptedData)) {
            return '';
        }

        // Retorna os dados como estão (sem descriptografia)
        return $encryptedData;

        /* CÓDIGO ORIGINAL COMENTADO
        try {
            return Crypt::decryptString($encryptedData);
        } catch (\Exception $e) {
            Log::error('Erro ao descriptografar dados sensíveis: ' . $e->getMessage());
            return $encryptedData; // Retorna o dado criptografado em caso de erro
        }
        */
    }

    /**
     * Criptografa dados de paciente conforme LGPD - DESABILITADO
     */
    public function encryptPatientData(array $patientData): array
    {
        // Retorna os dados sem criptografar
        return $patientData;

        /* CÓDIGO ORIGINAL COMENTADO
        $sensitiveFields = [
            'cpf_cnpj', 'cpf_responsavel', 'telefone', 'celular',
            'email', 'rua', 'numero', 'complemento', 'bairro', 'cep'
        ];

        $encryptedData = $patientData;

        foreach ($sensitiveFields as $field) {
            if (isset($encryptedData[$field]) && !empty($encryptedData[$field])) {
                $encryptedData[$field] = $this->encryptSensitiveData($encryptedData[$field]);
            }
        }

        return $encryptedData;
        */
    }

    /**
     * Descriptografa dados de paciente - DESABILITADO
     */
    public function decryptPatientData(array $patientData): array
    {
        // Retorna os dados como estão (sem descriptografar)
        return $patientData;

        /* CÓDIGO ORIGINAL COMENTADO
        $sensitiveFields = [
            'cpf_cnpj', 'cpf_responsavel', 'telefone', 'celular',
            'email', 'rua', 'numero', 'complemento', 'bairro', 'cep'
        ];

        $decryptedData = $patientData;

        foreach ($sensitiveFields as $field) {
            if (isset($decryptedData[$field]) && !empty($decryptedData[$field])) {
                $decryptedData[$field] = $this->decryptSensitiveData($decryptedData[$field]);
            }
        }

        return $decryptedData;
        */
    }

    /**
     * Mascara dados sensíveis para exibição
     */
    public function maskSensitiveData(string $data, string $type): string
    {
        if (empty($data)) {
            return '';
        }

        switch ($type) {
            case 'cpf':
                return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.***.***-$4', $data);
            case 'cnpj':
                return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.***.***/****-$5', $data);
            case 'phone':
                return preg_replace('/(\d{2})(\d{4,5})(\d{4})/', '($1) ****-$3', $data);
            case 'email':
                $parts = explode('@', $data);
                if (count($parts) === 2) {
                    return substr($parts[0], 0, 2) . '***@' . $parts[1];
                }
                return $data;
            case 'cep':
                return preg_replace('/(\d{5})(\d{3})/', '$1-***', $data);
            case 'text':
                if (strlen($data) <= 3) return $data;
                if (strlen($data) <= 6) {
                    return substr($data, 0, 2) . '***' . substr($data, -1);
                }
                return substr($data, 0, 3) . '***' . substr($data, -3);
            default:
                return $data;
        }
    }

    /**
     * Gera hash para auditoria de dados
     */
    public function generateDataHash(array $data): string
    {
        ksort($data);
        $dataString = json_encode($data);
        return hash('sha256', $dataString);
    }

    /**
     * Verifica integridade dos dados
     */
    public function verifyDataIntegrity(array $data, string $hash): bool
    {
        $currentHash = $this->generateDataHash($data);
        return hash_equals($currentHash, $hash);
    }

    /**
     * Gera token seguro para auditoria
     */
    public function generateAuditToken(): string
    {
        return Str::random(32);
    }

    /**
     * Valida se os dados estão criptografados - DESABILITADO
     */
    public function isEncrypted(string $data): bool
    {
        // Sempre retorna false pois não há mais criptografia
        return false;

        /* CÓDIGO ORIGINAL COMENTADO
        try {
            $this->decryptSensitiveData($data);
            return true;
        } catch (\Exception $e) {
            return false;
        }
        */
    }
}
