<?php
require '../../../db.php';
$user = mysql_real_escape_string($_POST['user']);
$admin = mysql_real_escape_string($_POST['admin']);

require '../../reg/ajax/encrypt.php';
$admin = encrypt_decrypt('decrypt', $admin);

// make sure user is part of admin account
$sql = "select * from admin_auths where id = " . $user . " and admin_id = " . $admin . " and role_id = 1";
$result = mysql_query($sql);
if (mysql_num_rows($result) > 0) {
    /*
    // get user barcode
    $sql = "select user_code from users where user_id = " . $user;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $usercode = $row['user_code'];
    
    $earned = header_total_points(array("user_code" => $usercode));
    $available = header_store_points(array("user_code" => $usercode));
    
    $totalPoints = floatval(mysql_result(mq(totalMarks("WHERE user_id = $user")), 0));
    $mashpiaPoints = floor(mysql_result(mq(totalMarks("WHERE user_id = $user and mark_date >= 2457629")), 0));
    
    $balance['tpoints'] = floor($totalPoints + $earned[$usercode]);
    $balance['mpoints'] = $mashpiaPoints;
    $balance['earned'] = floor($mashpiaPoints + $earned[$row['user_code']]);
    $balance['available'] = floor($mashpiaPoints + $available[$row['user_code']]);
    */
    require '../../../class.points.php';
    $p = new Points( $user );
    $balance['tpoints'] = $p->getTotalPoints();
    $balance['earned'] = $p->getTotalThisYear();
    $balance['available'] = $p->getStorePoints();
    
    echo json_encode($balance);
}
?>