<?php 

function get_staff_types($params) {

	$staff_types = array();

	$sql = "SELECT * FROM staff_types";
	//$sql = "SELECT * FROM admins where auth != 'super'";
	$query = mq($sql);
	
	while ($row = mysql_fetch_assoc($query)) {
	
        $staff_types_id = $row['staff_types_id'];
        $type_name = $row['type_name'];
        
        $array_element = compact('staff_types_id', 'type_name');

        array_push($staff_types, $array_element);
	}

	return json_encode($staff_types);
}

?>
