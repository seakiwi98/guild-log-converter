<?php

define('APP_BOOTSTRAPPED', true);

require_once __DIR__ . '/../vendor/autoload.php';

use LogConv\Security;

Security::sendHeaders();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === null) {
    http_response_code(404);
    exit;
}

/*
 * Never allow direct access to internal PHP endpoints.
 * Public users should only access:
 * - /
 * - /{result-id}
 * - static assets
 */
if ($path === '/results.php' || $path === '/router.php') {
    http_response_code(404);
    exit;
}

$fullPath = __DIR__ . $path;

/*
 * Serve static files only.
 * Do not return false for PHP files.
 */
if ($path !== '/' && is_file($fullPath)) {
    $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

    if ($extension === 'php') {
        http_response_code(404);
        exit;
    }

    return false;
}

if ($path === '/' || $path === '') {
    require __DIR__ . '/index.php';
    return true;
}

$id = trim($path, '/');

if (Security::isValidResultId($id)) {
    $_GET['id'] = $id;
    require __DIR__ . '/results.php';
    return true;
}

http_response_code(404);
exit;