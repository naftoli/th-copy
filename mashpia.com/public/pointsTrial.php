<?php
ini_set('display_errors',1);
$user_id = 19550;
require 'db.php';
require 'class.testPoints.php';
TestPoints::test( $user_id );

$sql = "select user_code from users where user_id = " . $user_id;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$usercode = $row['user_code'];

$mashpiaPoints = floor(mysql_result(mq(totalMarks("WHERE user_id = $user_id and mark_date >= 2457629")), 0));
echo "Mashpia Points: " . $mashpiaPoints . "<br />";