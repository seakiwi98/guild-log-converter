<?php

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function fmt($value)
{
    if (is_float($value)) {
        return number_format($value, 2);
    }

    return number_format((int) $value);
}

function detailKey($type, $guild, $name)
{
    return $type . ':' . md5($guild . '|' . $name);
}

function guildDetailKey($guild)
{
    return 'guild:' . md5($guild);
}