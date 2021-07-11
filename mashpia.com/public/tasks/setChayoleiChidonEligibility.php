<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

function getDate($subtractFromYear) {
    // get todays date in gregorian calendar
    $today = jdtogregorian(unixtojd());
    $todayArr = explode('/', $today);
    return (intval($todayArr[2]) - $subtractFromYear) . '-' . $todayArr[0] . '-' . $todayArr[1];
}

function updateChayolei() {
    $barMitzva = getDate(13);
    $basMitzva = getDate(12);

    $sql1 = "update users set chayolei_eligible = 0 where dob <= '" . $basMitzva . "' and gender = 'F'";
    $sql2 = "update users set chayolei_eligible = 0 where dob <= '" . $barMitzva . "' and gender = 'M'";
    $sql3 = "update users u 
            join classes c using (class_id) 
            set chayolei_eligible = 1 
            where u.gender = 'M' 
            and u.dob > '" . $barMitzva . "'
            and u.user_registered > 0 
            and u.school_id != 612";
    $sql4 = "update users u 
            join classes c using (class_id) 
            set chayolei_eligible = 1 
            where u.gender = 'F' 
            and u.dob > '" . $basMitzva . "'
            and u.user_registered > 0 
            and u.school_id != 612";
    return mysql_query($sql1) && mysql_query($sql2) && mysql_query($sql3) && mysql_query($sql4);
}

function updateChidon() {
    $sql1 = "update users u 
            join classes c using (class_id) 
            set chidon_eligible = 0 
            where c.class_grade = 8 
            or c.class_grade < 3";
    $sql2 = "update users u
            join classes c using (class_id) 
            set chidon_eligible = 1 
            where c.class_grade >= 3 
            and c.class_grade < 8 
            and u.school_id != 612";
    return mysql_query($sql1) && mysql_query($sql2);
}

if (! isset($_GET['yearly'])) {
    if (!updateChayolei()) {
        echo "Error updating chayolei eligibility";
    }
    if (!updateChidon()) {
        echo "Error updating chidon eligibility";
    }
    echo "<br />Done.";
}