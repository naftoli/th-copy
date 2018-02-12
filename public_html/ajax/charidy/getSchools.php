<?php
header("Access-Control-Allow-Origin: *");
require '../../db.php';

$key = mysql_real_escape_string($_POST['key']);
if ($key == 'th5776') {
    
    $schools = array();
    $sql = "select school_id, school_name from schools where chayolei = 1 and school_era is null order by school_name";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $schools[] = $row;
    }
    
    echo json_encode($schools);
}
?>