<?php
ini_set('display_errors',1);
require __DIR__ . '/../../../db.php';

$info = [];
$sql = "
  SELECT 
      *
  FROM
      th_chidon tc
          JOIN
      users u USING (user_id)
          JOIN
      schools s ON s.school_id = tc.school_id
          left JOIN
      th_chidon_teams t ON t.team_id = tc.team_id
  WHERE
      tc.year = 5779 AND tc.date_paid > 0
          AND u.gender = 'M'
";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $info[] = $row;
}
//echo "<pre>"; print_r( $info ); echo "</pre>";
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>ID Cards</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta charset="utf8" />
  <style>
    /* #main {
      background-image: url('background/KIDS-1.png');
    } */
    @font-face {
      font-family: gotham;
      src: url('fonts/Gotham-Bold.otf');
    }
    @font-face {
      font-family: gothamBook;
      src: url('fonts/GOTHAM-BOOK.OTF');
    }
    @font-face {
      font-family: gothamBlack;
      src: url('fonts/GOTHAM-MEDIUM.OTF');
    }
    #main img {
      width: 4.375in;
      height: 5.625in;
    }
    .front, .back {
      /* postion: relative; */
    }
    .rep {
      transform: rotate(-90deg);
      font-family: gothamBlack;
      font-size: 28pt;
      color: #fff;
      position: absolute;
      left: -1in;
      top: 2in;
    }
    .contestant {
      transform: rotate(-90deg);
      font-family: gothamBlack;
      font-size: 32pt;
      color: #fff;
      position: absolute;
      left: -0.8in;
      top: 2in;
    }
    .name {
      font-family: gotham;
      font-size: 20pt;
      color: #000;
      top: 2.125in;
      position: absolute;
      left: 1.5in;
      text-align: center;
      width: 250px;
    }
    .school {
      font-family: gothamBook;
      font-size: 10pt;
      color: #000;
      top: 2.5in;
      position: absolute;
      left: 1.5in;
      text-align: center;
      width: 250px;
    }
    .counselor {
      font-family: gotham;
      font-size: 10pt;
      color: blue;
      top: 2.95in;
      position: absolute;
      left: 1.5in;
      text-align: center;
      width: 250px;
    }
    .grade {
      font-family: gotham;
      font-size: 11pt;
      color: #fff;
      top: 3.85in;
      position: absolute;
      left: 1.9in;
      text-align: center;
      width: 0.8in;
      height: 0.25in;
    }
    .bunk {
      font-family: gotham;
      font-size: 11pt;
      color: #fff;
      top: 3.85in;
      position: absolute;
      left: 2.7in;
      text-align: center;
      width: 0.7in;
      height: 0.25in;
    }
    .team {
      font-family: gotham;
      font-size: 11pt;
      color: #fff;
      top: 4.1in;
      position: absolute;
      left: 2in;
      width: 1.6in;
      height: 0.25in;
      /* text-align: center; */
    }
    .chidon_years {
      font-family: gotham;
      font-size: 8pt;
      top: 4.5in;
      left: 0.25in;
      position: absolute;
      width: 100px;
      height: 60px;
      text-align: center;
      color: #fff;
    }
    .chidon_years .year {
      font-size: 16pt;
      padding-bottom: 2pt;
    }
  </style>
</head>
<body>
  <div id="main">
    <?php foreach ( $info as $row ) : ?>
      <div class='front'>
        <img src="background\KIDS-1.png" />
        <?php
        $class = 'contestant';
        if ( $row['school_rep'] ) $class = 'rep';
        ?>
        <div class="<?= $class ?>">
          <?= $row['school_rep'] ? 'REPRESENTATIVE' : 'CONTESTANT' ?>
        </div>
        <div class='name'>
          <?= $row['first'] . ' ' . $row['last'] ?>
        </div>
        <div class='school'>
          <?= $row['school_name'] ?><br />
          <?= $row['school_city'] . ', ' . $row['school_state'] ?>
        </div>
        <div class='counselor'>
          Counselor: 
        </div>
        <div class='grade'>
          Grade <?= $row['grade'] ?>
        </div>
        <div class='bunk'>
          Bunk <?=$row['bunk_number']?>
        </div>
        <div class='team'>
          Team <?=$row['team']?>
          <?= $row['team'] ?>
        </div>
        <div class='chidon_years'>
          <div class='year'>
            <?php
            $yrs = 1;
            if ( $row['history'] ) {
              $history = explode(',', $row['history']);
              $yrs += count( $history );
            } 
            echo $yrs;
            ?>
          </div>
          YEARS AT CHIDON
      </div>  
      </div>
      <div class='back'>
        <img src="background\KIDS-2.png" />
      </div>
      <?php break; ?>
    <?php endforeach; ?>
  </div>
</body>
</html>