<?php

return array(
    'app' => array(
        'title' => 'Guild Log Overview',
        'skip_link' => 'Skip to main content',
        'eyebrow' => 'Guild Battle Log Converter',
    ),

    'nav' => array(
        'upload' => 'Upload',
        'logs' => 'Converted Logs',
    ),

    'hero' => array(
        'upload_title' => 'Upload a guild log and review battle statistics',
        'upload_description' => 'Parse kills, deaths, guilds and player statistics. Every kill is counted as 2 points.',
        'shared_title' => 'Shared guild log statistics',
        'shared_description' => 'This saved result page can be bookmarked or shared with others.',
        'logs_title' => 'Converted logs',
        'logs_description' => 'Browse saved conversions and open their shareable result pages.',
    ),

    'upload' => array(
        'icon' => '',
        'title' => 'Analyze a battle log',
        'description' => 'Upload a TXT log file to calculate kills, deaths, K/D, points, guild statistics and player breakdowns.',
        'file_title' => 'Choose TXT file',
        'file_help' => 'Accepted format: plain text files ending in .txt.',
        'button' => 'Analyze Log',
        'button_aria' => 'Analyze uploaded guild log',
    ),

    'share' => array(
        'eyebrow' => 'Shareable Result',
        'title' => 'Share this result',
        'description' => 'Anyone with this link can view the parsed statistics.',
        'input_aria' => 'Shareable result URL',
        'copy_button' => 'Copy Link',
    ),

    'summary' => array(
        'aria' => 'Upload summary',
        'events' => 'Total Events',
        'players' => 'Players',
        'guilds' => 'Guilds',
        'file' => 'Uploaded File',
    ),

    'modal' => array(
        'eyebrow' => 'Detailed Statistics',
        'title' => 'Details',
        'description' => 'Detailed statistics for the selected player or guild.',
        'close' => 'Close details dialog',
    ),

    'logs' => array(
        'title' => 'All converted logs',
        'description' => 'Every saved conversion appears here with a direct link to its result page.',
        'empty' => 'No converted logs found yet.',
        'open' => 'Open result',
        'open_aria' => 'Open converted log result',
        'table_aria' => 'Scrollable converted logs table',
    ),

    'sections' => array(
        'players' => 'Player Overview',
        'players_hint' => 'Sorted by points, then kills.',
        'guilds' => 'Guild Overview',
        'guilds_hint' => 'Aggregated kills, deaths and points per guild.',
        'matchups' => 'Guild Matchups',
        'matchups_hint' => 'Shows how often one guild killed players from another guild.',
        'events' => 'Parsed Events',
        'events_hint' => 'Every parsed kill from the uploaded log.',
    ),

    'details' => array(
        'player_eyebrow' => 'Player Details',
        'guild_eyebrow' => 'Guild Details',
        'points' => 'Points',
        'kills' => 'Kills',
        'deaths' => 'Deaths',
        'kd' => 'K/D',
        'killed_opponents' => 'Killed opponents',
        'killed_by' => 'Killed by',
        'player_history' => 'Player event history',
        'members_seen' => 'Members seen',
        'matchups' => 'Matchups',
        'guild_history' => 'Guild event history',
        'no_kills' => 'No kills recorded.',
        'no_deaths' => 'No deaths recorded.',
        'no_matchups' => 'No matchups recorded.',
        'players_seen_suffix' => 'players seen in this log',
        'killed_word' => 'killed',
        'versus' => 'vs',
    ),

    'table' => array(
        'number' => '#',
        'file' => 'File',
        'created_at' => 'Created',
        'events' => 'Events',
        'players' => 'Players',
        'guilds' => 'Guilds',
        'actions' => 'Actions',
        'player' => 'Player',
        'guild' => 'Guild',
        'kills' => 'Kills',
        'deaths' => 'Deaths',
        'kd' => 'K/D',
        'points' => 'Points',
        'unique_players' => 'Unique Players',
        'attacking_guild' => 'Attacking Guild',
        'victim_guild' => 'Victim Guild',
        'killer' => 'Killer',
        'killer_guild' => 'Killer Guild',
        'victim' => 'Victim',
    ),

    'aria' => array(
        'player_table' => 'Scrollable player overview table',
        'guild_table' => 'Scrollable guild overview table',
        'matchups_table' => 'Scrollable guild matchups table',
        'events_table' => 'Scrollable parsed events table',
        'show_player' => 'Show details for player',
        'show_guild' => 'Show details for guild',
        'show_attacking_guild' => 'Show details for attacking guild',
        'show_victim_guild' => 'Show details for victim guild',
        'show_killer' => 'Show details for killer',
        'show_killer_guild' => 'Show details for killer guild',
        'show_victim' => 'Show details for victim',
    ),

    'captions' => array(
        'logs_table' => 'All converted logs with links to their result pages',
        'player_table' => 'Player statistics sorted by points, then kills',
        'guild_table' => 'Guild statistics with kills, deaths, points and unique players',
        'matchups_table' => 'Guild matchup statistics showing attacking guilds, victim guilds, kills and points',
        'events_table' => 'Parsed kill events from the uploaded guild log',
    ),

    'errors' => array(
        'invalid_upload' => 'Please upload a valid .txt log file.',
        'txt_only' => 'Only .txt files are allowed.',
        'empty_file' => 'The uploaded file is empty or could not be read.',
        'no_events' => 'No valid kill entries were found in the uploaded file.',
        'save_failed' => 'The result could not be saved. Please try again.',
        'not_found' => 'This shared result does not exist or is no longer available.',
        'prefix' => 'Error:',
    ),

    'js' => array(
        'copied' => 'Copied',
        'select_and_copy' => 'Select and copy'
    ),
);