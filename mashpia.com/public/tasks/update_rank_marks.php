<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

$qrys = [];
$sql = "select * from medal_marks where date_awarded <= 2460060";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    if ($row['medal_ord'] > 0) {
        $greg = jdtogregorian($row['date_awarded']);
        $dateInfo = explode('/', $greg);
        $greg = $dateInfo[2] . '-' . $dateInfo[0] . '-' . $dateInfo[1];
        $sql = "update medal_marks set date_shipped = '" . $greg . "' where medal_ord = " . $row['medal_ord'] .
            " and subject_id = " . $row['subject_id'] . " and user_id = " . $row['user_id'] .
            " and date_awarded = " . $row['date_awarded'];
        $qrys[] = $sql;
    }
}
echo count($qrys) . " queries to run.<br>";

$success = true;
mysql_query('set autocommit=0');
mysql_query('start transaction');
foreach ($qrys as $qry) {
    if (!mysql_query($qry)) {
        $success = false;
        break;
    }
}
if ($success) {
    mysql_query('commit');
    echo "Success";
} else {
    mysql_query('rollback');
    echo "Failed";
}
mysql_query('set autocommit=1');