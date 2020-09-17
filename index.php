<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>Chesscom team matches moveby</title>

  <!-- Custom fonts for this template -->
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">


  <!-- Custom styles for this template -->
  <link href="css/sb-admin-2.min.css" rel="stylesheet">

  <!-- Custom styles for this page -->
  <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
  <style>
    @font-face {
      font-family: olsen;
      src: url(fonts/OlsenTF-Regular.otf);
    }

    * {
      font-family: olsen;
    }
  </style>
</head>

<body id="page-top">

  <!-- Page Wrapper -->
  <div id="wrapper">

    <!-- Sidebar -->


    <!-- Begin Page Content -->
    <div class="container-fluid" style="margin-top:28px">
<h5>Chess.com - Move by report</h5><hr>st
      <form method="post">

        <input required type="text" class="form-control" id="team" name="team" placeholder="Team name" style="width:393px">
        <br clear="all"><label for="hours">Hours to alert</label> (0 doesn't filter)
        <input required type="number" class="form-control" min="0" max="72" id="hours" name="hours" style="width:93px" placeholder="Hours to alert (0 doesn't filter)" value="0">
        <br clear="all"><input type="submit" class="form-control" style="width:134px; background:#ccc">
        <br clear="all">
        <hr>
      </form>

      <?php 
      
      if (!empty($_POST)) {
        
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        require('funcs.php');

       $records = array(); // records to show in the report table


       $team_name = strtolower(htmlspecialchars($_POST['team'])); // team name for program search
       $hours_max = ($_POST['hours'] == 0) ? 72 : $_POST['hours'];
       $team_label = ucwords(str_replace('-', ' ', $team_name)); // team name to show

        $team_name = str_replace(' ', '-', $team_name);
        replace_accents($team_name); // this avoids "not found" result when user search for name that contains something like "Atlético"
  
        $team_matches = get_team_matches($team_name);

        if ($team_matches === false) {
          die('Team "' . $team_label . '" not found');
        } elseif ($team_matches === 0) {
          die('Team "' . $team_label . '" has no matches in progress');
        }

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
                      
                      $bgcolor = $bgcolor === $even_color ? 'white' : $even_color ;
                      
                      echo '<tr style="font-size:0.88em;background:'.$bgcolor.'">';
                      echo '<td><span style="display:none">'. $game['time_remaining'].'</span>' .  $game['player']. '</td>';
                      echo '<td>' . ucfirst($game['status']) . '</td>';
                      echo '<td>' . $game['rival'] . '</td>';
                      echo '<td style="text-align:center">' . ucfirst($game['colour']) . '</td>';
                      echo '<td style="text-align:center">' . $game['time_remaining']. '</td>';
                      echo '<td style="text-align:center">' . $game['TO_moment'] . '</td>';
                      echo '<td style="text-align:center"><a href="'.$game['url'].'" target="_blank"><img src="board.png" style="width:28px;" title="Watch game"></td>';

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
  <p class="mb-4" style="font-size:0.7em;margin:12px">DataTables is a third party plugin that is used to generate the demo table below. For more information about DataTables, please visit the <a target="_blank" href="https://datatables.net">official DataTables documentation</a>.</p>
<?php  } ?>


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