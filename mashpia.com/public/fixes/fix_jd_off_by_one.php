<?php
$admin_auth = ['school'];
require '../header.php';

// make sure it's super admin
if ($admin_user['auth'] != 'super') {
    die('You are not authorized to run this script');
}

$start = 2460552;
$end = $start + 6;
$final = 2460915;

$success = true;
mysql_query('START TRANSACTION');
while ($end < $final) {
    $sql = "UPDATE date_tasks_missions 
            SET start_date = start_date + 1, end_date = end_date + 1
            WHERE start_date = $start AND end_date = $end";
    if (! mysql_query($sql)) {
        $success = false;
        break;
    }
    $start += 7;
    $end += 7;
}

if ($success) {
    mysql_query('COMMIT');
    echo 'Success';
} else {
    mysql_query('ROLLBACK');
    echo 'Failed';
}