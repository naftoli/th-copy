<?php
require '../db.php';

// get auction id
$sql = "select auction_id from auctions order by auction_id desc limit 1";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$auction_id = $row['auction_id'];

// get winners from file
if ( isset( $_FILES['add'] ) ) {
    if ( $file = fopen($_FILES['add']['tmp_name'], "r") ) {
        // start transaction
        mysql_query("set autocommit = 0");
        mysql_query("start transaction");

        $success = true;
        $updated = 0;
        $numRows = 0;

        $qry = "delete from auction_winners where auction_id = " . $auction_id;
        if (! mysql_query($qry)) {
            $success = false;
            echo $qry . "<br />" . mysql_error();
        } else {
            while ($data = fgetcsv($file)) {
                $numRows++;
                $prize = $data[0];
                $serial = $data[1];

                // find out user id from serial
                $sql = "select user_id from users where user_serial = " . $serial;
                $result = mysql_query($sql);
                if (! $result) {
                    $success = false;
                    echo $sql . "<br />" . mysql_error();
                    break;
                }
                $row = mysql_fetch_assoc($result);
                $user_id = $row['user_id'];

                if ($auction_id && $user_id && $prize) {
                    $sql = "insert into auction_winners 
                            set quantity = 1,
                            user_id = " . $user_id . ", 
                            prize_id = " . $prize . ",  
                            auction_id = " . $auction_id;
                    echo $sql . "<br />";
                    $updated++;
//                    if (mysql_query($sql)) $updated++;
//                    else {
//                        $success = false;
//                        break;
//                    }
                } else {
                    $success = false;
                    echo "auction_id: " . $auction_id . "<br />";
                    echo "serial number: " . $serial . "<br />";
                    echo "user_id: " . $user_id . "<br />";
                    echo "prize: " . $prize . "<br />";
                    break;
                }
            }
        }

        if ($success && $updated != $numRows) {
            $success = false;
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
    <form action="update_winners.php" method="post" enctype="multipart/form-data">
      Select file to upload:<br />
      <input type="file" name="add" id="add"><br /><br />
      <input type="submit" value="upload" name="submit">
    </form>
  </body>
