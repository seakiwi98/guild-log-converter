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