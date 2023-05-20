<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

$sql = "select * from medal_marks where date_awarded <= 2460060";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $greg = jdtogregorian($row['date_awarded']);
    $sql = "update medal_marks set date_shipped = '" . $greg . "' where medal_ord = " . $row['medal_ord'] .
        " and subject_id = " . $row['subject_id'] . " and user_id = " . $row['user_id'] .
        " and date_awarded = " . $row['date_awarded'];
    echo $sql . "<br>";
}