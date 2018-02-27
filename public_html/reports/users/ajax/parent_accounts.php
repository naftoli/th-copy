<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

include(dirname(__FILE__)."/../../inc/header.php");
// get the action from the request
$action = $_GET['action'];

// parse the response and act accordingly
if($action == "report") {
    generate_report();
} elseif($action == "update") {
    //move_child();
} else {
    render_json_error("Invalid Request");
};

function generate_report(){
    global $admin_user;
    $response = ["parents" => [], "success" => true];
    // parse params
    $username   = mysql_real_escape_string($_POST['username']);
    $email      = mysql_real_escape_string($_POST['email']);
    $last_name  = mysql_real_escape_string($_POST['last_name']);
    $admin_id   = mysql_real_escape_string($_POST['parent_id']);
    
    if(!$username && !$admin_id && !$email && !$last_name){
        render_json_error("Invalid Options");
    }
    // admin search limits
    $limit = "";
    if($username)       $limit = "username='$username' ";
    elseif($email)      $limit = "admin_email LIKE '$email%' ";
    elseif($last_name)  $limit = "last LIKE '$last_name%' ";
    elseif($admin_id)   $limit = "admin_id='$admin_id' ";
    
    // check if the admin has a limited number of schools
    $school_ids = false;
    if ($admin_user['auth'] != 'super') {
        require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $school_ids = array_keys($as->getSchools());
    }
    
    // load admin from the DBS
    $parent_query = mysql_query(
        "SELECT a.admin_id, a.username, a.password, a.father, a.mother, a.admin_address1, a.admin_city, "
        ."a.admin_state, a.admin_postal, a.admin_country, a.admin_email, a.admin_phone_mobile, "
        ."a.last FROM admins a WHERE $limit;" // search by username or admin_id
    );
    
    if(mysql_num_rows($parent_query) == 0){
        echo_json_error("Could not find Parent Account");
    }
    
    $parents = [];
    while($parent = mysql_fetch_assoc($parent_query)){ // loadup each of the parents
        $children_query = mysql_query(
            "SELECT u.first, u.last, u.user_serial, s.school_id, s.school_name "
            ."FROM admin_auths aa JOIN users u ON aa.id = u.user_id AND aa.auth = 'user' "
            ."JOIN schools s USING (school_id) WHERE aa.admin_id = ".$parent['admin_id'].";"
        );
        
        $parent['children']         = [];
        $parent['other_children']   = 0;
        while($child = mysql_fetch_assoc($children_query)) {
            if($school_ids && in_array($child['school_id'], $school_ids)) {
                $parent['children'][] = $child;
            } elseif($school_ids) {
                 $parent['other_children'] += 1;
            } else { // superusers go here
                $parent['children'][] = $child;
            }
        }
        $parents[] = $parent;
    }
    
    foreach($parents as $parent){
        if(count($parent['children']) > 0) { // make sure the parent has kids in the current school...
            $response['parents'][] = $parent;
        }
    }
    
    if(count($response['parents']) == 0) {
        render_json_error("Could not find Parent Account");
    }
    
    echo json_encode($response);
}