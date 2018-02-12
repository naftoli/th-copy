<?
require 'db.php';

$users = array();
$sql = "select * from users where (he_name is not null and he_name != '')";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[] = $row;
}

echo "<pre>"; print_r($users); echo "</pre>";
