<?php
include_once( dirname(__FILE__) . "/header.php" );
include_once( dirname(__FILE__) . "/../../../newClasses/newSoldier.php" );
include_once( dirname(__FILE__) . "/../../../classes/admin.php" );

// POST / creates new user
if ( $_SERVER['REQUEST_METHOD'] == "POST" ) {
    // and create the user
    create_user( $admin_id );
}

function create_user( $admin_id ){
    // clean all post params
    foreach( $_POST as $key => $value )
        $_POST[$key] = post_param( $key );
    // fetch the parent that is currently logged in
    $parent_query = mysql_query(
        "SELECT * FROM admins WHERE admin_id = $admin_id"
    );
    $parent = new admin( mysql_fetch_assoc( $parent_query ) );
    // get the post data ( cleaned in the header )
    $school_id = $_POST['school_id']; $class_id = $_POST['class_id'];
    $first = $_POST['first'];       $last = $_POST['last'];
    $first_he = $_POST['first_he']; $last_he = $_POST['last_he'];
    $dob = $_POST['dob'];   $lang_id = $_POST['lang_id'];
    $gender = $_POST['gender']; $mobile_pic = $_POST['mobile_pic'];

    $user = new NewSoldier(
        $parent, $first, $last, $dob, $gender, 
        $school_id, $class_id, $first_he, $last_he, 
        $mobile_pic, true
    );
    $user->setLang( $lang_id );

    // if( !$user->create() ){
    //     render_json_error(
    //         "Could not create user due to unknown server error. "
    //         ."Please email bugs@tzivoshashem.com with the details of the child you wished to create."
    //     );
    // } else {
    //     render_json_success([
    //         "user_id" => $user->getUserID()
    //     ]);
    // }
}