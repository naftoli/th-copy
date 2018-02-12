<?
require 'db.php';

$user = mysql_real_escape_string($_GET['id']);

$sql = "delete from mishna_learned where user_id = " . $user;
$sql2 = "delete from mishna_at_once where user_id = " . $user;
$sql3 = "delete from bp_points where user_id = " . $user;

mysql_query($sql) or die(mysql_error());
mysql_query($sql2) or die(mysql_error());
mysql_query($sql3) or die(mysql_error());
?>