<?php
$admin_auth = array('camp');
require('../header.php'); 

$response = "Update failed!!!";

$division_id = $_GET['divisionId'];
$new_division_name = $_GET['divisionName'];

$sql = "UPDATE divisions SET division_name=".
        ms($new_division_name).
        " WHERE division_id=".
        ms($division_id);
    
$result = mq($sql);
if ($result == TRUE)
{
    $response = $group_type_name;
}   

echo $response;

?>
