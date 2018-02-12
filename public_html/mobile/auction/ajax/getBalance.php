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
    
    /*
    $mashpiaPoints = floor(mysql_result(mq(totalMarks("WHERE user_id = $user and mark_date >= $date")), 0));
    
    // get user barcode
    $sql = "select user_code from users where user_id = " . $user;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $usercode = $row['user_code'];
    
    //$earned = header_total_points(array("user_code" => $row['user_code']));
    //$available = header_store_points(array("user_code" => $row['user_code']));
    $auction_points = header_auction_points(array(
        "user_code" => $usercode,
        "auction_date" => $date
    ));
    
    if (floor($auction_points[$usercode] + $mashpiaPoints) >= 1200) {
        $mashpiaPoints = floor(mysql_result(mq(totalMarks("WHERE user_id = $user")), 0));
        $auction_points = header_total_points(array("user_code" => $usercode));
    }
    /*
    // deduct existing raffle tickets
    $deduct = 0;
    $sql = "select prize_points, quantity from auction_user_prizes aup 
            join prizes_auction pa using (prize_id)
            where aup.auction_id = " . $auction . "
            and aup.user_id = " . $user;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $amount = $row['quantity'] * $row['prize_points'];
        $deduct += $amount;
    }
    */
    /*
    $v2_points = header_total_points(array("user_code" => $usercode));
    $totalPoints = floatval(mysql_result(mq(totalMarks("WHERE user_id = $user")), 0));
    $balance['tpoints'] = floor($totalPoints + $v2_points[$usercode]);
    
    $balance['mpoints'] = $mashpiaPoints;
    $balance['auction_points'] = floor($auction_points[$usercode]);
    $balance['earned'] = floor($mashpiaPoints + $v2_points[$usercode]);
    $balance['available'] = floor($mashpiaPoints + $auction_points[$usercode]);
    */
    require '../../../class.points.php';
    $p = new Points( $user );
    $balance['tpoints'] = $p->getTotalPoints();
    $balance['earned'] = $p->getTotalThisYear();
    $balance['available'] = $p->getAuctionPoints( $date );
    
    echo json_encode($balance);
}
?>