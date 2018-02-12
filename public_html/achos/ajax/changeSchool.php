<?
$school = $_GET['sid'];
$child = $_GET['cid'];

require_once '../db.php';
if ($school == 0)
    $sql = "update users set school_id = null where user_id = " . $child;
else
    $sql = "update users set school_id = " . $school . " where user_id = " . $child;
mysql_query($sql);
echo $sql;
?>