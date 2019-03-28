<?
require '../../../db.php';

$schoolID = mysql_real_escape_string($_POST['id']);
$checked = mysql_real_escape_string($_POST['checked']);

$sql = "update th_chidon_chaps set ticket = " . $checked . " where th_chidon_chap_id = " . $schoolID;
if (@mysql_query($sql)) {
	echo 1;
} else {
	echo 0;
}
?>