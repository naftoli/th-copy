<?php
$admin_auth = array('camp');
require('../header.php'); 

$camp_id = gri('camp_id');

function getDefaultGroupType(){

    $query = mq("SELECT * FROM default_group_types");
	
    while ($row = mysql_fetch_assoc($query))
    {
        
    }
}

function setGroupTypes($groupTypes){

    

}



?>