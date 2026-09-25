<?php

declare(strict_types=1);

namespace LogConv;

final class ResultRepository
{
    private string $directory;

    public function __construct(string $directory)
    {
        $this->directory = rtrim($directory, DIRECTORY_SEPARATOR);

        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0750, true);
        }
    }

    public function save(array $result, string $fileName): ?string
    {
        $id = $this->createId();

        $payload = [
            'id' => $id,
            'file_name' => Security::safeUploadedFileName($fileName),
            'created_at' => gmdate('Y-m-d\TH:i:s\Z'),
            'result' => $result,
        ];

        $json = json_encode($payload, JSON_THROW_ON_ERROR);
        $path = $this->getPath($id);

        if (file_put_contents($path, $json, LOCK_EX) === false) {
            return null;
        }

        @chmod($path, 0640);

        return $id;
    }

    public function find(?string $id): ?array
    {
        if (!Security::isValidResultId($id)) {
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

        try {
            $payload = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        if (!is_array($payload) || !isset($payload['result'])) {
            return null;
        }

        return $payload;
    }

    public function all(): array
    {
        $items = [];
        $files = glob($this->directory . DIRECTORY_SEPARATOR . '*.json');

        if (!is_array($files)) {
            return $items;
        }

        foreach ($files as $file) {
            $id = basename($file, '.json');

            if (!Security::isValidResultId($id)) {
                continue;
            }

            $payload = $this->find($id);

            if ($payload === null || !isset($payload['result']['totals'])) {
                continue;
            }

            $totals = $payload['result']['totals'];

            $items[] = [
                'id' => $payload['id'],
                'url' => '/' . rawurlencode($payload['id']),
                'file_name' => $payload['file_name'] ?? 'Uploaded log',
                'created_at' => $payload['created_at'] ?? '',
                'events' => $totals['events'] ?? 0,
                'players' => $totals['players'] ?? 0,
                'guilds' => $totals['guilds'] ?? 0,
            ];
        }

        usort($items, static fn (array $a, array $b): int => strcmp($b['created_at'], $a['created_at']));

        return $items;
    }

    private function createId(): string
    {
        return bin2hex(random_bytes(16));
    }

    private function getPath(string $id): string
    {
        return $this->directory . DIRECTORY_SEPARATOR . $id . '.json';
    }
}