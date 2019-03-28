<?
require 'db.php';
require_once 'class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

$ids = array();
$sql = "select user_id from user_registration where year = " . $year;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$ids[] = $row['user_id'];
}

$updated = 0;
foreach ($ids as $id) {
	$sql = "select user_registered from users where user_id = " . $id;
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	if (! $row['user_registered'] > 0) {
		if (! $row['user_start_date'] > 0) {
			$sql = "update users set user_registered = now(), user_start_date = " . unixtojd() . " where user_id = " . $id;
		} else {
			$sql = "update users set user_registered = now() where user_id = " . $id;
		}
		if (mysql_query($sql)) {
			$updated++;
		}
	}
}

echo "Number of users: " . count($ids) . "<br />";
echo "Updated: " . $updated;
?>