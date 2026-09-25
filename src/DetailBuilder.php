<?php

namespace LogConv;

class DetailBuilder
{
    public function build(array $result)
    {
        return array(
            'players' => $this->buildPlayerDetails($result),
            'guilds' => $this->buildGuildDetails($result),
        );
    }

    private function buildPlayerDetails(array $result)
    {
        $details = array();

        foreach ($result['players'] as $player) {
            $key = detailKey('player', $player['guild'], $player['name']);

            $details[$key] = array(
                'player' => $player,
                'kills_against' => array(),
                'deaths_from' => array(),
                'events' => array(),
            );
        }

        foreach ($result['events'] as $event) {
            $killerKey = detailKey('player', $event['killer_guild'], $event['killer_name']);
            $victimKey = detailKey('player', $event['victim_guild'], $event['victim_name']);

            if (isset($details[$killerKey])) {
                $this->addPlayerKillDetail($details[$killerKey], $event);
            }

            if (isset($details[$victimKey])) {
                $this->addPlayerDeathDetail($details[$victimKey], $event);
            }
        }

        return $details;
    }

    private function buildGuildDetails(array $result)
    {
        $details = array();

        foreach ($result['guilds'] as $guild) {
            $key = guildDetailKey($guild['name']);

            $details[$key] = array(
                'guild' => $guild,
                'members' => array(),
                'matchups' => array(),
                'events' => array(),
            );
        }

        foreach ($result['events'] as $event) {
            $killerGuildKey = guildDetailKey($event['killer_guild']);
            $victimGuildKey = guildDetailKey($event['victim_guild']);

            if (isset($details[$killerGuildKey])) {
                $this->addGuildKillDetail($details[$killerGuildKey], $event);
            }

            if (isset($details[$victimGuildKey])) {
                $this->addGuildDeathDetail($details[$victimGuildKey], $event);
            }
        }

        return $details;
    }

    private function addPlayerKillDetail(array &$detail, array $event)
    {
        $opponentKey = $event['victim_guild'] . '|' . $event['victim_name'];

        if (!isset($detail['kills_against'][$opponentKey])) {
            $detail['kills_against'][$opponentKey] = array(
                'name' => $event['victim_name'],
                'guild' => $event['victim_guild'],
                'count' => 0,
            );
        }

        $detail['kills_against'][$opponentKey]['count']++;
        $detail['events'][] = $event;
    }

    private function addPlayerDeathDetail(array &$detail, array $event)
    {
        $opponentKey = $event['killer_guild'] . '|' . $event['killer_name'];

        if (!isset($detail['deaths_from'][$opponentKey])) {
            $detail['deaths_from'][$opponentKey] = array(
                'name' => $event['killer_name'],
                'guild' => $event['killer_guild'],
                'count' => 0,
            );
        }

        $detail['deaths_from'][$opponentKey]['count']++;
        $detail['events'][] = $event;
    }

    private function addGuildKillDetail(array &$detail, array $event)
    {
        $detail['members'][$event['killer_name']] = true;
        $detail['events'][] = $event;

        $matchupKey = $event['victim_guild'];

        if (!isset($detail['matchups'][$matchupKey])) {
            $detail['matchups'][$matchupKey] = array(
                'guild' => $event['victim_guild'],
                'kills' => 0,
                'deaths' => 0,
                'points' => 0,
            );
        }

        $detail['matchups'][$matchupKey]['kills']++;
        $detail['matchups'][$matchupKey]['points'] += $event['points'];
    }

    private function addGuildDeathDetail(array &$detail, array $event)
    {
        $detail['members'][$event['victim_name']] = true;
        $detail['events'][] = $event;

        $matchupKey = $event['killer_guild'];

        if (!isset($detail['matchups'][$matchupKey])) {
            $detail['matchups'][$matchupKey] = array(
                'guild' => $event['killer_guild'],
                'kills' => 0,
                'deaths' => 0,
                'points' => 0,
            );
        }

        $detail['matchups'][$matchupKey]['deaths']++;
    }
}
