<?php
ini_set('display_errors',1);
require __DIR__ . "/../../db.php";
$qrys = [];
if (($handle = fopen($_FILES['file']['tmp_name'], "r")) !== FALSE) {
  $row = 0;
  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    if ( $row++ < 1 ) continue;
    if ( empty( $data[0] ) ) continue;
    $qry = "update th_chidon set ";
    $qry .= "walking_group = '" . $data[1] . "',";
    $walking = strtolower( $data[2] ) == 'yes' ? 1 : 0;
    $qry .= " walking = " . $walking . ",";
    $qry .= " not_printed = 1";
    //$qry = substr( $qry, 0, strlen( $qry ) - 1 );
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
    <form action="updates.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
      <label>Upload your spreadsheet
      <br /><input type="file" name="file" class="file"></label>
      <br /><input type="submit" name="submit" value="upload" />
    </form>
  </body>
</html>