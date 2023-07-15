<?php
require '../../../db.php';
$user = mysql_real_escape_string($_POST['user']);

$sql = "SELECT school_id, class_id, school_name, school_store FROM users LEFT JOIN schools USING ( school_id ) WHERE user_id = " . $user;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$school = $row['school_id'];
$class_id = $row['class_id'];
$school_name = $row['school_name'] ? $row['school_name'] : 'Your Base';

$prizes = [];
$message = false;
if ( !$row['school_store'] ) {
    if (isset($_COOKIE['lang']) && $_COOKIE['lang'] == 'he') {
        $message = "בית הספר / תלמוד תורה שלכם סגר את חנות הפרסים שלו.";
    } else {
        $message = "$school_name has closed it's prize store.";
    }
}
else {
    mysql_select_db('pointsDB');
    $sql = "SELECT prize_id, prize_name, prize_description, prizes.modified, prizes.created, points, image_id, one_per_user, prize_count, class_id 
            FROM pointsDB.prizes 
            LEFT JOIN pointsDB.prize_classes USING (prize_id)
            WHERE is_active = 1
            AND prize_count > 0 
            AND institution_id = " . $school . "
            AND (class_id is null or class_id = " . $class_id . ")";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $row['prize_description'] = urldecode( $row['prize_description'] );
        // make sure user hasn't already purchased this prize if it's a one time prize
        $oneTime = $row['one_per_user'];
        if ($oneTime) {
            $prizeID = $row['prize_id'];
            // find out when to start checking for the prize
            $qry = "select one_time_prize_reset from schools where school_id = " . $school;
            $res = mysql_query($qry);
            $reset = mysql_fetch_assoc($res);
            $one_time_reset = $reset['one_time_prize_reset'];
            // convert from jd to gregorian
            $greg = jdtogregorian($one_time_reset);
            $date = explode('/', $greg);
            $date = $date[2] . '-' . $date[0] . '-' . $date[1];
            $qry = "SELECT * FROM pointsDB.user_prizes WHERE prize_id = " . $prizeID . " AND user_id = " . $user . " AND is_reversed = 0 AND created >= '" . $date . "'";
            $res = mysql_query($qry);
            if (mysql_num_rows($res) > 0)
                continue;
        }
        // if not, add the prize to the results
        $prizes[$row['points']][] = $row;
    }
    if ( count( $prizes ) == 0 ) {
        if (isset($_COOKIE['lang']) && $_COOKIE['lang'] == 'he') {
            $message = "נראה שבבית הספר / תלמוד תורה שלכם אין פרסים זמינים";
        } else {
            $message = "It appears that $school_name has no prizes available.";
        }
    }
}

header("Content-Type: application/json; charset=utf-8;");
echo json_encode([
    'prizes' => $prizes,
    'message' => $message
]);
?>