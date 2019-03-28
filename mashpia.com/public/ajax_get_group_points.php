<?
require('db.php');

$group_id = $_GET['group_id'];

$sql = "SELECT points FROM camp_groups WHERE group_id=" . $group_id;
$query = mq($sql);
$row = mysql_fetch_assoc($query);
echo $row['points'];
?>
