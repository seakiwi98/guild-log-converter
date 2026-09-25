<?php

return array(
    'app' => array(
        'title' => 'Guild Log Overview',
        'skip_link' => 'Skip to main content',
        'eyebrow' => '&#x2694;&#xFE0F; Guild Battle Log Converter',
    ),

    'nav' => array(
        'upload' => '&#x1F4E4; Upload',
        'logs' => '&#x1F5C2;&#xFE0F; Converted Logs',
    ),

    'hero' => array(
        'upload_title' => '&#x26A1; Upload a guild log and get instant stats',
        'upload_description' => 'Parses kills, deaths, guilds and player statistics. Every kill is counted as 2 points.',
        'shared_title' => '&#x1F4CA; Shared guild log stats',
        'shared_description' => 'This is a saved result page. You can bookmark it or send the link to other people.',
        'logs_title' => '&#x1F5C2;&#xFE0F; Converted logs',
        'logs_description' => 'Browse all converted battle logs and open their shareable result pages.',
    ),

    'upload' => array(
        'icon' => '&#x1F4DC;',
        'title' => '&#x1F50D; Analyze a battle log',
        'description' => 'Upload your TXT logfile. The page will calculate kills, deaths, K/D, points, guild stats and individual player breakdowns.',
        'file_title' => '&#x1F4C4; Choose your TXT file',
        'file_help' => 'Accepted format: plain text files ending in .txt.',
        'button' => '&#x1F680; Analyze Log',
        'button_aria' => 'Analyze uploaded guild log',
    ),

    'share' => array(
        'eyebrow' => '&#x1F517; Shareable Result',
        'title' => '&#x1F4E3; Send this page to others',
        'description' => 'This result has a unique URL. Anyone with the link can view the parsed statistics.',
        'input_aria' => 'Shareable result URL',
        'copy_button' => '&#x1F4CB; Copy Link',
    ),

    'summary' => array(
        'aria' => 'Upload summary',
        'events' => '&#x1F525; Total Events',
        'players' => '&#x1F9D9; Players',
        'guilds' => '&#x1F6E1;&#xFE0F; Guilds',
        'file' => '&#x1F4C4; Uploaded File',
    ),

    'modal' => array(
        'eyebrow' => '&#x1F4CC; Detailed Statistics',
        'title' => 'Details',
        'description' => 'Detailed statistics for the selected player or guild.',
        'close' => 'Close details dialog',
    ),

    'logs' => array(
        'title' => '&#x1F5C2;&#xFE0F; All converted logs',
        'description' => 'Every saved conversion appears here with a direct link to its shareable result page.',
        'empty' => 'No converted logs found yet.',
        'open' => 'Open result',
        'open_aria' => 'Open converted log result',
        'table_aria' => 'Scrollable converted logs table',
    ),

    'sections' => array(
        'players' => '&#x1F9D9; Player Overview',
        'players_hint' => 'Sorted by points, then kills.',
        'guilds' => '&#x1F6E1;&#xFE0F; Guild Overview',
        'guilds_hint' => 'Aggregated kills, deaths and points per guild.',
        'matchups' => '&#x2694;&#xFE0F; Guild Matchups',
        'matchups_hint' => 'Shows how often one guild killed players from another guild.',
        'events' => '&#x1F525; Parsed Events',
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
        'prefix' => '&#x26A0;&#xFE0F; Error:',
    ),

    'js' => array(
        'copied' => 'Copied!',
        'select_and_copy' => 'Select and copy',
    ),
);