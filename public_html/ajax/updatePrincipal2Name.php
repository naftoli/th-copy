<?
require '../db.php';

$school_id = mysql_real_escape_string( $_POST['school_id'] );
$hname = mysql_real_escape_string( trim($_POST['he_name']) );

$sql = "update schools set he_name_p2 = \"" . $hname . "\" where school_id = " . $school_id;
echo mysql_query($sql);
?>