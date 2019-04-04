<?php
ini_set('display_errors',1);
require __DIR__ . "/../../db.php";
$qrys = [];
$headers = ['th_chidon_id', 'grade', 'bunk_number', 'test_table', 'round_number', 'sunday_pm_bus', 'cert_number', 'walking_group', 'host', 'host_number', 'acc_street_num', 
  'acc_street', 'between_streets1', 'between_streets2', 'team_id', 'school_bus', 'open_air_bus', 'coach_bus'];
if (($handle = fopen($_FILES['file']['tmp_name'], "r")) !== FALSE) {
  $row = 0;
  $numFields = count( $headers );
  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    if ( $row++ < 1 ) continue;
    $qry = "update th_chidon set ";
    for ( $i = 1; $i < $numFields; $i++ ) {
      if ( !empty( $data[$i] ) ) $qry .= $headers[$i] . " = '" . $data[$i] . "',";
    }
    $qry = substr( $qry, 0, strlen( $qry ) - 1 );
    $qry .= " where th_chidon_id = " . $data[0];
    $qrys[] = $qry;
  }
  fclose($handle);
}
//echo "<pre>"; print_r( $qrys ); echo "</pre>"; exit;
mysql_query('set autocommit=0');
mysql_query('begin');
$success = true;

foreach ( $qrys as $qry ) {
  if ( !mysql_query( $qry ) ) {
    echo "There was an error - " . $qry . "<br />" . mysql_error();
    $success = false;
    break;
  }
}

if ( $success ) mysql_query('commit');
else mysql_query('rollback');
mysql_query('set autocommit=1');
echo "done.";
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf8" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  </head>
  <body>
    <form action="chidon_children.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
      <label>Upload your spreadsheet
      <br /><input type="file" name="file" class="file"></label>
      <br /><input type="submit" name="submit" value="upload" />
    </form>
  </body>
</html>