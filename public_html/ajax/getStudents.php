<?
require '../db.php';
$school_id = (int)$_POST['school'];
$users = array();
$sql = "select user_id, first, last from users where user_registered > 0 and school_id = " . 
	mysql_real_escape_string($school_id) . " order by last, first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[$row['user_id']] = $row['first'] . ' ' . $row['last'];
}
echo json_encode($users);
?>