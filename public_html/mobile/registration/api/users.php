<?php
include_once( dirname(__FILE__) . "/header.php" );
include_once( dirname(__FILE__) . "/../../../newClasses/newSoldier.php" );
include_once( dirname(__FILE__) . "/../../../classes/admin.php" );
include_once( dirname(__FILE__) . "/../../../classes/user.php" );

// POST / creates new user
// POST /?user_id=<id> updates user
if ( $_SERVER['REQUEST_METHOD'] == "POST" ) {
    $user_id = isset( $_GET['user_id'] ) ? mysql_real_escape_string( $_GET['user_id'] ) : false;

    if ( !$user_id )
        create_user( $admin_id );
    else
        update_user( $user_id );
// GET / returns all users the admin has access to.
} else if ( $_SERVER['REQUEST_METHOD'] == "GET" ) {
    index( $admin_id ); // run the index funciton
}

// GET /
function index( $admin_id ){
    $users_query = mysql_query(
         " SELECT users.* FROM users "
        ." JOIN admin_auths ON auth='user' AND id=user_id "
        ." WHERE admin_id='$admin_id' "
    );
    $users = fetch_results_assoc( $users_query );

    foreach( $users as $index => $user_row ){
        $user = new user( $user_row );
        // limit what we send the client
        $users[ $index ] = [
            'user_id'   => $user->user_id,  'user_code' => $user->user_code,
            'first'     => $user->first,    'last'      => $user->last,
            'first_he'  => $user->first_he, 'last_he'   => $user->last_he,
            'lang_id'   => $user->lang_id,  'gender'    => $user->gender,
            'school_id' => $user->school_id,'class_id'  => $user->class_id,
            'dob'       => $user->dob,      'mobile_pic'=> $user->mobile_pic,
            'profile_picture'   => $user->get_profile_picture(),
            'user_registered'   => $user->user_registered,
            'user_serial'       => $user->user_serial ,
            'registration_fee'  => $user->get_registration_fee()
        ];
    }

    render_json_response( $users );
}

// POST /
function create_user( $admin_id ){
    // clean all post params
    foreach( $_POST as $key => $value )
        $_POST[$key] = post_param( $key );
    // fetch the parent that is currently logged in
    $parent_query = mysql_query(
        "SELECT * FROM admins WHERE admin_id = $admin_id"
    );
    $parent = new \classes\admin( mysql_fetch_assoc( $parent_query ) );
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

    if( !$user->create() ){
        render_json_error(
            "Could not create user due to unknown server error. "
            ."Please email bugs@tzivoshashem.com with the details of the child you wished to create."
        );
    } else {
        render_json_response([
            "user_id" => $user->getUserID()
        ]);
    }
}

// POST /?user_id=
function update_user( $user_id ){
    // filter POST paramaters
    $keys = [ 'mobile_pic', 'gender', 'first', 'last', 'first_he', 'last_he', 'dob', 'lang_id', 'class_id' ];
    foreach( $_POST as $key => $value ) {
        if ( isset( $keys[$key] ) ) $_POST[$key] = post_param( $key );
        else unset( $_POST['key'] );
    }
    // update the keys with the posted values
    $updates = [];
    foreach( $keys as $key ){
        if ( isset($_POST[$key]) ) $updates[] = "$key = '" . $_POST[$key] . "'";
    }
    // make sure that there is something to update
    if ( count( $updates ) == 0)
        render_json_error( "Nothing to update" );
    // run the update
    $update_query = mysql_query(
        "UPDATE users SET " . implode( ", ", $updates ) . " WHERE user_id = '$user_id'"
    );
    // respond if it suceeds
    if ( !$update_query ) render_json_error( "Server Error" );
    render_json_response( false );
}