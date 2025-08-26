<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

if ( isset( $_FILES['add'] ) ) {
  $sql = "select max(auction_id) from auctions";
  $result = mysql_query( $sql );
  $row = mysql_fetch_assoc( $result );
  $auction_id = $row['auction_id'];

  if ( $file = fopen($_FILES['add']['tmp_name'], "r") ) {
    $updated = 0;

    while ( $data = fgetcsv( $file ) ) {
      $prize = $data[0];
      $serial = $data[1];

      if ( $prize && $serial ) {
        $sql = "insert ignore into auction_winners 
                set auction_id = " . $auction_id . ", 
                prize_id = " . $prize . ",  
                quantity = 1, 
                user_id = (select user_id from users where user_serial = " . $serial . ")";
//        echo $sql . "<br />";
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
    <title>Add Auction Winners</title>
  </head>

  <body>
    <form action="addAuctionWinners.php" method="post" enctype="multipart/form-data">
      Select file to upload:<br />
      <input type="file" name="add" id="add"><br /><br />
      <input type="submit" value="upload" name="submit">
    </form>
  </body>
</html>