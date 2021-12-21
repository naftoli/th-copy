<?php
$admin_auth = array('school');
require('../header.php');

$info = $_POST['info'];

$updates = [];
foreach ($info as $item) {
    $year = mysql_real_escape_string($item['year']);
    $user = mysql_real_escape_string($item['user']);
    $type = mysql_real_escape_string($item['type']);
    $lines = mysql_real_escape_string($item['lines']);

    // first get campaign id
    $sql = "select id from line_campaigns where year = $year and type = '$type'";
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $id = $row['id'];

    // update lines in summary table
    if ($lines != '') {
        $sql = "update bp_user_summary set num_lines = $lines where campaign_id = $id and user_id = $user";
        $updates[] = $sql;
    }
}

$success = true;
mysql_query('set autocommit=0');
mysql_query('begin');
foreach ($updates as $qry) {
    if (!mysql_query($qry)) {
        $success = false;
        break;
    }
}

if ($success) {
    mysql_query('commit');
    mysql_query('set autocommit=1');
} else {
    mysql_query('rollback');
    mysql_query('set autocommit=1');
}

echo json_encode([
    'success' => $success
]);