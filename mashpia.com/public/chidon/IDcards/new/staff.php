<?php
ini_set('display_errors',1);
require __DIR__ . '/../../../db.php';
require __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$info = [];
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
          and year = $year 
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
          if ( $row['staff_type_id'] == 2 ) {
            $class = 'headCounselor';
          } else if ( $row['staff_type_id'] == 3 ) {
            $class = 'safetyOfficer';
          }
          ?>
          <div class="role <?= $class ?>">
            <?= strtoupper( $row['role'] ) ?>
          </div>
          <div class='name'>
            <?= $row['first_name'] . ' ' . $row['last_name'] ?>
          </div>
          <div class='staffInfo'>
            Grade <?= $row['grade'] ?> / 
            Bunk <?= $row['group_number']?> <br />
            Walking Group <?= $row['group_number'] ?>
          </div>
          <div class='busInfo'>
            YEARS AT CHIDON
        </div>  
        </div>
      </div>
      <div style="clear: both"></div>
      <div style="page-break-after: always;"></div>
    <?php endforeach; ?>
  </div>
</body>
</html>