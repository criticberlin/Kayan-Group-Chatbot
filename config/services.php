<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | n8n Integration
    |--------------------------------------------------------------------------
    */

    'n8n' => [
        'webhook_url' => env('N8N_WEBHOOK_URL'),
        'chat_webhook_url' => env('N8N_CHAT_WEBHOOK_URL'),
        'callback_secret' => env('N8N_CALLBACK_SECRET'),
        'timeout' => env('N8N_TIMEOUT', 30),
        'max_retries' => env('N8N_MAX_RETRIES', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAI GPT Configuration
    |--------------------------------------------------------------------------
    */

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'organization' => env('OPENAI_ORGANIZATION'),
        'model' => env('OPENAI_MODEL', 'gpt-4'),
        'max_tokens' => env('OPENAI_MAX_TOKENS', 500),
        'temperature' => env('OPENAI_TEMPERATURE', 0.7),
        'chat_endpoint' => env('OPENAI_CHAT_ENDPOINT', 'https://api.openai.com/v1/chat/completions'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Excel Import Configuration
    |--------------------------------------------------------------------------
    */

    'excel' => [
        'max_file_size' => env('EXCEL_MAX_FILE_SIZE', 10485760), // 10MB in bytes
        'allowed_extensions' => ['xlsx', 'xls', 'csv'],
        'upload_path' => env('EXCEL_UPLOAD_PATH', 'imports'),
        'chunk_size' => env('EXCEL_CHUNK_SIZE', 1000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Document Management Configuration
    |--------------------------------------------------------------------------
    */

    'documents' => [
        'max_file_size' => env('DOCUMENT_MAX_FILE_SIZE', 52428800), // 50MB in bytes
        'allowed_mimes' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/png',
        ],
        'storage_disk' => env('DOCUMENT_STORAGE_DISK', 'documents'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    */

    'features' => [
        'department_isolation' => env('FEATURE_DEPARTMENT_ISOLATION', true),
        'audit_logging' => env('FEATURE_AUDIT_LOGGING', true),
        'chat_enabled' => env('FEATURE_CHAT_ENABLED', true),
        'n8n_integration' => env('FEATURE_N8N_INTEGRATION', true),
        'gpt_caching' => env('FEATURE_GPT_CACHING', false),
    ],

];
