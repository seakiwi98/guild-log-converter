$ErrorActionPreference = "Stop"

function Write-ProjectFile {
    param (
        [string] $Path,
        [string] $Content
    )

    $directory = Split-Path -Parent $Path

    if ($directory -and -not (Test-Path $directory)) {
        New-Item -ItemType Directory -Path $directory -Force | Out-Null
    }

    $utf8NoBom = New-Object System.Text.UTF8Encoding($false)
    $normalizedContent = $Content.TrimStart([char]0xFEFF)

    if ($Path.EndsWith(".php")) {
        $normalizedContent = $normalizedContent.TrimStart()
    }

    [System.IO.File]::WriteAllText((Join-Path (Get-Location) $Path), $normalizedContent, $utf8NoBom)
}

Write-ProjectFile "public/router.php" @'
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
'@

Write-ProjectFile "public/results.php" @'
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use LogConv\DetailBuilder;
use LogConv\ResultRepository;
use LogConv\Security;
use LogConv\ViewRenderer;

/*
 * This file is internal. It may only be included by router.php.
 * Direct browser access to /results.php must never render the app.
 */
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (!defined('APP_BOOTSTRAPPED') || $requestPath === '/results.php') {
    http_response_code(404);
    exit;
}

Security::sendHeaders();
Security::requireMethod(array('GET'));

$result = null;
$error = null;
$fileName = null;
$playerDetails = array();
$guildDetails = array();
$shareUrl = null;
$isSharedResult = true;

$text = require __DIR__ . '/../config/ui_texts.php';

$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!Security::isValidResultId($id)) {
    http_response_code(404);
    exit;
}

$repository = new ResultRepository(__DIR__ . '/../data/results');
$payload = $repository->find($id);

if ($payload === null) {
    http_response_code(404);
    exit;
}

$result = $payload['result'];
$fileName = isset($payload['file_name']) ? $payload['file_name'] : 'Uploaded log';

$detailBuilder = new DetailBuilder();
$details = $detailBuilder->build($result);

$playerDetails = $details['players'];
$guildDetails = $details['guilds'];

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

$shareUrl = $scheme . '://' . $host . '/' . rawurlencode($payload['id']);

$renderer = new ViewRenderer(__DIR__ . '/../templates', $text);

echo $renderer->render('layout.twig', array(
    'result' => $result,
    'error' => $error,
    'fileName' => $fileName,
    'playerDetails' => $playerDetails,
    'guildDetails' => $guildDetails,
    'shareUrl' => $shareUrl,
    'isSharedResult' => $isSharedResult,
));
'@

Write-ProjectFile "public/.htaccess" @'
Options -Indexes

<IfModule mod_rewrite.c>
    RewriteEngine On

    RewriteCond %{THE_REQUEST} \s/+results\.php[?\s] [NC]
    RewriteRule ^results\.php$ - [R=404,L]

    RewriteCond %{THE_REQUEST} \s/+router\.php[?\s] [NC]
    RewriteRule ^router\.php$ - [R=404,L]

    RewriteCond %{REQUEST_FILENAME} -f [OR]
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^ - [L]

    RewriteRule ^([a-f0-9]{32,40})$ results.php?id=$1 [L,QSA]

    RewriteRule ^$ index.php [L]
</IfModule>

<IfModule mod_headers.c>
    Header always set X-Frame-Options "DENY"
    Header always set X-Content-Type-Options "nosniff"
    Header always set Referrer-Policy "no-referrer"
    Header always set Permissions-Policy "camera=(), microphone=(), geolocation=(), payment=()"
    Header always set Cross-Origin-Opener-Policy "same-origin"
    Header always set Cross-Origin-Resource-Policy "same-origin"
</IfModule>

<FilesMatch "\.(json|lock|ps1|md|dist|env|ini|log)$">
    Require all denied
</FilesMatch>
'@

Write-Host ""
Write-Host "Direct results.php access is now blocked." -ForegroundColor Green
Write-Host "Restart your local server before testing." -ForegroundColor Yellow