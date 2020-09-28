<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');

/* const.php - included in git ignore

<?php 

    define('TOKEN', '2329131983189xxxx' ;

    $destinatarios = array( 
                            'user1' => '654535352',
                            'user2' => '6322354535352',
                        );

*/
require('const.php');
require('funcs.php');


$records = array(); // records to show in the report table

$team_name = TEAM; // team name for program search
$hours_max = HORAS;
$team_label = ucwords(str_replace('-', ' ', $team_name)); // team name to show

$team_name = str_replace(' ', '-', $team_name);
replace_accents($team_name); // this avoids "not found" result when user search for name that contains something like "Atlético"

$team_matches = get_team_matches($team_name);

if ($team_matches === false) {
    $msg = $team_label . ' no encontrado.';
} elseif ($team_matches === 0) {
    $msg = $team_label . 'No tiene matches en juego';
}

if (empty($msg)) {

    foreach ($team_matches as $match) {


        $id_match = substr($match->id, strrpos($match->id, '/') + 1);
        $rival = ucwords(str_replace('-', ' ', substr($match->opponent, strrpos($match->opponent, '/') + 1)));

        $match_players = get_match_players($id_match, $team_name);


        foreach ($match_players as $player) {
            $games_to_report = get_games_to_report($hours_max, $player->board, $player->username, $rival, $player->status);

            if (count($games_to_report)) {
                foreach ($games_to_report as $record) {
                    $records[] = $record;
                }
            }
        }
    }
    if (empty($records)) {
        $msg = 'Sin TOs a la vista';
    } else {
        $msg = '<b>Riesgo de TO</b>';

        foreach ($records as $record) {
            $color = $record['colour'] == 'white' ? 'blancas' : 'negras';
            $tiempo = explode(':', $record['time_remaining']);
            $msg .= PHP_EOL. '- '. $record['player'] . ' con ' . $color . ' vs. ' . $record['rival'] . ' - ' . $tiempo[0] . 'hs. ' . $tiempo[1] . ' min.';
        }
    }
} else {
    $msg = 'Error: ' . $msg;
}


$token = TOKEN;
$id = ID_CANAL;
$urlMsg = "https://api.telegram.org/bot{$token}/sendMessage";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $urlMsg);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "chat_id={$id}&parse_mode=HTML&text=$msg");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$server_output = curl_exec($ch);
$result = json_decode($server_output);

if (!$result->ok) {
    $errorMsg = "Error code: $result->error$result->description \n";
    $handler = fopen('errorLog.log', 'a+') or die('no se pudo abrir archivo de log <br>'.$errorMsg);
    fwrite($handler, date('d/m/Y H:i:s') . $errorMsg);
}
curl_close($ch);
muestraArrayUobjeto($result, __FILE__, __LINE__, 1, 0);