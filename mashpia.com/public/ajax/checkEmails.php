<?
require_once '../db.php';
$users = $_POST['users'];

$missing = array();
$sql = "select first, last, email from users where user_id in (" . implode(',', $users) . ")";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$name = $row['first'] . ' ' . $row['last'];
	$email = $row['email'];
	if (empty($email) || strpos($email, '@') === false) {
		$missing[] = $name;
	}
}

echo json_encode($missing);
?>