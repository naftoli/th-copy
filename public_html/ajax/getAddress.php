<?
require '../db.php';
$school_id = (int)$_POST['id'];
$sql = "select * from schools where school_id = " . $school_id;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
echo json_encode($row);
?>