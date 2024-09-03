<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
$sql = "SELECT * FROM non_th_schools ORDER BY school_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $str = $row['school_name'];
    if (!empty($str)) {
        if ($row['city']) $str .= ',' . $row['city'];
        if ($row['state']) $str .= ',' . $row['state'];
        if ($row['zip']) $str .= ',' . $row['zip'];
        if ($row['country']) $str .= ',' . $row['country'];
        $schools[$row['non_th_school_id']] = $str;
    }
}
echo json_encode($schools);
