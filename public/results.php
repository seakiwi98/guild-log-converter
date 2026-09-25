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