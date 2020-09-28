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

if(empty($msg)){

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
  $msg = 'Riesgo de TO';
  foreach($records as $record){
      $msg .= '\n'. $record['player'].' vs. '.$record['rival'] 
  }

  muestraArrayUobjeto($records , __FILE__ , __LINE__ , 1 , 0);
} else{
    $msg = 'Error: '.$msg ;
}

    $token = TOKEN;

    $urlMsg = "https://api.telegram.org/bot{$token}/sendMessage";


    foreach ($destinatarios as $user => $id) {
       $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlMsg);
        curl_setopt($ch, CURLOPT_POST, 1);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, "chat_id={$id}&parse_mode=HTML&text=$msg");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        $result = json_decode($server_output);
        if (!$result->ok) {
            $handler = fopen('errorLog.log', 'a+') or die('no se pudo abrir');
            fwrite($handler, date('d/m/Y H:i:s') . " Error code: $result->error_c   $result->description \n");
        }

        curl_close($ch);
    }
?>

<h4><?= $team_label ?></h4>
Games with less than <?= $hours_max ?> hours to move:<br>&nbsp;<br clear="all">
<?php
if (empty($records)) {
    die('There are no games with less than ' . $hours_max . ' hours to move');
}

$records_sorted = sort_list($records, 'time_remaining');

$thead = $tfoot = ' <tr style="text-align:center;background:#999;color:white">
                    <th>Player</th>
                    <th>User type</th>
                    <th>Opponent team</th>
                    <th>Colour</th>
                    <th>Time remaining</td>
                    <th>Time over on</th>
                    <th>Watch game</th>
                  </tr>'

?>


<div class="card shadow mb-4">
    <div class="card-header py-3">

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <?= $thead ?>
                    </thead>
                    <tfoot>
                        <?= $tfoot ?>
                    </tfoot>
                    <tbody>
                        <?php
                        $even_color = '#ececef';
                        $bgcolor = $even_color;
                        foreach ($records_sorted as $game) {

                            $bgcolor = $bgcolor === $even_color ? 'white' : $even_color;

                            echo '<tr style="font-size:0.88em;background:' . $bgcolor . '">';
                            echo '<td><span style="display:none">' . $game['time_remaining'] . '</span>' .  $game['player'] . '</td>';
                            echo '<td>' . ucfirst($game['status']) . '</td>';
                            echo '<td>' . $game['rival'] . '</td>';
                            echo '<td style="text-align:center">' . ucfirst($game['colour']) . '</td>';
                            echo '<td style="text-align:center">' . $game['time_remaining'] . '</td>';
                            echo '<td style="text-align:center">' . $game['TO_moment'] . '</td>';
                            echo '<td style="text-align:center"><a href="' . $game['url'] . '" target="_blank"><img src="board.png" style="width:28px;" title="Watch game"></td>';

                            echo '</tr>';
                        }
                        ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->

<!-- Footer -->
<footer class="sticky-footer bg-white">
    <div class="container my-auto">

    </div>
</footer>
<!-- End of Footer -->

</div>
<!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>



<!-- Bootstrap core JavaScript-->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- Custom scripts for all pages-->
<script src="js/sb-admin-2.min.js"></script>

<!-- Page level plugins -->
<script src="vendor/datatables/jquery.dataTables.min.js"></script>
<script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

<!-- Page level custom scripts -->
<script src="js/demo/datatables-demo.js"></script>

</body>

</html>