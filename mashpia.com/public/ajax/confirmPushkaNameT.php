<?
require '../db.php';

$class_id = mysql_real_escape_string( $_POST['grade'] );
$val = mysql_real_escape_string( $_POST['value'] );

$sql = "update classes set conf_hname = " . $val . " where class_id = " . $class_id;
mysql_query( $sql );
?>