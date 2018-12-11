<?php 

function get_all_divisions($params) {
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
        while ( $division_row = mysql_fetch_assoc($division_query) )
        {
            $division_id = $division_row['division_id'];
            $division_name = $division_row['division_name'];
        
            $divisions_array_element = compact('division_id', 'division_name');   
        
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

function get_group_type_divisions($params) {
    global $camp_id;

	$group_type_id = $params[0];
	
    // Get the group types
    $divisions = array();
    
    $query = mq("SELECT * FROM divisions WHERE group_type_id=" . $group_type_id);
    while ($row = mysql_fetch_assoc($query) ) {
    
        $division_id = $row['division_id'];
        $division_name = $row['division_name'];
        $groups = $row['groups'];
		
        $division_element = compact('division_id', 'division_name', 'groups');      
		array_push($divisions, $division_element);
    }
    
    // Return the array of all group types
    return json_encode($divisions);                                    
}


function add_division($params) {
    
    $group_type_id = $params[0];
    $division_name = $params[1];
    $new_division_id = 0;
    $error_code = 0;

    $new_division_query = "SELECT * FROM divisions WHERE group_type_id=" . ms($group_type_id) . " AND division_name=" . ms($division_name);
    $new_division_query_result = mq($new_division_query);
    
    // Ensure that the division name doesn't already exist
    $num_rows = mysql_num_rows($new_division_query_result);
    if ($num_rows == 0) {

        $new_division_query = "INSERT INTO divisions SET group_type_id=" . ms($group_type_id) .", division_name=" . ms($division_name);
        $new_division_query_result = mq($new_division_query);
        
        if ($new_division_query_result == FALSE) {
            $error_code = 2;
        }
        else
        {
            // Do a third query to get back the new division id
            $new_division_query = 
                "SELECT * FROM divisions WHERE division_name=" . ms($division_name);
            
            $new_division_query_result = mq($new_division_query);
            $division_row = mysql_fetch_assoc($new_division_query_result);
            
            $new_division_id = $division_row['division_id'];
        }
    }
    else
    {
        $error_code = 1;
    }
    
    // Generate results
    $results = compact('error_code', 'new_division_id');
    
    return json_encode($results);
}

function delete_division($params) {
    $division_id = $params[0];
    $error_code = 0;

    $sql = "DELETE FROM divisions WHERE division_id=" . $division_id;
    $query = mq($sql);
    
    if (!$query) {
        $error_code = 1;
    }
    
    $results = compact('error_code');
    
    return json_encode($results);
}

function edit_division($params) {
    global $camp_id;
    
    $division_id = $params[0];
    $new_division_name = $params[1];
    $error_code = 0;

    // Update the group type with the new name
    $update_division_query = "UPDATE divisions SET division_name=".
                                ms($new_division_name).
                                " WHERE division_id=".
                                ms($division_id);
    
    $update_division_query_result = mq($update_division_query);
    if ($update_division_query_result == FALSE)
    {
        $error_code = 1;
        $new_division_name = 0;
    }   
    
    $results = compact('error_code', 'new_division_name');
    
    // Return the array of all group types
    return json_encode($results);
}
    
?>