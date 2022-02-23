<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require __DIR__ . '/../../../db.php';

$id = $_COOKIE['customer_id'];
$admin = mysql_real_escape_string($_POST['admin_id']);

if ($id && $admin) {
    $sql = "update admins set authorize_customer_profile_id = '$id' where admin_id = " . $admin;
    mysql_query($sql);
}
