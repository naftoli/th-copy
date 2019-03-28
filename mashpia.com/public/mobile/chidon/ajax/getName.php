<?
require '../../../db.php';
$sql = "select name, last_name from chidon_reg where chidon_reg_id = " . mysql_real_escape_string($_POST['id']);
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
echo $row['name'] . ' ' . $row['last_name'];
?>