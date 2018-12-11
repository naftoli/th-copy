<?php
require '../../../db.php';
$user = (int)mysql_real_escape_string($_POST['user']);
$admin = mysql_real_escape_string($_POST['admin']);
$auction = (int)mysql_real_escape_string($_POST['auction']);

require '../../reg/ajax/encrypt.php';
$admin = encrypt_decrypt('decrypt', $admin);

// make sure user is part of admin account
$sql = "select * from admin_auths where id = " . $user . " and admin_id = " . $admin . " and role_id = 1";
$result = mysql_query($sql);
if (mysql_num_rows($result) > 0) {
    // get start date of auction
    $sql = "select auction_points_start_date from auctions where auction_id = " . $auction;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $date = $row['auction_points_start_date'];
    
    require '../../../class.points.php';
    $p = new Points( $user );
    $balance['tpoints'] = $p->getTotalPoints();
    $balance['earned'] = $p->getTotalThisYear();
    $balance['available'] = $p->getAuctionPoints( $date );
    
    echo json_encode($balance);
}
?>