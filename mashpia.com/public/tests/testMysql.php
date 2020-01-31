<?php
ini_set('display_errors',1);
require_once '../db.php';
var_dump($link);
require_once '../api/header/db.php';
$sql = "select * from admins limit 1";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
var_dump($row);