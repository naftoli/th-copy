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
          AND u.gender = 'F'
";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $info[] = $row;
}
//echo "<pre>"; print_r( $info[0] ); echo "</pre>";
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>ID Cards</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="cards.css" />
</head>
<body>
  <div id="main">
    <?php foreach ( $info as $row ) : ?>
      <div style="position: relative">
        <div class='front'>
          <?php
          $row['grade'] = 6;
          // figure out which image and color to use
          switch ( $row['grade'] ) {
            case '4':
              $color = '#009EDE';
              $front = "background\KIDS-1.png";
              $back = "background\KIDS-2.png";
              break;
            case '5':
              $color = '#24b35f';
              $front = "background\KIDS-3.png";
              $back = "background\KIDS-4.png";
              break;
            case '6':
              $color = '#f07621';
              $front = "background\KIDS-5.png";
              $back = "background\KIDS-6.png";
              break;
            case '7':
              $color = '#fdcd06';
              $front = "background\KIDS-7.png";
              $back = "background\KIDS-8.png";
              break;
            case '8':
              $color = '#a663a1';
              $front = "background\KIDS-1.png";
              $back = "background\KIDS-2.png";
              break;
          }
          ?>
          <img src="<?= $front ?>" />
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
          <div class='counselor' style='color: <?= $color ?>'>
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
          <img src="<?= $back ?>" />
          <div class="zone1">
            
            <div class="cert_id">
              <div class="info">CERTIFICATE ID</div>
              <img src="icons/avatar.png" /><span class="info"><?= $row['cert_number'] ?></span>
            </div>
            <div style="clear: both"></div>
            <div class="test_table">
              TEST TABLE<br />
              <img src="icons/table.png" /><?= $row['test_table'] ?>
            </div>
            <div style="clear: both"></div>
            <div class="walking_group">
              WALKING ZONE<br />
              <img src="icons/walking icon.png" /><?= $row['walking_group'] ?>
            </div>
            <div style="clear: both"></div>
            <div class="bowling_lane">
              BOWLING LANE<br />
            <img src="icons/bowling icon.png" /><?= $row['bowling_lane'] ?>
            </div>
            <div style="clear: both"></div>
            <div class="buses">
              BUSES<br />
              <div class="school_bus"><img src="icons/bus icon.png" /><?= $row['school_bus'] ?></div>
              <div style="clear: both"></div>
              <?php if ( $row['grade'] == 6 ) : ?>
                <div class="open_air_bus"><img src="icons/icon double decker.png" /><?= $row['open_air_bus'] ?></div>
                <div style="clear: both"></div>
              <?php elseif ( in_array( $row['grade'], [7,8] ) ) : ?>
              <div class="coach_bus"><img src="icons/icon double decker.png" /><?= $row['coach_bus'] ?></div>
              <div style="clear: both"></div>
              <?php endif; ?>
              <div class="sunday_pm_bus"><img src="icons/icon double decker.png" /><?= $row['sunday_pm_bus'] ?></div>
              <div style="clear: both"></div>
            </div>
          </div>
          <div class="zone2">
              <div class="contacts">
                <div style="font-size: 8pt; color: <?= $color ?>">CONTACTS</div>
              </div>
              <div class="host">
                <div style="font-size: 8pt; color: <?= $color ?>">Host</div>
                <?php
                echo $row['host'] . " Family<br />" . $row['host_street_num'] . ' ' . $row['host_street'] . $row['host_street_num_suffix'] . "<br />";
                echo "<span><i>btwn " . $row['between_streets1'] . ' & ' . $row['between_streets2'] . "</i></span><br />";
                echo $row['host_number'];
                ?>
              </div>
              <div class="chap">
                <div style="font-size: 8pt; color: <?= $color ?>">Chaperone</div>
              </div>
              <div class="coordinator">
                <div style="font-size: 8pt; color: <?= $color ?>">Safery Coordinator</div>
              </div>
              <div class="walking_counselor">
                <div style="font-size: 8pt; color: <?= $color ?>">Walking Counselor</div>
              </div>
              <div class="hq">
                <div style="font-size: 8pt; color: <?= $color ?>">HQ Hotline</div>
                718-907-8884
              </div>
              <div class="emergency" style="color: <?= $color ?>">
                EMERGENCY<br />
                Police/Fire: 911<br />
                Hatzola: 718-387-1750
              </div>
          </div>
          <div class="zone3">
            <p style="color: <?= $color ?>">AWARD CEREMONY</p>
          </div>
        </div>
      </div>
      <div style="clear: both"></div>
    <?php endforeach; ?>
  </div>
</body>
</html>