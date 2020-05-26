<?php
ini_set('display_errors',1);
$admin_auth = ['school'];
require '../../header.php';

if ( $admin_user['auth'] != 'super' ) {
    echo "No Permissions.";
    exit;
}

if ( isset($_FILES['file']) ) {
    $qrys = [];
    $headers = ['th_chidon_id', 'cert_number', 'award_type'];
    if (($handle = fopen($_FILES['file']['tmp_name'], "r")) !== FALSE) {
    $row = 0;
    $numFields = count( $headers );
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $qry = "update th_chidon set ";
        for ( $i = 1; $i < $numFields; $i++ ) {
            $qry .= $headers[$i] . " = '" . $data[$i] . "',";
        }
        $qry = substr( $qry, 0, strlen( $qry ) - 1 );
        $qry .= " where th_chidon_id = " . $data[0];
        $qrys[] = $qry;
    }
    fclose($handle);
    }
    // echo "<pre>"; print_r( $qrys ); echo "</pre>"; exit;
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
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf8" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  </head>
  <body>
    <form action="awards.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
      <label>Upload your spreadsheet
      <br /><input type="file" name="file" class="file"></label>
      <br /><input type="submit" name="submit" value="upload" />
    </form>
  </body>
</html>