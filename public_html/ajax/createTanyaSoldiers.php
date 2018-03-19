<?php // import the header
include(dirname(__FILE__)."/../reports/inc/header.php");

// get the post paramaters
$class_id   = clean_post_param( "class_id"   );
$school_id  = clean_post_param( "school_id"  );
$user_count = clean_post_param( "user_count" );

// validate the post paramaters
if ( !$class_id || !$school_id || !$user_count )
    render_json_error( "Invalid Request", "Missing Paramaters" );

$school_validation_query = mysql_query(
    "SELECT school_id FROM classes WHERE class_id = '$class_id'"
);
// make sure that the class exists and that it's school ID matches the one provided
if ( 
    !mysql_num_rows($school_validation_query) 
    || mysql_fetch_assoc($school_validation_query)['school_id'] != $school_id 
) render_json_error( "Invalid request", "Data invalid" );

// start the user creation transaction...
mysql_query("START TRANSACTION;");

// create the number of users that they want
$username_count = 1;
for($index = 0; $index < $user_count; $index++) {
    $username = "tanya_" . $school_id . "_" . $class_id . "_";
    // prevent duplicate usernames
    while (mysql_num_rows(mq('SELECT username FROM users WHERE username = ' . ms($username . $username_count))))
        $username_count++;
    // add the count to the username to enforce it's uniqueness
    $username .= $username_count;

    // generate user barcode
    $count = 0; // reset the count...
    do {
        if ($count++ > 100000) trigger_error('could not get ID', E_USER_ERROR);
        $user_code = mysql_result(mq('SELECT FLOOR(RAND() * 9223372036854775807)'),0);
    } while (mysql_result(mq("SELECT COUNT(*) FROM users WHERE user_code = $user_code"),0) != 0);

    $user_serial = mysql_result(mysql_query("(SELECT IFNULL(MAX(user_serial), 0)+1 FROM users users_max)"), 0);

    $status = mysql_query(
        " INSERT INTO users (user_code, username, email, password, first, last, first_he, last_he, school_id, class_id, school_type_id, user_serial, "
        ." user_address1, user_address2, user_city, user_state, user_postal, user_country, user_phone, kiosk_edit, yan) "
        ." VALUES ('$user_code', '$username', ' ', ' ', 'Tanya', 'User $username_count', '', '', '$school_id', '$class_id', '5', '$user_serial', "
        ." '', '', '', '', '', '', '', '', 1)"
    ); // matches line 45

    if(!$status) {
        mysql_query("ROLLBACK;"); // rollback any changes
        render_json_error( "Server Error: Could not create users." );
    }
}

mysql_query("COMMIT;"); // save the users

echo json_encode( [ "success" => true ] );