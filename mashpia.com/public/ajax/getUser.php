<?
$class = $_GET['id'];
require_once '../db.php';
$sql = "select user_id, last, first from users where class_id = " . $class . " order by last, first limit 1";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
echo $row['user_id'];
?>