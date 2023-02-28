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

$updated = 0;
//$serials = [7749798, 7780392, 7780394];
//$sql = "select * from mashpia_backup2.users where user_serial in (" . implode(',', $serials) . ")";
$sql = "SELECT * FROM mashpia_backup2.users where user_id not in (select user_id from users)";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $sql = "insert ignore into users set ";
    foreach ($table_info as $field) {
        $sql .= $field . "=\"" . mysql_real_escape_string($row[$field]) . "\", ";
    }
    $sql = substr($sql, 0, -2);
    mysql_query($sql) or die(mysql_error() . "<br />" . $sql . "<br />");
    $updated++;
}
echo "Updated: " . $updated;