<?
require_once 'db.php';
$sql = "select user_id, max(rank_ord) as medal from rank_marks 
		join users using (user_id) 
		where user_registered > 0 
		group by user_id";
$result = mysql_query($sql);
$users = array();
while ($row = mysql_fetch_assoc($result)) {
	if ($row['medal'] == 1)
		$users[] = $row['user_id'];
}

$final = array();
foreach ($users as $user) {
	$sql = "select * from medal_marks where user_id = " . $user;
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		$final[] = $user;
	}
}

echo count($users) . "<br />";
echo count($final);
?>