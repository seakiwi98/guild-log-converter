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

    public function all()
    {
        $items = array();
        $files = glob($this->directory . DIRECTORY_SEPARATOR . '*.json');

        if (!is_array($files)) {
            return $items;
        }

        foreach ($files as $file) {
            $id = basename($file, '.json');

            if (!$this->isValidId($id)) {
                continue;
            }

            $payload = $this->find($id);

            if ($payload === null || !isset($payload['result']['totals'])) {
                continue;
            }

            $items[] = array(
                'id' => $payload['id'],
                'url' => '/' . rawurlencode($payload['id']),
                'file_name' => isset($payload['file_name']) ? $payload['file_name'] : 'Uploaded log',
                'created_at' => isset($payload['created_at']) ? $payload['created_at'] : '',
                'events' => isset($payload['result']['totals']['events']) ? $payload['result']['totals']['events'] : 0,
                'players' => isset($payload['result']['totals']['players']) ? $payload['result']['totals']['players'] : 0,
                'guilds' => isset($payload['result']['totals']['guilds']) ? $payload['result']['totals']['guilds'] : 0,
            );
        }

        usort($items, array($this, 'sortNewestFirst'));

        return $items;
    }

    private function sortNewestFirst($a, $b)
    {
        return strcmp($b['created_at'], $a['created_at']);
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