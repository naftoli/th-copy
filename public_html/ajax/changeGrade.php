<?
$user = $_GET['cid'];
$grade = $_GET['gid'];

require_once '../db.php';
if ($grade == 0)
    $sql = "update users set class_id = null where user_id = " . $user;
else
    $sql = "update users set class_id = " . $grade . " where user_id = " . $user;
mysql_query($sql);
?>