<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

$table_info = [];
$sql = "show columns from users";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $table_info[] = $row['Field'];
}

$qrys = [];
$serials = [7782529];
$sql = "select * from mashpia_backup2.users where user_serial in (" . implode(',', $serials) . ")";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $sql = "insert ignore into users set ";
    foreach ($table_info as $field) {
        $sql .= $field . "=\"" . mysql_real_escape_string($row[$field]) . "\", ";
    }
    $sql = substr($sql, 0, -2);
    mysql_query($sql) or die(mysql_error() . "<br />" . $sql . "<br />");
}
echo "done.";