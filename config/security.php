<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configurações de Segurança da API
    |--------------------------------------------------------------------------
    */

    'encryption' => [
        'key' => env('ENCRYPTION_KEY', 'default-key-change-in-production'),
        'cipher' => 'AES-256-CBC',
    ],

    'rate_limiting' => [
        'enabled' => true,
        'max_attempts' => env('API_RATE_LIMIT', 60),
        'decay_minutes' => env('API_RATE_LIMIT_WINDOW', 1),
    ],

    'lgpd' => [
        'enabled' => env('LGPD_ENCRYPTION_ENABLED', false), // Criptografia desabilitada
        'audit_log' => env('LGPD_AUDIT_LOG', true),
        'data_retention_days' => env('LGPD_DATA_RETENTION_DAYS', 2555), // 7 anos
        'sensitive_fields' => [
            'cpf_cnpj', 'cpf_responsavel', 'telefone', 'celular',
            'email', 'rua', 'numero', 'complemento', 'bairro', 'cep'
        ],
    ],

    'audit' => [
        'enabled' => true,
        'channels' => ['audit'],
        'sensitive_endpoints' => [
            'pacientes' => ['POST', 'PUT', 'DELETE'],
            'anamneses' => ['POST', 'PUT', 'DELETE'],
            'schedulings' => ['POST', 'PUT', 'DELETE'],
        ],
    ],

    'cors' => [
        'allowed_origins' => [
            env('FRONTEND_URL', 'http://localhost:3000'),
            env('DASHBOARD_URL', 'http://localhost:3001'),
        ],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'max_age' => 86400,
    ],

    'headers' => [
        'x_content_type_options' => 'nosniff',
        'x_frame_options' => 'DENY',
        'x_xss_protection' => '1; mode=block',
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'permissions_policy' => 'geolocation=(), microphone=(), camera=()',
    ],

    'backup' => [
        'enabled' => env('BACKUP_ENABLED', true),
        'schedule' => env('BACKUP_SCHEDULE', 'daily'),
        'retention_days' => env('BACKUP_RETENTION_DAYS', 30),
        'encrypt' => true,
    ],
];
