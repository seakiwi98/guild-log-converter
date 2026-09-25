<?php

namespace LogConv;

class GuildLogParser
{
    public function parse($content)
    {
        $content = str_replace("\r\n", "\n", $content);
        $content = str_replace("\r", "\n", $content);

        $lines = explode("\n", $content);
        $events = array();

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || strpos($line, 'Angriffskraft') === false) {
                continue;
            }

            $event = $this->parseKillLine($line);

            if ($event !== null) {
                $events[] = $event;
            }
        }

        return $this->buildStats($events);
    }

    private function parseKillLine($line)
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

        return array(
            'killer_guild' => trim($attackerMatches[1]),
            'killer_name' => $this->cleanPlayerName($attackerMatches[2]),
            'killer_level' => (int) $attackerMatches[3],
            'victim_guild' => trim($victimMatches[1]),
            'victim_name' => $this->cleanPlayerName($victimMatches[2]),
            'points' => 2,
        );
    }

    private function cleanPlayerName($name)
    {
        $name = trim($name);
        $name = preg_replace('/\s+/', ' ', $name);

        $prefixes = array(
            'Verteidiger ',
            'Der Gildenmeister ',
        );

        foreach ($prefixes as $prefix) {
            if (strpos($name, $prefix) === 0) {
                $name = substr($name, strlen($prefix));
                break;
            }
        }

        return trim(preg_replace('/\s+/', ' ', $name));
    }

    private function buildStats(array $events)
    {
        $players = array();
        $guilds = array();
        $matchups = array();

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

        usort($players, array($this, 'sortPlayers'));
        usort($guilds, array($this, 'sortGuilds'));
        usort($matchups, array($this, 'sortMatchups'));

        return array(
            'events' => $events,
            'players' => $players,
            'guilds' => $guilds,
            'matchups' => $matchups,
            'totals' => array(
                'events' => count($events),
                'players' => count($players),
                'guilds' => count($guilds),
            ),
        );
    }

    private function addPlayerStats(array &$players, array $event)
    {
        $killerKey = $event['killer_guild'] . '|' . $event['killer_name'];
        $victimKey = $event['victim_guild'] . '|' . $event['victim_name'];

        if (!isset($players[$killerKey])) {
            $players[$killerKey] = $this->emptyPlayerStats($event['killer_name'], $event['killer_guild']);
        }

        if (!isset($players[$victimKey])) {
            $players[$victimKey] = $this->emptyPlayerStats($event['victim_name'], $event['victim_guild']);
        }

        $players[$killerKey]['kills']++;
        $players[$killerKey]['points'] += $event['points'];
        $players[$victimKey]['deaths']++;
    }

    private function addGuildStats(array &$guilds, array $event)
    {
        if (!isset($guilds[$event['killer_guild']])) {
            $guilds[$event['killer_guild']] = $this->emptyGuildStats($event['killer_guild']);
        }

        if (!isset($guilds[$event['victim_guild']])) {
            $guilds[$event['victim_guild']] = $this->emptyGuildStats($event['victim_guild']);
        }

        $guilds[$event['killer_guild']]['kills']++;
        $guilds[$event['killer_guild']]['points'] += $event['points'];
        $guilds[$event['killer_guild']]['players'][$event['killer_name']] = true;

        $guilds[$event['victim_guild']]['deaths']++;
        $guilds[$event['victim_guild']]['players'][$event['victim_name']] = true;
    }

    private function addMatchupStats(array &$matchups, array $event)
    {
        $matchupKey = $event['killer_guild'] . '|' . $event['victim_guild'];

        if (!isset($matchups[$matchupKey])) {
            $matchups[$matchupKey] = array(
                'killer_guild' => $event['killer_guild'],
                'victim_guild' => $event['victim_guild'],
                'kills' => 0,
                'points' => 0,
            );
        }

        $matchups[$matchupKey]['kills']++;
        $matchups[$matchupKey]['points'] += $event['points'];
    }

    private function emptyPlayerStats($name, $guild)
    {
        return array(
            'name' => $name,
            'guild' => $guild,
            'kills' => 0,
            'deaths' => 0,
            'kd_ratio' => 0,
            'points' => 0,
        );
    }

    private function emptyGuildStats($name)
    {
        return array(
            'name' => $name,
            'kills' => 0,
            'deaths' => 0,
            'kd_ratio' => 0,
            'points' => 0,
            'unique_players' => 0,
            'players' => array(),
        );
    }

    private function ratio($kills, $deaths)
    {
        if ($deaths <= 0) {
            return $kills;
        }

        return round($kills / $deaths, 2);
    }

    private function sortPlayers($a, $b)
    {
        if ($a['points'] === $b['points']) {
            if ($a['kills'] === $b['kills']) {
                return strcmp($a['name'], $b['name']);
            }

            return $b['kills'] - $a['kills'];
        }

        return $b['points'] - $a['points'];
    }

    private function sortGuilds($a, $b)
    {
        if ($a['points'] === $b['points']) {
            return $b['kills'] - $a['kills'];
        }

        return $b['points'] - $a['points'];
    }

    private function sortMatchups($a, $b)
    {
        if ($a['kills'] === $b['kills']) {
            return $b['points'] - $a['points'];
        }

        return $b['kills'] - $a['kills'];
    }
}
