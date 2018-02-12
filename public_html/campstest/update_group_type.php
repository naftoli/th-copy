<?php
$admin_auth = array('camp');
require('../header.php'); 

$response = "Update failed!!!";

$group_type_name = $_GET['group_type_name'];
$group_type_id = $_GET['group_type_id'];
$camp_id = $_GET['camp_id'];

$sql = "UPDATE group_types SET group_type_name=".
        ms($group_type_name).
        " WHERE group_type_id=".
        ms($group_type_id).
        " AND camp_id=".$camp_id;
    
$result = mq($sql);
if ($result == TRUE)
{
    $response = $group_type_name;
}   

echo $response;

?>
