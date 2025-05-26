<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('max_execution_time', 600);

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
    // update the password if it is not hashed
    if ( !password_verify($row['password'], $row['hashed_pass']) ) {
        $sql = "update admins set hashed_pass = '" . password_hash($row['password'], PASSWORD_DEFAULT) . "' where admin_id = " . $row['admin_id'];
        mysql_query($sql);
    }
}
echo "Done.";