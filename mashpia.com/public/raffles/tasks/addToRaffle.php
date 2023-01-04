<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

if ( isset( $_FILES['add'] ) ) {
  $raffle = $_POST['number'];

  if ( $file = fopen($_FILES['add']['tmp_name'], "r") ) {
    $updated = 0;

    while ( $data = fgetcsv( $file ) ) {
      $serial = $data[0];
      $school = $data[1];
      $prize = $data[2];

      // find out user id from serial
      $sql = "select user_id from users where user_serial = " . $serial;
      $result = mysql_query( $sql );
      $row = mysql_fetch_assoc( $result );
      $user_id = $row['user_id'];

      if ( $raffle && $user_id ) {
        $sql = "insert ignore into raffle_winners 
                set user_id = " . $user_id . ", 
                prize_id = " . $prize . ",  
                raffle_id = " . $raffle;
        //echo $sql . "<br />";
        if ( mysql_query( $sql ) ) $updated++;
      }
    }
    echo "Updated: " . $updated;
  }
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf8" />
    <title>Add To Raffle</title>
  </head>

  <body>
    <form action="addToRaffle.php" method="post" enctype="multipart/form-data">
      Enter Raffle Number:<br />
      <input type="text" name="number" /><br /><br />
      Select file to upload:<br />
      <input type="file" name="add" id="add"><br /><br />
      <input type="submit" value="upload" name="submit">
    </form>
  </body>
</html>