<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

$user_id = $_POST['user'];

$sql = "update admins set chidon_confirmed_5782 = 0 where admin_id = (
    select admin_id from admin_auths where auth = 'user' and id = $user_id
)";
if (mysql_query($sql)) echo 1;
else echo 0;