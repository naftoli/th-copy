<?php
// connect to the database
require_once( $_SERVER['DOCUMENT_ROOT'] . "/db.php" );

// default response
$response = [
    'success' => false
];

// parse the card number
$number = mysql_real_escape_string( $_POST['number'] );

// load the user from the database
// make sure the number starts with a 3 or a 7
if ( substr( $number, 0, 1 ) != '3' && substr( $number, 0, 1 ) != '7' ) {
    $response['body'] = "Invalid Number";
    echo json_encode($response);
    exit;
}
if ( strlen( $number ) == 7 ) {
    $sql = "SELECT * FROM users WHERE user_serial = " . $number;
} else {
    $number = substr( $number, 1 );
    $sql = "SELECT * FROM users WHERE user_code = " . $number;
}
$user_query = mysql_query( $sql );

if ( mysql_num_rows( $user_query ) ) { // user exists
    $user = mysql_fetch_assoc( $user_query );
    if ( $user['user_registered'] > 0 ) {
        $user_id = $user['user_id'];
        $response['success'] = true;
        $response['user_id'] = $user_id;
        
        // encrypt user id
        require_once( dirname(__FILE__).'/../../reg/ajax/encrypt.php' );
        $encrypted = encrypt_decrypt('encrypt', $user_id);

        // set some cookies in the browser (does this work via AJAX?)
        if ( !isset( $_COOKIE['user'] ) )
            setcookie( 'user', $encrypted, 0, '/' );
        if ( !isset( $_COOKIE['kiosk'] ) )
            setcookie( 'kiosk', 1, 0, '/' );
    } else {
        $response['body'] = "You are not registered for the current year.";
    }
} else { // no user found
    $response['body'] = "Invalid " . $type . " Number";
}
// render the response as JSON
echo json_encode($response);