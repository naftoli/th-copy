<?php
require '../db.php';
$user = mysql_real_escape_string($_POST['user']);
$card = mysql_real_escape_string($_POST['card']);

if (empty($card)) {
    echo "No number was scanned.";
    exit;
}

$sql = "select * from pointsDB.achievement_cards where card_serial = " . $card;
//echo $sql; exit;
$result = mysql_query($sql);
if (mysql_num_rows($result) == 0) {
    $msg = "This number was not found in our system. Maybe the barcode wasn't scanned properly.";
} else {
    $row = mysql_fetch_assoc($result);
    $status = $row['status'];
    $card_school_id = $row['institution_id'];
    $card_class_id = $row['class_id'];
    $points = $row['card_points'];
    $achievement_card_id = $row['achievement_card_id'];
    $campaign_id = $row['campaign_id'];
    $mission_id = $row['mission_id'];
    $task_id = $row['task_id'];
    if ($status == 'scanned') {
        $msg = "This card has already been scanned.";
    } else {
        $sql = "select school_id, class_id from users where user_registered > 0 and user_id = " . $user;
        $result = mysql_query($sql);
        if (mysql_num_rows($result) == 0) {
            $msg = "Unauthorized Access.";
        } else {
            $row = mysql_fetch_assoc($result);
            $school_id = $row['school_id'];
            $class_id = $row['class_id'];
            if (empty($school_id) || empty($class_id)) {
                $msg = "You must be assigned to a school and class to be able to scan this card.";
            } else {
                if ($school_id != $card_school_id) {
                    $msg = "You are not in the correct school to scan this card.";
                } else {
                    if ($card_class_id > 0 && ($class_id != $card_class_id)) {
                        $msg = "You are not in the correct class to scan this card.";
                    } else {
                        
                        $sql1 = "update pointsDB.achievement_cards
                                set status = 'scanned'
                                where card_serial = " . $card;
                        $sql2 = "insert into pointsDB.user_points
                                set achievement_card_id = " . $achievement_card_id . ",
                                user_id = " . $user . ",
                                campaign_id = " . $campaign_id . ",
                                mission_id = " . $mission_id . ",
                                task_id = " . $task_id . ",
                                institution_id = " . $card_school_id . ",
                                class_id = " . $card_class_id . ",
                                points = " . $points . ",
                                created = now(), 
                                resource_name = 'specific achievement card'";
                        
                        mysql_query("set autocommit=0");
                        mysql_query("begin");
                        if (mysql_query($sql1) && mysql_query($sql2)) {
                            mysql_query("commit");
                            mysql_query("set autocommit=1");
                            $msg = "Congratulations! You have just earned " . $points . " points!";
                        } else {
                            mysql_query("rollback");
                            mysql_query("set autocommit=1");
                            $msg = "There was an error updating your account.";
                        }                        
                    }
                }
            }
        }
    }
}

echo $msg;
?>