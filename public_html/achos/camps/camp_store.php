<?php

	//include 'globalDefs.php';

include ("/home/mashpia/public_html/CampMotivationalSystem/dev/presentation/includes/get_camp_id.php");
$camp_id = get_camp_id();
/*
session_start();
	$camp_id = $_SESSION['camp_id'];
	if(!$camp_id)
	{ $camp_id = 4; }
*/
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
        case "add_new_blank_prize":
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

/*
function linkImgFile($id, $width=NULL, $height=NULL, $extra = '') 
{
    $result = mysql_unbuffered_query("SELECT file_name FROM files WHERE file_id = $id");
    if(!isset($result)|| $result == "" || $result == 0) 
    {
        $row = array();
        $row['file_name'] = "";
    } 
    else
    {
        $row = mysql_fetch_assoc($result);
    } 
    if(!is_null($width)) $width = "WIDTH='$width'";
    if(!is_null($height)) $height = "HEIGHT='$height'";
    return "<IMG SRC='/file_view.php?id=$id' $width $height $extra ALT='" . es($row['file_name']) . "'>";
}
*/

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

// parameters: none 
function add_new_blank_prize() {
    global $camp_id;
    
    $new_blank_prize_id = 0;
    $new_blank_prize_name = "New Prize Name - ". generateRandomString(10);
    $new_blank_prize_points = 0;
    $new_blank_prize_available = 0; 
    $error_code = 0;

    // Add the new prize element
    $add_new_blank_prize_query = "INSERT INTO prizes_camp SET camp_id=" . $camp_id .  
            " , prize_name=". ms($new_blank_prize_name) ." , prize_points='0', prize_description=''".
            " , prize_available='0', prize_image_id='0', prize_current=TRUE, prize_enabled=FALSE";
      
    // If the query was successful, then get the new element,
    // so that we can we return the new id              
    if (mysql_query($add_new_blank_prize_query)) { 
        $new_blank_prize_id = mysql_insert_id();     
    }
    else{
        $error_code = 1; 
	}
    
    $results = compact('error_code', 'new_blank_prize_id', 'new_blank_prize_name', 'new_blank_prize_points', 'new_blank_prize_available');
    
	return json_encode($results);
}

// THIS FUNCTION SHOULD BE MOVED TO A GLOBAL LOCATION WHEN A SUITABLE LOCATION IS FOUND - mtrussler
function generateRandomString($str_len) {
    $alphabet = array ("A", "B", "C", "D", "E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
    $random_str = "";

    for ($char_index = 0; $char_index < $str_len; $char_index++) {
        
        $index = mt_rand(0,count($alphabet) - 1);
        $random_str.=$alphabet[$index];
    }
    
    return $random_str;
}
  
?>
