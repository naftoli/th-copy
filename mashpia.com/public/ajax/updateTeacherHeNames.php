<?
require '../db.php';

$class_id = mysql_real_escape_string( $_POST['class_id'] );
$hname = mysql_real_escape_string( trim($_POST['hname']) );

$sql = "update classes set teacher_hname = \"" . $hname . "\" where class_id = " . $class_id;
echo mysql_query($sql);
?>