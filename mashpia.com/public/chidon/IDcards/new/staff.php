<?php
ini_set('display_errors',1);
require __DIR__ . '/../../../db.php';
require __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$info = [];
$staffInfo = [];
$sql = "
 SELECT 
      * 
  FROM
      th_chidon_staff_assignments sa
          JOIN
      th_chidon_staff s USING (staff_id)
          JOIN
      th_chidon_types t ON t.th_chidon_type_id = sa.staff_type_id
  WHERE
      staff_type_id in (1,2,3) 
          AND year = $year 
          AND s.gender = 'boys' 
";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $info[$row['staff_id']][$row['staff_type_id']][$row['group_number']] = 1;
  $staffInfo[$row['staff_id']] = $row;
}
//echo "<pre>"; print_r( $info ); echo "</pre>"; 
// create array with range of bunks with safety officer taking precedence
$staff = [];
foreach ( $info as $staff_id => $more ) {
  krsort( $info[$staff_id] );
}
foreach ( $info as $staff_id => $more ) {
  foreach ( $more as $type => $other ) {
    if ( isset( $staff[$staff_id][$type] ) ) continue; // we already have this staff member in a higher capacity
    $start = key( $other );
    end( $other );
    $end = key( $other );
    $staff[$staff_id][$type] = [
      'start' =>  $start,
      'end'   =>  $end
    ];
  }
}
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
    <?php 
    foreach ( $staff as $id => $more ) {
      foreach ( $more as $type => $groupInfo ) {
        $row = $staffInfo[$id];
        ?>

      <div style="position: relative">
        <div class='front'>
          <?php
          // figure out which image and color to use
          switch ( $row['grade'] ) {
            case '4':
              $color = '#009EDE';
              $img = "background\STAFF BG-1.png";
              break;
            case '5':
              $color = '#24b35f';
              $img = "background\STAFF BG-3.png";
              break;
            case '6':
              $color = '#f07621';
              $img = "background\STAFF BG-5.png";
              break;
            case '7':
              $color = '#fdcd06';
              $img = "background\STAFF BG-7.png";
              break;
            case '8':
              $color = '#a663a1';
              $img = "background\STAFF BG-9.png";
              break;
          }
          ?>
          <img src="<?= $img ?>" />
          <?php
          $class = '';
          if ( $type == 2 ) {
            $class = 'headCounselor';
          } else if ( $type == 3 ) {
            $class = 'safetyOfficer';
          }
          ?>
          <div class="role <?= $class ?>">
            <?= strtoupper( $row['role'] ) ?>
          </div>
          <div class='name staffName'>
            <?= $row['first_name'] . ' ' . $row['last_name'] ?>
          </div>
          <div class='staffInfo'>
            <?php
            if ( !empty( $row['grade'] ) ) echo "Grade " . $row['grade'];
            
            $groups = $staff[$id][$type];
            if ( $groups['start'] == $groups['end'] ) echo " / Bunk " . $groups['start'];
            else echo "<br />Bunks " . $groups['start'] . ' - ' . $groups['end'];
            ?>
            <br />
            <!-- Walking Group <?= $row['group_number'] ?> -->
          </div>
          <div class="staffBusInfo">
            <div class="busDetails">
              <div class="busDay">School Bus</div>
              <div class="busNum"><?= $row['school_bus'] ?></div>
            </div>
            <?php if ( $row['grade'] == 6 ) : ?>
              <div class="busDetails">
                <div class="busDay">Open Air Bus</div>
                <div class="busNum"><?= $row['open_air_bus'] ?></div>
              </div>
            <?php endif; ?>
            <div class="busDetails">
              <div class="busDay">Coach Bus</div>
              <div class="busNum"><?= $row['coach_bus'] ?></div>
            </div>
            <div class="busDetails">
              <div class="busDay">Sun PM Bus</div>
              <div class="busNum"><?= $row['sunday_pm_bus'] ?></div>
            </div>
          </div>                
        </div>
      </div>
      <div style="clear: both"></div>
      <div style="page-break-after: always;"></div>
    <?php
      }
    }
    ?>
  </div>
</body>
</html>