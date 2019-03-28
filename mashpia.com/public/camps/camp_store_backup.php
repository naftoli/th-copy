<?php

include 'globalDefs.php';

$camp_id = 4/*$_SESSION['camp_id']*/;
$action = "";

switch ($_SERVER['REQUEST_METHOD']) {
    case "GET": 		
        if (isset($_GET['action'])) { 		
             $action = $_GET['action'];
		}
	    break;
	default:
	   
}

echo callCampStoreFunction($action);

function callCampStoreFunction($funcName)	        
{
    $result = null; 
    
    // Initialize parameters- a bit of a hack but we don't have time
    switch ($funcName) {
    
        case "get_camp_prizes":
            $result = $funcName();
            break;
        case "disable_camp_prize":
        case "enable_camp_prize":
            $result = $funcName($_GET['prize_id']);
            break;
        case "edit_camp_prize_name":
        case "edit_camp_prize_points":
        case "edit_camp_prize_available":
            $result = $funcName($_GET['prize_id'], $_GET['edit_value']);
            break;
        default:
            break;
    }
      
    return $result;   
}

function get_camp_prizes() {
	global $camp_id;
  
    $camp_prizes = array();
	
    $camp_prizes_query = "SELECT * FROM prizes_camp";
	$camp_prizes_query_result = mq($camp_prizes_query);
	
    while ($camp_prizes_row = mysql_fetch_assoc($camp_prizes_query_result)) {
        $prize_id = $camp_prizes_row['prize_id'];
        $school_id = $camp_prizes_row['school_id'];
        $prize_name = $camp_prizes_row['prize_name'];
        $prize_description = $camp_prizes_row['prize_description'];
        $prize_points = $camp_prizes_row['prize_points'];
        $prize_available = $camp_prizes_row['prize_available'];
        
        $prize_image_id = $camp_prizes_row['prize_image_id'];
        
        
        
        $prize_current = $camp_prizes_row['prize_current'];
        $prize_enabled = $camp_prizes_row['prize_enabled'];
	    $prize_element = compact('prize_id', 
                                 'school_id', 
                                 'prize_name',
                                 'prize_description',
                                 'prize_points',
                                 'prize_available',
                                 'prize_image_id',
                                 'prize_current',
                                 'prize_enabled');
		
        array_push($camp_prizes, $prize_element);
	}
	
	return json_encode($camp_prizes);
}

// parameters: prize_id
function enable_camp_prize($prize_id) {
    global $camp_id;

    $error_code = 0;
    
    $enable_prize_query = "UPDATE prizes_camp SET prize_enabled=TRUE".
                            " WHERE camp_id=" . $camp_id .
                            " AND prize_id=" . $prize_id;
    
    if (!mysql_query($enable_prize_query)) {
		$error_code = 1;
	}

    $results = compact('error_code');
    
	return json_encode($results);
}

// parameters: prize_id
function disable_camp_prize($prize_id) {
    global $camp_id;
  
    $error_code = 0;
    
    $disable_prize_query = "UPDATE prizes_camp SET prize_enabled=FALSE".
                            " WHERE camp_id=" . $camp_id .
                            " AND prize_id=" . $prize_id;
    
    if (!mysql_query($disable_prize_query)) {
		$error_code = 1;
	}

    $results = compact('error_code');
    
	return json_encode($results);
}

// parameters: prize_id, prize_name 
function edit_camp_prize_name($prize_id, $prize_name) {
    global $camp_id;
    
    $error_code = 0;
    
    $edit_camp_prize_name_query = "UPDATE prizes_camp SET prize_name=" . ms($prize_name) .
                            " WHERE camp_id=" . $camp_id .
                            " AND prize_id=" . $prize_id;
    
    if (!mysql_query($edit_camp_prize_name_query)) {
		$error_code = 1;
	}
	
    $results = compact('error_code');
    
	return json_encode($results);
}

// parameters: prize_id, prize_points
function edit_camp_prize_points($prize_id, $prize_points) {
    global $camp_id;
    
    $error_code = 0;
    
    $edit_camp_prize_points_query = "UPDATE prizes_camp SET prize_points=" . $prize_points .
                            " WHERE camp_id=" . $camp_id .
                            " AND prize_id=" . $prize_id;
    
    if (!mysql_query($edit_camp_prize_points_query)) {
		$error_code = 1;
	}

    $results = compact('error_code');
    
	return json_encode($results);
}

// parameters: prize_id, prize_available
function edit_camp_prize_available($prize_id, $prize_available) {
    global $camp_id;
    
    $error_code = 0;
    
    $edit_camp_prize_available_query = "UPDATE prizes_camp SET prize_available=" . $prize_available .
                            " WHERE camp_id=" . $camp_id .
                            " AND prize_id=" . $prize_id;
    
    if (!mysql_query($edit_camp_prize_available_query)) {
		$error_code = 1;
	}

    $results = compact('error_code');
    
	return json_encode($results);
}

// parameters: prize_name -> $params[0], 
//  prize_points = $params[1], 
//  prize_description = $params[2],
//  prize_available = $params[3],
//  prize_image = $params[5],
//  prize_current = $params[6]
function add_camp_prize($params) {
  global $camp_id;
	
	/*
	$prize_name = $params[0];
	$prize_points = $params[1];
  $prize_description = $params[2];
  $prize_available = $params[3];
  $prize_image = $params[4];
  $prize_current = $params[5];
  $prize_enabled = $params[6];
  $prize_id = 0;
  $error_code = 0;

  // Ensure that the prize is not already in the camp prize table
  $prizes_query = "SELECT * FROM prizes_camp WHERE camp_id=" . $camp_id. "prize_name=" . $prize_name;
	$prizes_query_results = mq($prizes_query);
  $num_rows = mysql_num_rows($prizes_query_results);
    
  // If the camp prize is not already installed, install it
  if($num_rows == 0) {     
    $prizes_query = "INSERT INTO prizes_camp SET camp_id=" . $camp_id .  
            " , prize_name=" . ms($prize_name) .
            " , prize_points=" . ms($prize_points) .
            " , prize_description=" . ms($prize_description) .
            " , prize_available=" . ms($prize_available) .
            " , prize_image=" . ms($prize_image) .
            " , prize_current=" . ms($prize_current) .
            " , prize_enabled=" . ms($prize_enabled);
      
    // If the query was successful, then get the new element,
    // so that we can we return the new id              
    if (mysql_query($prizes_query)) { 
		  $prizes_query = "SELECT * FROM prizes_camp WHERE camp_id=" . $camp_id. "prize_name=" . $prize_name;
      $prizes_query_results = mq($prizes_query);
      $prizes_row = mysql_fetch_assoc($prizes_query_results);
      $prize_id = $prizes_row['prizes_id'];     
    }
    else{
        // The select failed
        $prize_id = 0;
        $error_code = 2; 
		}
  }
  else {
    // A prize with the given name already exists
    $error_code = 1;  
  }
    
	$results = compact('error_code', 'prize_id');
	*/
	
	return json_encode($results);
}
  
?>