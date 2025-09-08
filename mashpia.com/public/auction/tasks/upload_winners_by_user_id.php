<?php
$admin_auth = ['school'];
require '../../header.php';

if ($admin_user['auth'] != 'super') {
    echo "You are not authorized to access this page.";
    exit;
}

// get auction id
$sql = "select auction_id from auctions order by auction_id desc limit 1";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$auction_id = intval($row['auction_id']);
echo "Auction ID: " . $auction_id . "<br />";

// get winners from file
if ( isset( $_FILES['add'] ) ) {
    if ( $file = fopen($_FILES['add']['tmp_name'], "r") ) {
        // start transaction
        mysql_query("set autocommit = 0");
        mysql_query("start transaction");

        $success = true;
        $updated = 0;

        $qry = "delete from auction_winners where auction_id = " . $auction_id;
        if (! mysql_query($qry)) {
            $success = false;
            echo $qry . "<br />" . mysql_error();
        } else {
            while ($data = fgetcsv($file)) {
                $prize = intval($data[0]);
                $user_id = intval($data[1]);

                if ($auction_id > 0 && $user_id > 0 && $prize > 0) {
                    $sql = "insert into auction_winners (auction_id, user_id, prize_id, quantity) 
                            values ($auction_id, $user_id, $prize, 1)";
                    if (mysql_query($sql)) {
                        $updated++;
                        echo $sql . "<br />";
                    } else {
                        $success = false;
                        echo $sql . "<br />" . mysql_error();
                        break;
                    }
                }
            }
        }

        if ( $success ) {
            mysql_query("commit");
            echo "Auction winners have been set.";
        } else {
            mysql_query("rollback");
            echo "Error setting auction winners.";
        }
        mysql_query("set autocommit = 1");
    }
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf8" />
    <title>Set Auction Winners</title>
  </head>

  <body>
    <form action="upload_winners_by_serial.php" method="post" enctype="multipart/form-data">
      Select file to upload:<br />
      <input type="file" name="add" id="add"><br /><br />
      <input type="submit" value="upload" name="submit">
    </form>
  </body>
