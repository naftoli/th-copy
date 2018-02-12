<?php 

function get_all_group_types_divisions_groups($params) {
	$camp_id = $params[0];
	
    $group_types = array();
    $divisions = array();
	$groups = array();
	
	$sql = "SELECT gt.group_type_id, gt.group_type_name, d.division_id, d.division_name, g.group_id, g.group_name ";
	$sql = $sql . "FROM group_types AS gt ";
	$sql = $sql . "LEFT JOIN divisions AS d USING (group_type_id) ";
	$sql = $sql . "LEFT JOIN groups AS g USING (division_id) ";
	$sql = $sql . "WHERE camp_id=" . $camp_id . " ";
	$sql = $sql . "ORDER BY gt.group_type_id, d.division_id, g.group_id";
	
    $query = mq($sql);
	$num_rows = mysql_num_rows($query);
	$row_num = 0;
    while ($row = mysql_fetch_assoc($query) ) {
		$row_num++;
		
        $prev_group_type_id = $row['group_type_id'];
        $prev_division_id = $row['division_id'];
		$group_id = $row['group_id'];
		$group_name = $row['group_name'];
		
		//echo "GROUP TYPE:" . $group_type_name . " DIVISION:" . $division_name . " GROUP:" . $group_name . "<br />";
		
		if ($prev_division_id != $division_id && $division_id != "") {
			$division = compact('division_id', 'division_name', 'groups');
			array_push($divisions, $division);		
			$groups = array();
		}
		
		if ($prev_group_type_id != $group_type_id && $group_type_id != "") {
			$group_type = compact('group_type_id', 'group_type_name', 'divisions');
			array_push($group_types, $group_type);		
			$divisions = array();
		}		
				
		$group = compact('group_id', 'group_name');
		array_push($groups, $group);

		//if ($row_num == $num_rows) {
		//	$division = compact('division_id', 'division_name', 'groups');
		//	array_push($divisions, $division);		
			
		//	$group_type = compact('group_type_id', 'group_type_name', 'divisions');
		//	array_push($group_types, $group_type);			
		//}
		
        $group_type_id = $prev_group_type_id;
        $group_type_name = $row['group_type_name'];

		$division_id = $prev_division_id;
        $division_name = $row['division_name'];		

    }
    
    // Return the array of all group types
    return json_encode($group_types);
}


function delete_group_type($params){
    global $camp_id;

    $error_code = 0;

    $group_type_id = $params[0];

    $delete_group_type_query = "DELETE FROM group_types WHERE group_type_id=" . $group_type_id;
    $delete_group_type_query_result = mq($delete_group_type_query);
    
    if ($delete_group_type_query_result == FALSE) {
        $error_code = 1;
    }
    
    $results = compact('error_code');
    
    // Return the array of all group types
    return json_encode($results);
}

function edit_group_type($params) {
    $group_type_id = $params[0];
    $new_group_type_name = $params[1];
    $error_code = 0;

    $query = "UPDATE group_types SET group_type_name=" . ms($new_group_type_name) . " WHERE group_type_id=" . ms($group_type_id);    
    $result = mq($query);
    if (!$result) {
        $error_code = 1;
        $new_group_type_name = 0;
    }   
    
    $results = compact('error_code');
    
    return json_encode($results);
}

function get_all_groups($params) {
    global $camp_id;
    
    // Get the group types
    $group_types = array();
    
    $group_type_query = mq("SELECT * FROM group_types WHERE camp_id=" . $camp_id);
    while ( $group_type_row = mysql_fetch_assoc($group_type_query) ) {
    
        // Get the group type data
        $group_type_id = $group_type_row['group_type_id'];
        $group_type_name = $group_type_row['group_type_name'];
        
        // Get all the divisions assocaited to the group
        $divisions = array();
        $division_query = mq("SELECT * FROM divisions WHERE group_type_id=".$group_type_row['group_type_id']);
        while ( $division_row = mysql_fetch_assoc($division_query) ) {
            $division_id = $division_row['division_id'];
            $division_name = $division_row['division_name'];
        
            $groups = array();
            $group_query = mq("SELECT * FROM groups WHERE division_id=" . $division_row['division_id']);
            while ( $group_row = mysql_fetch_assoc($group_query) ) {
                $group_id = $group_row['group_id'];
                $group_name = $group_row['group_name'];   
            
                $group_array_element = compact('group_id', 'group_name');   
        
                // Assemble groups
                array_push($groups, $group_array_element);
            }
            
            $divisions_array_element = compact('division_id', 'division_name', 'groups');   
        
            // Assemble divisions
            array_push($divisions, $divisions_array_element);
        }
        
        // Assemble the group type element
        $group_type_array_element = compact('group_type_id', 'group_type_name', 'divisions');

        // Add the group type with asscociated divisions
        array_push($group_types, $group_type_array_element);
    }
    
    // Return the array of all group types
    return json_encode($group_types);
}

function get_division_groups($params) {
    global $camp_id;
    
	$division_id = $params[0];
	
    $groups = array();
    
	$sql = "SELECT * FROM groups WHERE division_id=" . $division_id;
    $query = mq($sql);
    while ($row = mysql_fetch_assoc($query)) {
		$group_id = $row['group_id'];
		$group_name = $row['group_name'];              
		$group_element = compact('group_id', 'group_name');   
		array_push($groups, $group_element);
    }
    
    // Return the array of all group types
    return json_encode($groups);
}


function delete_group($params) {
    $group_id = $params[0];
    $error_code = 0;

    $sql = "DELETE FROM groups WHERE group_id=" . $group_id;
    $query = mq($sql);
    
    if (!$query) 
        $error_code = 1;
    
    $results = compact('error_code');
    
    return json_encode($results);
}

function edit_group($params) { 
    $group_id = $params[0];
	$new_group_name = $params[1];
    $error_code = 0;

    $query = "UPDATE groups SET group_name=" . ms($new_group_name) . " WHERE group_id=" . $group_id;
    $result = mq($query);
    if (!$result) {
        $error_code = 1;
        $new_group_name = 0;
    }   
    
    $results = compact('error_code');
    
    return json_encode($results);
}

function generate_groups($params) {

    /*
    $format = param[0];
	$division_id = param[1];
	$number_of_groups = param[2];
	$division_name = param[3];
	$new_division_names = "";
	$letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	
	for ($cntr = 0; $cntr < $number_of_groups; $cntr++) {
		if ($format == "A") { 
			$character = substr($letters, $cntr, 1);
		}
        else {
			$character = ($cntr + 1) . "";
		}
        	
		$generate_groups_query = "SELECT * FROM groups WHERE division_id=" . $division_id . " AND group_name=" . ms($division_name . " " . $character);
		$generate_groups_query_result = mysql_query($generate_groups_query);
		$num_rows = mysql_num_rows($generate_groups_query_result);

		if ($num_rows == 0) {
			$generate_groups_query = "INSERT INTO groups SET division_id=" . $division_id . ", group_name=" . ms($division_name . " " . $character);
			
			if (!mysql_query($sql)) {} 
                die();
			}
			else { 
                $new_group_id = mysql_insert_id();
			}
			
			$new_division_names = $new_division_names . $new_group_id . "~" . $division_name . " " . $character . "|";
		}
	}
	
	$new_division_names = substr($new_division_names, 0, strlen($new_division_names) - 1);
	
    // Return the array of all group types
    return json_encode($results);
    //echo $new_division_names;
    */
}

?>