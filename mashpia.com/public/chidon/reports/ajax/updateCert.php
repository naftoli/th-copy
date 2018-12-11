<?
require '../../../db.php';

$regID = mysql_real_escape_string($_POST['id']);
$field = mysql_real_escape_string($_POST['field']);
$val = mysql_real_escape_string($_POST['val']);
if ($val == 'true') {
	$checked = 1;
} else {
	$checked = 0;
}

$sql = "update th_chidon set " . $field . " = " . $checked . " where th_chidon_id = " . $regID;
if (mysql_query($sql)) {
	echo 0;
} else {
	echo 1;
}
?>