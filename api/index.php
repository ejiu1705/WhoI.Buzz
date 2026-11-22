<?php

// Entry point Vercel Serverless Runtime (PHP 8.5 via vercel-php)
// Pastikan direktori tulis sementara (/tmp) tersedia karena filesystem serverless bersifat read-only.
$tmpDirs = [
    '/tmp/storage/app/private',
    '/tmp/storage/app/private_vault',
    '/tmp/storage/app/public',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/logs',
];

foreach ($tmpDirs as $dir) {
    if (! is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Normalisasi SCRIPT_NAME agar Symfony/Laravel tidak memotong prefix /api pada rute REST API /api/v1/*
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

require __DIR__.'/../public/index.php';
