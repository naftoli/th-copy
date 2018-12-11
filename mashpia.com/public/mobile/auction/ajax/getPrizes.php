<?php
require '../../../db.php';
$user = mysql_real_escape_string($_POST['user']);
$admin = mysql_real_escape_string($_POST['admin']);
$auction = mysql_real_escape_string($_POST['auction']);

require '../../reg/ajax/encrypt.php';
$admin = encrypt_decrypt('decrypt', $admin);

// make sure user is part of admin account
$sql = "select * from admin_auths where id = " . $user . " and admin_id = " . $admin . " and role_id = 1";
$result = mysql_query($sql);
if (mysql_num_rows($result) > 0) {

    $sql = "select school_id, class_id from users where user_id = " . $user;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $school = $row['school_id'];
    $class_id = $row['class_id'];
    
    $prizes = array();
    $sql = "select p.prize_id, p.prize_name, p.prize_points, p.prize_image_id, ap.available 
            from prizes_auction p
            join auction_prizes ap using (prize_id)
            where ap.auction_id = " . $auction;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $prizes[$row['prize_points']][] = $row;
    }
    
    echo json_encode($prizes);
}
?>