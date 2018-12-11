<?
require 'db.php';
$users = array();
$sql = "select user_id from users where school_id = 14";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[] = $row['user_id'];
}

foreach ($users as $id) {
	$sql = "insert into user_registration 
			set user_id = $id, 
			admin_id = 38, 
			year = '5776', 
			reg_date = now()";
	mysql_query($sql);
}
