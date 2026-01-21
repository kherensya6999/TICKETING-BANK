<?php

use Illuminate\Support\Str;

return [
    'driver' => env('SESSION_DRIVER', 'file'),
    'lifetime' => env('SESSION_LIFETIME', 60), // Set pendek (misal 60 menit) via ENV
    'expire_on_close' => true, // SECURITY: Expire saat browser tutup
    'encrypt' => false,
    'files' => storage_path('framework/sessions'),
    'connection' => env('SESSION_CONNECTION'),
    'table' => 'sessions',
    'store' => env('SESSION_STORE'),
    'lottery' => [2, 100],
    'cookie' => env('SESSION_COOKIE', 'banksumut_ticketing_session'),
    'path' => '/',
    'domain' => env('SESSION_DOMAIN'),
    'secure' => env('SESSION_SECURE_COOKIE', false), // Set TRUE di production (HTTPS)
    'http_only' => true, // SECURITY: Prevent XSS access to cookie
    'same_site' => 'lax',
    'partitioned' => false,
];