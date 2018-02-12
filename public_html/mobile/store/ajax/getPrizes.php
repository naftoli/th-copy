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

    $sql = "select school_id, class_id from users where user_id = " . $user;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $school = $row['school_id'];
    $class_id = $row['class_id'];
    
    mysql_select_db('pointsDB');
    $prizes = array();
    $sql = "select prize_id, prize_name, prize_description, points, image_id, one_per_user, prize_count, class_id from pointsDB.prizes 
            left join pointsDB.prize_classes using (prize_id)
            where is_active = 1
            and prize_count > 0 
            and institution_id = " . $school . "
            and (class_id is null or class_id = " . $class_id . ")";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        // make sure user hasn't already purchased this prize if it's a one time prize
        $oneTime = $row['one_per_user'];
        if ($oneTime) {
            $prizeID = $row['prize_id'];
            $qry = "select * from user_prizes where prize_id = " . $prizeID . " and user_id = " . $user . " and is_reversed = 0";
            $res = mysql_query($qry);
            if (mysql_num_rows($res) > 0) {
                continue;
            }
        }    
        $prizes[$row['points']][] = $row;
    }
    echo json_encode($prizes);
}
?>