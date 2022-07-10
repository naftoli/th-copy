<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
$sql = "select * from non_th_schools order by school_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $schools[$row['non_th_school_id']] = $row['school_name'];
}
asort($schools);
echo json_encode($schools);