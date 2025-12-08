<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

if ($admin_user['auth'] != 'super') {
    die('No Permission');
}

$tasks = [];
$sql = "select * from date_tasks where name like '%onclick=showAudioPlaye'";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $tasks[] = $row;
}

foreach ($tasks as $task) {
    $id = $task['date_task_id'];
    $name = $task['name'];
    $pos = strrpos($name, ',');
    $name = substr($name, 0, $pos);
    $sql = "update date_tasks set name = '" . $name . "' where date_task_id = " . $id;
    mysql_query($sql);
}

echo "Done";