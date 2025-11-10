<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    die('Access denied');
}

$sql = "select * from schools where chayolei = 1";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $school_id = $row['school_id'];
    $sql = "insert into school_subjects values($school_id, 136)";
    mysql_query($sql);
}
echo "Done";