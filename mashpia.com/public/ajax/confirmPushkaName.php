<?
require '../db.php';

$user_id = mysql_real_escape_string( $_POST['user'] );
$val = mysql_real_escape_string( $_POST['value'] );

$sql = "update users set he_name_conf = " . $val . " where user_id = " . $user_id;
mysql_query( $sql );
?>