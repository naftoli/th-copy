<?php
ini_set('display_errors',1);
require_once '../../db.php';

if ( isset( $_FILES['charidy'] ) ) {
  $qrys = [];
  if ( ($handle = fopen($_FILES['charidy']['tmp_name'], "r")) !== FALSE ) {
    while ( ($data = fgetcsv($handle, 0, ",")) !== FALSE ) {
      $num = count($data);
      for ( $c = 0; $c < $num; $c++ ) {
        $value = trim( $data[$c] );
        switch ( $c ) {
          case 0:
            $id = intval( $value );
            break;
          case 1:
            $first_name = $value;
            break;
          case 2:
            $last_name = $value;
            break;
          case 3:
            $phone = $value;
            break;
          case 4:
            $email = $value;
            break;
        }
      }
      //echo "ID: " . $id . "; Phone: " . $phone . "; Email: " . $email . "; Caller: " . $caller . "<br />";
      // build query 
      $qry = "update mashpia_charidy.donors 
              set first_name = '" . addslashes( $first_name ) . "', 
              last_name = '" . addslashes( $last_name ) . "', 
              phone = '" . $phone . "', 
              email = '" . $email . "'  
              where donor_id = " . $id;
      $qrys[] = $qry;
    }
    mysql_query('set autocommit = 0');
    mysql_query('begin');
    $success = true;
    foreach ( $qrys as $qry ) { 
      //echo $qry . "<br />";
      if ( !mysql_query( $qry ) ) {
        $success = false;
        break;
      }
    }
    if ( $success ) {
      mysql_query('commit');
      echo "done.";
    } else {
      mysql_query('rollback');
      echo "errors.";
      echo "<br />" . mysql_error() . "<br />" . $qry . "<br />";
    }
  }
} else {
?>
<!DOCTYPE html>
<html>
  <head>
  </head>
  <body>
    <form enctype="multipart/form-data" action="update_donors.php" method="POST">
      <!-- Name of input element determines name in $_FILES array -->
      Send this file: <input name="charidy" type="file" />
      <input type="submit" value="Upload File" />
    </form>
  </body>
</html>
<?php } ?>