<?
require_once 'db.php';

$users = array();
$sql = "select aa.id, a.admin_email from admin_auths aa 
		join admins a using (admin_id) 
		where aa.auth = 'user'";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$user_id = $row['id'];
	$email = $row['admin_email'];
	$users[$user_id] = $email;
}

foreach ($users as $id => $email) {
	$sql = "select email from users where user_id = $id";
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	
	if (!empty($email) && !empty($row['email']) && $row['email'] != $email) {
		echo "User ID: " . $id . "<br />";
		echo "Parent Email: " . $email . "<br />";
		echo "User Email: " . $row['email'] . "<br /><br />";
	}
}
?>