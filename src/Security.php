<?php

declare(strict_types=1);

namespace LogConv;

final class Security
{
    public static function sendHeaders(): void
    {
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: no-referrer');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
        header('Cross-Origin-Opener-Policy: same-origin');
        header('Cross-Origin-Resource-Policy: same-origin');

        header(
            "Content-Security-Policy: " .
            "default-src 'self'; " .
            "base-uri 'self'; " .
            "form-action 'self'; " .
            "frame-ancestors 'none'; " .
            "object-src 'none'; " .
            "img-src 'self' data:; " .
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; " .
            "font-src 'self' https://fonts.gstatic.com; " .
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net"
        );
    }

    public static function denyDirectAccess(): void
    {
        if (!defined('APP_BOOTSTRAPPED')) {
            http_response_code(404);
            exit;
        }
    }

    public static function requireMethod(array $methods): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? '';

        if (!in_array($requestMethod, $methods, true)) {
            http_response_code(405);
            header('Allow: ' . implode(', ', $methods));
            exit;
        }
    }

    public static function isValidResultId(?string $id): bool
    {
        return is_string($id) && preg_match('/^[a-f0-9]{32,40}$/', $id) === 1;
    }

    public static function safeUploadedFileName(string $fileName): string
    {
        $fileName = basename($fileName);
        $fileName = preg_replace('/[^a-zA-Z0-9._ -]/', '_', $fileName) ?? '';

        if ($fileName === '' || $fileName === '.' || $fileName === '..') {
            return 'uploaded-log.txt';
        }

        return $fileName;
    }
}