<?
require '../db.php';

$admin_id = mysql_real_escape_string($_POST['admin']);
$year = mysql_real_escape_string($_POST['year']);
/*
$sql = "select u.user_id, u.last, u.first, u.user_registered from users u 
		join admin_auths aa on (aa.id = u.user_id) 
		join admins a using (admin_id) 
		where aa.admin_id = $admin_id 
		and aa.auth = 'user' 
		order by u.first";
 *
 */
$sql = "select u.user_id, u.last, u.first, ur.year from users u 
		join admin_auths aa on (aa.id = u.user_id) 
		join admins a using (admin_id) 
		left join user_registration ur using (user_id) 
		where aa.admin_id = $admin_id 
		and aa.auth = 'user' 
		order by u.first";
$result = mysql_query($sql);
if (mysql_num_rows($result) > 0) {
	$children = array();	
	while ($row = mysql_fetch_assoc($result)) {
		if ($row['reg_date'] == $year)
			$children['registered'][$row['user_id']] = $row['first'] . " " . $row['last'];
		else 
			$children['not-registered'][$row['user_id']] = $row['first'] . " " . $row['last'];
	}
	echo json_encode($children);
} else {
	echo json_encode(0);
}
?>