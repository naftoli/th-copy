<?php
header('Content-Type: application/json');
require '../../db.php';

$key = mysql_real_escape_string($_POST['key']);
if ($key == 'cth5778!') {
    
    $schools = array();
    $sql = "select school_id, school_name from schools where chayolei = 1 and school_era is null order by school_name";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $schools[$row['school_id']] = $row['school_name'];
    }
    
    echo json_encode($schools);
}
?>