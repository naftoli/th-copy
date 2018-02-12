<?php
require 'db.php';

$parents = array();
$sql = "select a.*, s.school_name from admins a 
		join admin_auths aa using (admin_id) 
		join users u on u.user_id = aa.id 
		where u.user_registered > 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$parents[$row['school_name']][] = $row;
}
?>