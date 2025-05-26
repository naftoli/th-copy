<?php
$admin_auth = ['school'];
require_once '../header.php';

// make sure we are super
if ( $admin_user['auth'] != 'super' ) {
    echo 'You are not authorized to view this page.';
    exit;
}

// get the list of admins
$sql = "select * from admins";
$result = mysql_query($sql);
while ( $row = mysql_fetch_assoc($result) ) {
    // update the password
    $sql = "update admins set hashed_pass = '" . password_hash($row['password'], PASSWORD_DEFAULT) . "' where admin_id = " . $row['admin_id'];
    mysql_query($sql);
}
echo "Done.";