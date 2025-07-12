<?php

return [
    /*
    |--------------------------------------------------------------------------
    | QR Code Generator Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the QR Code Generator Service.
    | You can customize various aspects of QR code generation here.
    |
    */

    'cache' => [
        'ttl' => env('QR_CACHE_TTL', 3600), // Cache TTL in seconds (default: 1 hour)
        'key_prefix' => 'qr_code:',
        'enabled' => env('QR_CACHE_ENABLED', true),
    ],

    'limits' => [
        'max_content_length' => env('QR_MAX_CONTENT_LENGTH', 4296),
        'min_size' => 50,
        'max_size' => 2000,
        'default_size' => env('QR_DEFAULT_SIZE', 300),
    ],

    'defaults' => [
        'file_type' => 'png',
        'output_type' => 'base64',
        'dot_style' => 'square',
        'color' => '#000000',
        'background' => '#ffffff',
        'error_correction' => 'M',
    ],

    'storage' => [
        'disk' => env('QR_STORAGE_DISK', 'public'),
        'path' => 'qr-codes',
        'cleanup_days' => env('QR_STORAGE_CLEANUP_DAYS', 30),
        'auto_cleanup' => env('QR_STORAGE_AUTO_CLEANUP', true),
    ],

    'performance' => [
        'target_rps' => 1000, // Target requests per second
        'memory_limit' => '512M',
        'max_execution_time' => 30,
    ],

    'supported_formats' => [
        'png' => [
            'mime_type' => 'image/png',
            'extension' => 'png',
            'raster' => true,
        ],
        'jpg' => [
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'raster' => true,
        ],
        'svg' => [
            'mime_type' => 'image/svg+xml',
            'extension' => 'svg',
            'raster' => false,
        ],
        'pdf' => [
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'raster' => false,
        ],
    ],

    'validation' => [
        'strict_mode' => env('QR_STRICT_VALIDATION', true),
        'allowed_schemes' => ['http', 'https', 'mailto', 'tel', 'sms'],
        'max_url_length' => 2048,
    ],

    'monitoring' => [
        'log_generation_time' => env('QR_LOG_GENERATION_TIME', true),
        'log_cache_hits' => env('QR_LOG_CACHE_HITS', true),
        'log_errors' => env('QR_LOG_ERRORS', true),
    ],
];