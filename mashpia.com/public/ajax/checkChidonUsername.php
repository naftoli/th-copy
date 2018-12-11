<?
require '../db.php';
$username = mysql_real_escape_string(trim($_POST['username']));
$year = mysql_real_escape_string($_POST['year']);
$sql = "select * from chidon_schools where year = $year and username = '" . $username . "'";
$result = mysql_query($sql);
if (mysql_num_rows($result) > 0) {
	echo 1;
} else {
	echo 0;
}