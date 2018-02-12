<?
require '../db.php';

$id = mysql_real_escape_string($_POST['id']);
$checked = mysql_real_escape_string($_POST['checked']);

$sql = "update chidon set prepared = " . $checked . " where id = " . $id;
mysql_query($sql) or die(mysql_error());
?>