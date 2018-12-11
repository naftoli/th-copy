<?
require '../db.php';

$school_id = mysql_real_escape_string( $_POST['school'] );
$val = mysql_real_escape_string( $_POST['value'] );

$sql = "update schools set conf_pushka_users = " . $val . " where school_id = " . $school_id;
mysql_query( $sql );
?>