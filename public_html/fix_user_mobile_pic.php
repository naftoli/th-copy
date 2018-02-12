<?php
require 'db.php';

$info = array();
$sql = "select user_id, user_photo_id from users_bak where user_photo_id is not null";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $info[] = $row;
}

$updated = 0;
foreach ($info as $row) {
    $sql = "update users set user_photo_id = '" . $row['user_photo_id'] . "' where user_id = " . $row['user_id'] . " and user_photo_id = null";
    if (mysql_query( $sql )) {
        $updated++;
    }
}
echo "Updated: " . $updated;