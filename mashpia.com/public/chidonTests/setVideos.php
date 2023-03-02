<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

$num_videos = mysql_real_escape_string($_POST['numVideos']);
$school_id = mysql_real_escape_string($_POST['school_id']);

$sql = "update schools set num_chidon_videos = $num_videos where school_id = " . $school_id;
if (mysql_query($sql)) echo 0; // no errors
else echo 1;