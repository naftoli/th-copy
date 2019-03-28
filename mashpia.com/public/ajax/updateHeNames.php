<?
require '../db.php';

$user_id = mysql_real_escape_string( $_POST['user_id'] );
$hname = mysql_real_escape_string( trim($_POST['hname']) );
$arrName = explode(' ', $hname);
$num = count($arrName);
$first = '';
$last = '';
for ($i = 0; $i < $num; $i++) {
	if ($i == ($num-1)) {
		$last = $arrName[$i];
	} else {
		$first .= $arrName[$i] . ' ';
	}
}
$first = trim($first);
$last = trim($last);

$sql = "update users set he_name = \"" . $hname . "\", first_he = \"" . $first . "\", last_he = \"" . $last . "\" where user_id = " . $user_id;
echo mysql_query($sql);
?>