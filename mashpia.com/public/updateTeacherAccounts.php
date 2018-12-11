<?php
require_once 'db.php';
$info = array();

$sql = "select * from admin_auths where auth='class' and role_id = 13 and admin_id >= 164766";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}

echo "<pre>";
//print_r($info);
echo "</pre>";
?>