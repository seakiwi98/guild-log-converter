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