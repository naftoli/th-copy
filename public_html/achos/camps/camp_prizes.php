<?php

function get_global_prizes($params) {
	$global_prizes = array();
	
	$sql = "SELECT * FROM prizes_stores";
	$query = mq($sql);
	while ($row = mysql_fetch_assoc($query)) {
        $global_prize_id = $row['prize_id'];
        $global_school_id = $row['school_id'];
        $global_prize_name = $row['prize_name'];
        $global_prize_description = $row['prize_description'];
        $global_prize_points = $row['prize_points'];
        $global_prize_available = $row['prize_available'];
        $global_prize_image_id = $row['prize_image_id'];
        $global_prize_current = $row['prize_current'];
		
		$global_prize_element = compact('global_prize_id', 
                                        'global_school_id', 
                                        'global_prize_name',
                                        'global_prize_description',
                                        'global_prize_points',
                                        'global_prize_available',
                                        'global_prize_image_id',
                                        'global_prize_current');
		
        array_push($global_prizes, $global_prize_element);
	}
	
	return json_encode($global_prizes);
}

function get_camp_prizes($params) {
    global $camp_id;
	
	$camp_prizes = array();
	
	$sql = "SELECT global_prize_id FROM prizes_camps WHERE camp_id=".$camp_id;
	$query = mq($sql);
	while ($row = mysql_fetch_assoc($query)) {
        $camp_prize_id = $row['global_prize_id'];
    
        array_push($camp_prizes, $camp_prize_element);
    }
	
	return json_encode($camp_prizes);
}

function add_camp_prize($params) {
    global $camp_id;
	
	$global_prize_id = $params[0];
	$error_code = 0;

    // Ensure that the prize is not already in the camp prize table
    $sql = "SELECT * FROM prizes_camp WHERE camp_id=" . $camp_id. "global_prize_id=" . $global_prize_id;
	$query = mq($sql);
    $num_rows = mysql_num_rows($query);
    
    // If the camp prize is not already installed, install it from the global
    // prize repository
    if($num_rows == 0) {
        
        // Get the data for the global prize
        $sql = "SELECT * FROM prizes_store WHERE global_prize_id=" . $global_prize_id;

	    if (mysql_query($sql)) { 
            $global_prize_id = $row['prize_id'];
            $global_prize_name = $row['prize_name'];
            $global_prize_description = $row['prize_description'];
            $global_prize_points = $row['prize_points'];
            $global_prize_available = $row['prize_available'];
            $global_prize_image_id = $row['prize_image_id'];
            $global_prize_current = $row['prize_current'];      
        
            $sql = "INSERT INTO prizes_camp SET camp_id=" . $camp_id . 
                    " , global_prize_id=" . $global_prize_id . 
                    " , prize_name=" . ms($global_prize_name) .
                    " , prize_description=" . ms($global_prize_description) .
                    " , prize_points=" . $global_prize_points .
                    " , prize_available=" . ms($global_prize_available) .
                    " , prize_image_id=" . ms($global_prize_image_id) .
                    " , prize_current=" . ms($global_prize_current); 
                    
            if (!mysql_query($sql)) 
		      
		      $global_prize_id = 0;
              $error_code = 1; 
		    }
        }
        else {
        
            $global_prize_id = 0;
            $error_code = 2;
        }
    }
    
	$results = compact('error_code', 'global_prize_id');
	
	return json_encode($results);
}

function delete_camp_prize($params) {
    global $camp_id;
    
    $prize_id = $params[0];
	$error_code = 0;

	$sql = "DELETE FROM prizes_camp WHERE camp_id=" . $camp_id .
            " prize_id=" . $prize_id;
	
	if (!mysql_query($sql)) 
		$error_code = 1;
		
	$results = compact('error_code');
	
	return json_encode($results);
}

?>