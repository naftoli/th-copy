<?php
ini_set('display_errors', 1);
require 'db.php';
$filename = "deleted/tasks_50605";
$contents = file_get_contents($filename);
$info = (array)json_decode($contents);

$filename2 = "deleted/tasks_6658";
$contents2 = file_get_contents($filename2);
$info2 = (array)json_decode($contents2);

$users = array_keys($info);
$users2 = array_keys($info2);

echo "<pre>";
print_r($users);
print_r($users2);
echo "</pre>";