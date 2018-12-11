<?
require '../db.php';

$user = mysql_real_escape_string($_POST['user_id']);
$level = mysql_real_escape_string($_POST['level']);

$sql = "update user_tracks set level = " . $level . " where user_id = " . $user . " and subject_id = 1";
mysql_query($sql);
?>