<?php
// Basic DB config. Override via env vars.
return [
    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'sheet_music_manager',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: 'tester',
        'charset' => 'utf8mb4',
    ],
    // Allow all origins by default; tighten in production.
    'cors_origin' => getenv('CORS_ORIGIN') ?: '*',
];
?>
