<?php

declare(strict_types=1);

namespace LogConv;

final class GuildLogParser
{
    public function parse(string $content): array
    {
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $lines = explode("\n", $content);
        $events = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || !str_contains($line, 'Angriffskraft')) {
                continue;
            }

            $event = $this->parseKillLine($line);

            if ($event !== null) {
                $events[] = $event;
            }
        }

        return $this->buildStats($events);
    }

    private function parseKillLine(string $line): ?array
    {
        $parts = explode('Angriffskraft', $line, 2);

        if (count($parts) !== 2) {
            return null;
        }

        $attackerPart = trim($parts[0]);
        $victimPart = trim($parts[1]);
        $victimGuildPosition = strpos($victimPart, '[');

        if ($victimGuildPosition === false) {
            return null;
        }

        $victimPart = substr($victimPart, $victimGuildPosition);

        if (!preg_match('/^\[(.+?)\]\s*(.+?)\(Stufe\s+(\d+)\)$/', $attackerPart, $attackerMatches)) {
            return null;
        }

        if (!preg_match('/^\[(.+?)\]\s*(.+)$/', $victimPart, $victimMatches)) {
            return null;
        }

        return [
            'killer_guild' => trim($attackerMatches[1]),
            'killer_name' => $this->cleanPlayerName($attackerMatches[2]),
            'killer_level' => (int) $attackerMatches[3],
            'victim_guild' => trim($victimMatches[1]),
            'victim_name' => $this->cleanPlayerName($victimMatches[2]),
            'points' => 2,
        ];
    }

    private function cleanPlayerName(string $name): string
    {
        $name = trim($name);
        $name = preg_replace('/\s+/', ' ', $name) ?? $name;

        foreach (['Verteidiger ', 'Der Gildenmeister '] as $prefix) {
            if (str_starts_with($name, $prefix)) {
                $name = substr($name, strlen($prefix));
                break;
            }
        }

        return trim(preg_replace('/\s+/', ' ', $name) ?? $name);
    }

    private function buildStats(array $events): array
    {
        $players = [];
        $guilds = [];
        $matchups = [];

        foreach ($events as $event) {
            $this->addPlayerStats($players, $event);
            $this->addGuildStats($guilds, $event);
            $this->addMatchupStats($matchups, $event);
        }

        foreach ($players as $key => $player) {
            $players[$key]['kd_ratio'] = $this->ratio($player['kills'], $player['deaths']);
        }

        foreach ($guilds as $key => $guild) {
            $guilds[$key]['unique_players'] = count($guild['players']);
            $guilds[$key]['kd_ratio'] = $this->ratio($guild['kills'], $guild['deaths']);
            unset($guilds[$key]['players']);
        }

        usort($players, [$this, 'sortPlayers']);
        usort($guilds, [$this, 'sortGuilds']);
        usort($matchups, [$this, 'sortMatchups']);

        return [
            'events' => $events,
            'players' => $players,
            'guilds' => $guilds,
            'matchups' => $matchups,
            'totals' => [
                'events' => count($events),
                'players' => count($players),
                'guilds' => count($guilds),
            ],
        ];
    }

    private function addPlayerStats(array &$players, array $event): void
    {
        $killerKey = $event['killer_guild'] . '|' . $event['killer_name'];
        $victimKey = $event['victim_guild'] . '|' . $event['victim_name'];

        $players[$killerKey] ??= $this->emptyPlayerStats($event['killer_name'], $event['killer_guild']);
        $players[$victimKey] ??= $this->emptyPlayerStats($event['victim_name'], $event['victim_guild']);

        $players[$killerKey]['kills']++;
        $players[$killerKey]['points'] += $event['points'];
        $players[$victimKey]['deaths']++;
    }

    private function addGuildStats(array &$guilds, array $event): void
    {
        $guilds[$event['killer_guild']] ??= $this->emptyGuildStats($event['killer_guild']);
        $guilds[$event['victim_guild']] ??= $this->emptyGuildStats($event['victim_guild']);

        $guilds[$event['killer_guild']]['kills']++;
        $guilds[$event['killer_guild']]['points'] += $event['points'];
        $guilds[$event['killer_guild']]['players'][$event['killer_name']] = true;

        $guilds[$event['victim_guild']]['deaths']++;
        $guilds[$event['victim_guild']]['players'][$event['victim_name']] = true;
    }

    private function addMatchupStats(array &$matchups, array $event): void
    {
        $matchupKey = $event['killer_guild'] . '|' . $event['victim_guild'];

        $matchups[$matchupKey] ??= [
            'killer_guild' => $event['killer_guild'],
            'victim_guild' => $event['victim_guild'],
            'kills' => 0,
            'points' => 0,
        ];

        $matchups[$matchupKey]['kills']++;
        $matchups[$matchupKey]['points'] += $event['points'];
    }

    private function emptyPlayerStats(string $name, string $guild): array
    {
        return [
            'name' => $name,
            'guild' => $guild,
            'kills' => 0,
            'deaths' => 0,
            'kd_ratio' => 0,
            'points' => 0,
        ];
    }

    private function emptyGuildStats(string $name): array
    {
        return [
            'name' => $name,
            'kills' => 0,
            'deaths' => 0,
            'kd_ratio' => 0,
            'points' => 0,
            'unique_players' => 0,
            'players' => [],
        ];
    }

    private function ratio(int $kills, int $deaths): float|int
    {
        if ($deaths <= 0) {
            return $kills;
        }

        return round($kills / $deaths, 2);
    }

    private function sortPlayers(array $a, array $b): int
    {
        return [$b['points'], $b['kills'], $a['name']] <=> [$a['points'], $a['kills'], $b['name']];
    }

    private function sortGuilds(array $a, array $b): int
    {
        return [$b['points'], $b['kills']] <=> [$a['points'], $a['kills']];
    }

    private function sortMatchups(array $a, array $b): int
    {
        return [$b['kills'], $b['points']] <=> [$a['kills'], $a['points']];
    }
}