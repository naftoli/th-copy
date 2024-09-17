<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once '../header.php';

// make sure we are super
if ( $admin_user['auth'] != 'super' ) {
    echo 'You are not authorized to view this page.';
    exit;
}

// create cron job to delete admin_auth associations that point to missing child
$sql = "delete from admin_auths where auth = 'user' and id not in (select user_id from users)";
if ( mysql_query($sql) ) echo "Deleted extra children.";
else echo "Failed to delete extra children.";
