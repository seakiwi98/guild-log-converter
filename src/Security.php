<?php

namespace LogConv;

class Security
{
    public static function sendHeaders()
    {
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: no-referrer');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
        header('Cross-Origin-Opener-Policy: same-origin');
        header('Cross-Origin-Resource-Policy: same-origin');

        /*
         * Keep CDN entries because Bootstrap and Google Fonts are loaded remotely.
         * If you self-host those assets later, this CSP can be tightened further.
         */
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

    public static function denyDirectAccess()
    {
        if (!defined('APP_BOOTSTRAPPED')) {
            http_response_code(404);
            exit;
        }
    }

    public static function requireMethod(array $methods)
    {
        if (!isset($_SERVER['REQUEST_METHOD']) || !in_array($_SERVER['REQUEST_METHOD'], $methods, true)) {
            http_response_code(405);
            header('Allow: ' . implode(', ', $methods));
            exit;
        }
    }

    public static function isValidResultId($id)
    {
        return is_string($id) && preg_match('/^[a-f0-9]{32,40}$/', $id);
    }

    public static function safeUploadedFileName($fileName)
    {
        $fileName = basename((string) $fileName);
        $fileName = preg_replace('/[^a-zA-Z0-9._ -]/', '_', $fileName);

        if ($fileName === '' || $fileName === '.' || $fileName === '..') {
            return 'uploaded-log.txt';
        }

        return $fileName;
    }
}