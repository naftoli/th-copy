<?php
require '../../../db.php';

$id = mysql_real_escape_string($_POST['id']);
$val = mysql_real_escape_string($_POST['val']);

$sql = "update th_chidon_chaps set chidon_type = '" . $val . "' where th_chidon_chap_id = " . $id;
if (!mysql_query($sql)) {
	echo 1;
} else {
	echo 0;
}
?>