<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
$sql = "select * from non_th_schools order by school_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $schools[$row['non_th_school_id']] = $row['school_name'] . ',' . $row['city'] . ',' . $row['state'] . ',' . $row['zip'] . ',' . $row['country'];
}
echo json_encode($schools);
