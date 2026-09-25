<?php

require_once __DIR__ . '/../vendor/autoload.php';

use LogConv\ResultRepository;
use LogConv\Security;
use LogConv\ViewRenderer;

/*
 * Internal endpoint. Public access should happen through /logs.
 */
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (!defined('APP_BOOTSTRAPPED') || $requestPath === '/logs.php') {
    http_response_code(404);
    exit;
}

Security::sendHeaders();
Security::requireMethod(array('GET'));

$text = require __DIR__ . '/../config/ui_texts.php';

$repository = new ResultRepository(__DIR__ . '/../data/results');
$logs = $repository->all();

$renderer = new ViewRenderer(__DIR__ . '/../templates', $text);

echo $renderer->render('logs.twig', array(
    'logs' => $logs,
    'isLogsPage' => true,
    'isSharedResult' => false,
    'result' => null,
    'error' => null,
    'fileName' => null,
    'playerDetails' => array(),
    'guildDetails' => array(),
    'shareUrl' => null,
));