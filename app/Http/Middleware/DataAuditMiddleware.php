<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\EncryptionService;

class DataAuditMiddleware
{
    protected EncryptionService $encryptionService;

    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Log de auditoria para operações sensíveis
        if ($this->shouldAudit($request)) {
            $this->logAudit($request, $response);
        }

        return $response;
    }

    /**
     * Verifica se a requisição deve ser auditada
     */
    protected function shouldAudit(Request $request): bool
    {
        $sensitiveEndpoints = [
            'pacientes' => ['POST', 'PUT', 'DELETE'],
            'anamneses' => ['POST', 'PUT', 'DELETE'],
            'schedulings' => ['POST', 'PUT', 'DELETE']
        ];

        $path = $request->path();
        $method = $request->method();

        foreach ($sensitiveEndpoints as $endpoint => $methods) {
            if (str_contains($path, $endpoint) && in_array($method, $methods)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Registra log de auditoria
     */
    protected function logAudit(Request $request, $response): void
    {
        $responseData = $this->extractResponseData($response);

        $auditData = [
            'timestamp' => now()->toISOString(),
            'user_id' => Auth::id(),
            'user_email' => Auth::user()?->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'endpoint' => $request->path(),
            'status_code' => $response->getStatusCode(),
            'request_data' => $this->sanitizeRequestData($request->all()),
            'response_data' => $this->sanitizeResponseData($responseData),
            'audit_token' => $this->encryptionService->generateAuditToken()
        ];

        Log::channel('audit')->info('Data Access Audit', $auditData);
    }

    /**
     * Extrai dados da resposta de forma segura para auditoria.
     */
    protected function extractResponseData($response): array
    {
        try {
            if ($response instanceof JsonResponse) {
                $data = $response->getData(true);
                return is_array($data) ? $data : ['data' => $data];
            }

            if (method_exists($response, 'getOriginalContent')) {
                $original = $response->getOriginalContent();
                if (is_array($original)) {
                    return $original;
                }
                if (is_object($original)) {
                    return json_decode(json_encode($original), true) ?: ['data' => 'Object response'];
                }
                if (is_string($original) && $original !== '') {
                    return ['message' => $original];
                }
            }

            return ['data' => 'Non-JSON response'];
        } catch (\Throwable $e) {
            return ['data' => 'Response extraction failed'];
        }
    }

    /**
     * Remove dados sensíveis do request para auditoria
     */
    protected function sanitizeRequestData(array $data): array
    {
        $sensitiveFields = [
            'password', 'password_confirmation', 'cpf_cnpj', 'cpf_responsavel',
            'telefone', 'celular', 'email', 'rua', 'numero', 'complemento',
            'bairro', 'cep'
        ];

        return $this->maskSensitiveFields($data, $sensitiveFields);
    }

    /**
     * Remove dados sensíveis da response para auditoria
     */
    protected function sanitizeResponseData($data): array
    {
        if (is_object($data)) {
            $data = json_decode(json_encode($data), true);
        }

        if (!is_array($data)) {
            return ['data' => 'Non-array response'];
        }

        $sensitiveFields = [
            'password', 'cpf_cnpj', 'cpf_responsavel', 'telefone', 'celular',
            'email', 'rua', 'numero', 'complemento', 'bairro', 'cep'
        ];

        return $this->maskSensitiveFields($data, $sensitiveFields);
    }

    /**
     * Mascara campos sensíveis
     */
    protected function maskSensitiveFields(array $data, array $sensitiveFields): array
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $sensitiveFields)) {
                $data[$key] = '***MASKED***';
            } elseif (is_array($value)) {
                $data[$key] = $this->maskSensitiveFields($value, $sensitiveFields);
            }
        }

        return $data;
    }
}
