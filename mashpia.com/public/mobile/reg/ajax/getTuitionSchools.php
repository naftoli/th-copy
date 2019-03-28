<?php
$tuitionSchools = array();
require_once '../../../db.php';
$sql = "select school_id from schools where tuition = 1";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $tuitionSchools[] = $row['school_id'];
}
echo json_encode($tuitionSchools);