<?php

declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function fmt(int|float|string|null $value): string
{
    if (is_float($value)) {
        return number_format($value, 2);
    }

    return number_format((int) $value);
}

function detailKey(string $type, string $guild, string $name): string
{
    return $type . ':' . md5($guild . '|' . $name);
}

function guildDetailKey(string $guild): string
{
    return 'guild:' . md5($guild);
}