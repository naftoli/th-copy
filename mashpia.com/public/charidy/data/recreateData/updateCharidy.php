<?php
ini_set('display_errors',1);
require_once '../../../db.php';

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
            $phone = $value;
            break;
          case 2:
            $email = $value;
            break;
          case 3:
            $caller = intval( $value );
            break;
        }
      }
      //echo "ID: " . $id . "; Phone: " . $phone . "; Email: " . $email . "; Caller: " . $caller . "<br />";
      // build query 
      $updatePhone = false;
      $updateEmail = false;
      if ( $phone != '' ) $updatePhone = true;
      if ( $email != '' && filter_var($email, FILTER_VALIDATE_EMAIL) ) $updateEmail = true;
      if ( $phone || $email ) {
        $qry = "update mashpia_charidy.donors set ";
        if ( $updatePhone ) $qry .= "phone   = '" . $phone . "'";
        if ( $updatePhone && $updateEmail ) $qry .= ", ";
        if ( $updateEmail ) $qry .= "email = '" . $email . "'";
        $qry .= " where donor_id = " . $id;
        $qrys[] = $qry;
      }
      // if ( $caller > 0 ) {
      //   $sql = "insert into mashpiadb.charidy_donors_callers 
      //           set donor_id = " . $id . ", 
      //           charidy_caller_id = " . $caller . ", 
      //           year = 5779";
      //   $qrys[] = $sql;
      // }
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
    <form enctype="multipart/form-data" action="updateCharidy.php" method="POST">
      <!-- Name of input element determines name in $_FILES array -->
      Send this file: <input name="charidy" type="file" />
      <input type="submit" value="Upload File" />
    </form>
  </body>
</html>
<?php } ?>