<?php
$admin_auth = array('camp');
require('../header.php'); 

$response = "Update failed!!!";

$group_type_id = $_GET['groupTypeId'];
$division_name = $_GET['divisionName'];

$sql = "SELECT * FROM divisions WHERE group_type_id=" . ms($group_type_id) . " AND division_name=" . ms($division_name);
$result = mq($sql);
$num_rows = mysql_num_rows($result);

if ($num_rows == 0) {

    $sql = "INSERT INTO divisions SET group_type_id=" . ms($group_type_id) .", division_name=" . ms($division_name);
            
    $result = mq($sql);
    if ($result == TRUE)
    {
        $response = $division_name;
    }
    
    echo "INSERT";
}

echo $response;

?>