<?php
ini_set('display_errors',1);
require __DIR__ . '/../../../db.php';
require __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

function getStaffInfo( $groups ) {
  $groupList = implode('","', $groups);
  $info = [];
  $sql = "
    SELECT 
        staff_type_id, first_name, last_name, cell
    FROM
        th_chidon_staff_assignments sa
            JOIN
        th_chidon_staff s USING (staff_id)
            JOIN
        th_chidon_types t ON t.th_chidon_type_id = sa.staff_type_id
    WHERE
        group_number in (\"$groupList\")  
            AND staff_type_id IN (1,6,9)
  ";
  $result = mysql_query( $sql );
  while ( $row = mysql_fetch_assoc( $result ) ) {
    switch ( $row['staff_type_id'] ) {
      case 1:
        $type = "counselor";
        break;
      case 6:
        $type = 'coordinator';
        break;
      case 9:
        $type = 'walking_counselor';
        break;
    }
    $info[$type] = [
      'name'  =>  $row['first_name'] . ' ' . $row['last_name'], 
      'phone' =>  $row['cell']
    ];
  }
  return $info;
}

$info = [];
$sql = "
  SELECT 
      tc.*, u.*, s.*, t.*, tcc.first_name chap_first, tcc.last_name as chap_last, tcc.phone as chap_phone 
  FROM
      th_chidon tc
          JOIN
      th_chidon_chaps tcc ON tcc.school_id = tc.school_id
          JOIN
      users u USING (user_id)
          JOIN
      schools s ON s.school_id = tc.school_id 
          LEFT JOIN
      th_chidon_teams t ON t.team_id = tc.team_id
  WHERE
      tc.year = $year AND tc.date_paid > 0
          AND u.gender = 'F'
          AND tcc.chap_type = 1 
          AND tcc.chidon_type = 'girls' 
          AND tcc.year = $year 
  GROUP BY tc.user_id  
  ORDER BY s.school_name, tc.grade, u.last, u.first
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

      <?php
      // get safety coordinator and walking counselor
      $staffInfo = getStaffInfo( [$row['walking_group'], $row['bunk_number']] );
      ?>

      <div style="position: relative">
        <div class='front'>
          <?php
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
              $front = "background\KIDS-9.png";
              $back = "background\KIDS-10.png";
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
            <?php if ( in_array( $row['school_id'], [61,269] ) ) : ?>
            Worldwide 
            <?php else : ?>
            <?= $row['school_city'] . ', ' . $row['school_state'] ?>
            <?php endif; ?>
          </div>
          <div class='counselor' style='color: <?= $color ?>'>
            Counselor: <?= isset( $staffInfo['counselor'] ) ? $staffInfo['counselor']['name'] : ''; ?>
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
                foreach ( $history as $year ) if ( !empty( $year ) ) $yrs++;
              } 
              echo $yrs;
              ?>
            </div>
            YEARS AT CHIDON
        </div>  
        </div>
        
      </div>
      <div style="clear: both"></div>
    <?php endforeach; ?>
  </div>
</body>
</html>