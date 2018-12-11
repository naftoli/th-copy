<?php
if (!$_COOKIE['user_id']) { 
	$page = 'http://mashpia.com/statement.php';
	header('Location: http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . "://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . $page);
}

function get_user_id() {
	return $_COOKIE['user_id'];
}

function get_camp_id($user_id) {
	$sql = "SELECT camp_id FROM users WHERE user_id=" . $user_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$camp_id = $row['camp_id'];
			
	return $camp_id;
}
?>