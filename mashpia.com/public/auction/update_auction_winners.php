<?php
ini_set('display_errors',1);
require_once '../db.php';

$auction_id = 83;

if ( isset( $_FILES['winners'] ) ) {
  $qrys = [];
  if ( ($handle = fopen($_FILES['winners']['tmp_name'], "r")) !== FALSE ) {
    while ( ($data = fgetcsv($handle, 0, ",")) !== FALSE ) {
      $num = count($data);
      for ( $c = 0; $c < $num; $c++ ) {
        $value = trim( $data[$c] );
        switch ( $c ) {
          case 0:
            $prize_id = intval($value);
            break;
          case 1:
            $user_id = intval($value);
            break;
        }
      }
      //echo "ID: " . $id . "; Phone: " . $phone . "; Email: " . $email . "; Caller: " . $caller . "<br />";
      // build query 
      $qry = "insert into auction_winners 
              set user_id = " . $user_id . ", 
              prize_id = " . $prize_id . ", 
              quantity = 1, 
              auction_id = " . $auction_id;
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
    <meta charset="utf8">
  </head>
  <body>
    <form enctype="multipart/form-data" action="update_auction_winners.php" method="POST">
      <!-- Name of input element determines name in $_FILES array -->
      Send this file: <input name="winners" type="file" />
      <input type="submit" value="Upload File" />
    </form>
  </body>
</html>
<?php } ?>