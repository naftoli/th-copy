<?php
// connect to the database
require_once( $_SERVER['DOCUMENT_ROOT'] . "/db.php" );

// default response
$response = [
    'success' => false
];

// parse the card number
$card_number = mysql_real_escape_string( $_POST['card'] );
// remove first number (3, not stored in DBS)
$card_number = substr($card_number, 1);

// load the user from the database
$user_query = mysql_query(
    "SELECT * FROM users WHERE user_code = " . $card_number
);

if ( mysql_num_rows( $user_query ) ) { // ID card exists
    $user = mysql_fetch_assoc( $user_query );
    // make sure that the user is registered for the current year.
    if ( $user['user_registered'] > 0 ) {
        $user_id = $user['user_id'];
        $response['success'] = true;
        $response['user_id'] = $user_id;
        
        // encrypt user id
        require_once( dirname(__FILE__).'/../reg/ajax/encrypt.php' );
        $encrypted = encrypt_decrypt('encrypt', $user_id);
        // set some cookies in the browser (does this work via AJAX?)
        if ( !isset( $_COOKIE['user'] ) )
            setcookie( 'user', $encrypted, 0, '/' );
        if ( !isset( $_COOKIE['kiosk'] ) )
            setcookie( 'kiosk', 1, 0, '/' );
    } else {
        $response['body'] = "You are not registered for the current year.";
    }
} else { // no user
    $response['body'] = "Invalid ID Card";
}
// render the response as JSON
echo json_encode($response);