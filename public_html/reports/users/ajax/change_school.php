<?php
include(dirname(__FILE__)."/../../inc/header.php");
// only superusers may use this page
if ($admin_user['auth'] != 'super') {
    render_json_error("Invalid Permissions");
}
// get the action from the request
$action = $_GET['action'];

// parse the response and act accordingly
if($action == "report") {
    generate_report();
} elseif($action == "move") {
    move_child();
} else {
    render_json_error("Invalid Request");
};

// get the info for the report on the page
function generate_report(){
    $response = ["users" => [], "schools" => [], "success" => true];
    // parse params
    $last_name      = mysql_real_escape_string($_POST['last_name']);
    $registered     = isset($_POST['registered']) && $_POST['registered'] == "true";
    $not_registered = isset($_POST['not_registered']) && $_POST['not_registered'] == "true";
    
    if(!$last_name || (!$registered && !$not_registered)){
        render_json_error("Invalid Options");
    }
    
    // load users
    $users_sql = "SELECT user_id, user_serial, first, last, school_name, class_grade, class_sub, user_registered "
        ."FROM users LEFT JOIN schools USING (school_id) LEFT JOIN classes USING (class_id) WHERE last LIKE '$last_name%' ";
    // limit the users based on the options
    if(!$registered)        $users_sql  .= "AND user_registered IS NULL ";
    if(!$not_registered)    $users_sql  .= "AND user_registered IS NOT NULL ";
    
    $users_sql .= "ORDER BY last, first";
    
    $users_query = mysql_query($users_sql);
    while($user = mysql_fetch_assoc($users_query)){
        $response['users'][] = $user;
    }
    
    // load schools
    $classes_query = mysql_query("SELECT school_id, school_name, class_id, class_grade, class_sub "
        ."FROM schools JOIN classes USING (school_id) "
        ."WHERE class_era = 0 AND test_school=0 ORDER BY school_name, class_grade, class_sub;");
    
    $current_school = ["school_id" => 0]; // initialize object with refrence for check on line 52
    while($class = mysql_fetch_assoc($classes_query)){
        if($class['school_id'] !== $current_school['school_id']){
            if($current_school['school_id'] !== 0) $response['schools'][] = $current_school; // add the school to the response once we are done with it.
            $current_school = [
                "school_id"     => $class['school_id'],
                "name"   => $class['school_name'],
                "classes"       => []
            ];
        }
        
        $current_school["classes"][] = [
            "class_id" => $class['class_id'],
            "name" => $class['class_grade'] . ($class['class_sub'] ? " - " . $class['class_sub'] : "")
        ];
    }
    
    if($current_school['school_id'] !== 0) $response['schools'][] = $current_school; // add the last school to the array
    
    echo json_encode($response);
}

// move the child to a different school
function move_child(){
    $user_id    = mysql_real_escape_string($_POST['user_id']);
    $school_id  = mysql_real_escape_string($_POST['school_id']);
    $class_id   = mysql_real_escape_string($_POST['class_id']);
    
    if(!$user_id || !$school_id || !$class_id){
        render_json_error("Invalid Options");
    }
    
    $move_query = mysql_query("UPDATE users SET school_id = '$school_id', class_id = '$class_id' WHERE user_id = '$user_id'");
    
    echo json_encode(["success" => !!$move_query]);
}
