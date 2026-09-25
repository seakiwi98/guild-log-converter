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

New-Item -ItemType Directory -Path "data/results" -Force | Out-Null

Write-ProjectFile "src/Security.php" @'
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
'@

Write-ProjectFile "public/router.php" @'
<?php

define('APP_BOOTSTRAPPED', true);

require_once __DIR__ . '/../vendor/autoload.php';

use LogConv\Security;

Security::sendHeaders();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$fullPath = __DIR__ . $path;

/*
 * Serve real static files directly through PHP's built-in server.
 * Apache/Nginx will serve them normally in production.
 */
if ($path !== '/' && is_file($fullPath)) {
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
require __DIR__ . '/index.php';
return true;
'@

Write-ProjectFile "public/index.php" @'
<?php

if (!defined('APP_BOOTSTRAPPED')) {
    define('APP_BOOTSTRAPPED', true);
}

require_once __DIR__ . '/../vendor/autoload.php';

use LogConv\GuildLogParser;
use LogConv\ResultRepository;
use LogConv\Security;
use LogConv\ViewRenderer;

Security::sendHeaders();
Security::requireMethod(array('GET', 'POST'));

$result = null;
$error = null;
$fileName = null;
$playerDetails = array();
$guildDetails = array();
$shareUrl = null;
$isSharedResult = false;

$text = require __DIR__ . '/../config/ui_texts.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['logfile']) || $_FILES['logfile']['error'] !== UPLOAD_ERR_OK) {
        $error = $text['errors']['invalid_upload'];
    } else {
        $fileName = Security::safeUploadedFileName($_FILES['logfile']['name']);
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($extension !== 'txt') {
            $error = $text['errors']['txt_only'];
        } elseif (!is_uploaded_file($_FILES['logfile']['tmp_name'])) {
            $error = $text['errors']['invalid_upload'];
        } elseif ($_FILES['logfile']['size'] > 1024 * 1024 * 5) {
            $error = 'The uploaded file is too large. Maximum size is 5 MB.';
        } else {
            $content = file_get_contents($_FILES['logfile']['tmp_name']);

            if ($content === false || trim($content) === '') {
                $error = $text['errors']['empty_file'];
            } else {
                $parser = new GuildLogParser();
                $result = $parser->parse($content);

                if ($result['totals']['events'] === 0) {
                    $error = $text['errors']['no_events'];
                    $result = null;
                } else {
                    $repository = new ResultRepository(__DIR__ . '/../data/results');
                    $id = $repository->save($result, $fileName);

                    if ($id === null) {
                        $error = $text['errors']['save_failed'];
                        $result = null;
                    } else {
                        header('Location: /' . rawurlencode($id), true, 303);
                        exit;
                    }
                }
            }
        }
    }
}

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

Write-ProjectFile "public/results.php" @'
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use LogConv\DetailBuilder;
use LogConv\ResultRepository;
use LogConv\Security;
use LogConv\ViewRenderer;

Security::denyDirectAccess();
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
    $error = $text['errors']['not_found'];
} else {
    $repository = new ResultRepository(__DIR__ . '/../data/results');
    $payload = $repository->find($id);

    if ($payload === null) {
        http_response_code(404);
        $error = $text['errors']['not_found'];
    } else {
        $result = $payload['result'];
        $fileName = isset($payload['file_name']) ? $payload['file_name'] : 'Uploaded log';

        $detailBuilder = new DetailBuilder();
        $details = $detailBuilder->build($result);

        $playerDetails = $details['players'];
        $guildDetails = $details['guilds'];

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

        $shareUrl = $scheme . '://' . $host . '/' . rawurlencode($payload['id']);
    }
}

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

Write-ProjectFile "src/ResultRepository.php" @'
<?php

namespace LogConv;

class ResultRepository
{
    private $directory;

    public function __construct($directory)
    {
        $this->directory = rtrim($directory, DIRECTORY_SEPARATOR);

        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0750, true);
        }
    }

    public function save(array $result, $fileName)
    {
        $id = $this->createId();

        $payload = array(
            'id' => $id,
            'file_name' => Security::safeUploadedFileName($fileName),
            'created_at' => date('c'),
            'result' => $result,
        );

        $path = $this->getPath($id);
        $json = json_encode($payload);

        if ($json === false) {
            return null;
        }

        if (file_put_contents($path, $json, LOCK_EX) === false) {
            return null;
        }

        @chmod($path, 0640);

        return $id;
    }

    public function find($id)
    {
        if (!$this->isValidId($id)) {
            return null;
        }

        $path = $this->getPath($id);

        if (!is_file($path)) {
            return null;
        }

        $json = file_get_contents($path);

        if ($json === false) {
            return null;
        }

        $payload = json_decode($json, true);

        if (!is_array($payload) || !isset($payload['result'])) {
            return null;
        }

        return $payload;
    }

    private function createId()
    {
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes(16));
        }

        return sha1(uniqid('', true) . mt_rand());
    }

    private function isValidId($id)
    {
        return Security::isValidResultId($id);
    }

    private function getPath($id)
    {
        return $this->directory . DIRECTORY_SEPARATOR . $id . '.json';
    }
}
'@

Write-ProjectFile "public/.htaccess" @'
Options -Indexes

<IfModule mod_rewrite.c>
    RewriteEngine On

    RewriteCond %{THE_REQUEST} \s/+results\.php[?\s] [NC]
    RewriteRule ^results\.php$ - [R=404,L]

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

Write-ProjectFile "data/.htaccess" @'
Require all denied
'@

Write-ProjectFile "data/results/.htaccess" @'
Require all denied
'@

Write-ProjectFile ".gitignore" @'
/vendor/
/data/results/*.json
/.idea/
/*.log
.env
.DS_Store
Thumbs.db
'@

Write-Host ""
Write-Host "Security hardening complete." -ForegroundColor Green
Write-Host ""
Write-Host "Run:" -ForegroundColor Yellow
Write-Host "composer dump-autoload" -ForegroundColor Cyan
Write-Host ""
Write-Host "Start local server with:" -ForegroundColor Yellow
Write-Host "php -S localhost:8000 -t public public/router.php" -ForegroundColor Cyan
Write-Host ""
Write-Host "Direct /results.php access will now return 404." -ForegroundColor Green