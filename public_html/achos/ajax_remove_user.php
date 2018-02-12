<?
require('db.php');

$user_id = $_GET['user_id'];

$sql = "UPDATE users SET school_id=NULL, class_id=NULL, team_id=NULL WHERE user_id=" .  $user_id;
$query = mysql_query($sql);

if ($query)
	echo "1";
else
	echo "0";
?>