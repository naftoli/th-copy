<?php
$admin_auth = ['school'];
require '../header.php';

if ($admin_user['auth'] != 'super') {
    echo 'You are not authorized to view this page';
    exit;
}

require '../class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

$serial = $_GET['serial'];
if (!$serial) {
    echo 'No serial number provided';
    exit;
}

$sql = "SELECT user_id FROM `users` WHERE `user_serial` = '$serial'";
$result = mysql_query($sql);
if (!$result) {
    echo 'Error: ' . mysql_error();
    exit;
} else {
    $row = mysql_fetch_assoc($result);
    if (!$row) {
        echo 'No user found with serial number ' . $serial;
        exit;
    } else {
        $user = $row['user_id'];
    }
}

$removed = true;
mysql_query('SET AUTOCOMMIT=0');
mysql_query('START TRANSACTION');

$sql = "update users set user_registered = null where user_id = $user";
$result = mysql_query($sql);
if (!$result) {
    echo 'Error: ' . $sql . "<br />" . mysql_error();
    $removed = false;
} else {
    $sql = "delete from user_registration where user_id = $user and year = $year";
    $result = mysql_query($sql);
    if (!$result) {
        echo 'Error: ' . $sql . "<br />" . mysql_error();
        $removed = false;
    } else {
        $sql = "delete from registration_charges where user_id = $user and year = $year and type like '%THE%'";
        $result = mysql_query($sql);
        if (!$result) {
            echo 'Error: ' . $sql . "<br />" . mysql_error();
            $removed = false;
        }
    }
}

if ($removed) {
    mysql_query('COMMIT');
    echo 'User unregistered successfully';
} else {
    mysql_query('ROLLBACK');
    echo 'Error: User not unregistered';
}


