<?
$admin_auth = array('school'); 
require('header.php');

$sql = "select user_id, user_start_date from users where school_id = 65 and class_id in (2783,2785,2786)";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$s2 = "insert into rank_marks set rank_ord = 1, user_id = $row[user_id], date_promoted = $row[user_start_date]";
	echo $s2 . '<br />';
	mysql_query($s2);
}
?>