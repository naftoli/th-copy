<?php
ini_set('display_errors',1);
require __DIR__ . "/../../db.php";
$qrys = [];
$headers = ['staff_id', 'staff_type_id', 'group_number'];
if (($handle = fopen($_FILES['file']['tmp_name'], "r")) !== FALSE) {
  $row = 0;
  $numFields = count( $headers );
  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    if ( $row++ < 1 ) continue;
    $groupList = explode(',', $data[2]);
    foreach ( $groupList as $group ) {
      $qry = "insert into th_chidon_staff_assignments 
              set staff_id = " . $data[0] . ", 
              group_number = '" . $group . "', 
              staff_type_id = " . $data[1];
      $qrys[] = $qry;
    }
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
    <form action="update_staff_groups.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
      <label>Upload your spreadsheet
      <br /><input type="file" name="file" class="file"></label>
      <br /><input type="submit" name="submit" value="upload" />
    </form>
  </body>
</html>