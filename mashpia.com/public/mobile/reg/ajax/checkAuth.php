<?php // connect to the DBS
require_once( dirname(__FILE__) . '/../../../db.php' ) ;

// parse the params
$user_id = isset($_POST['user_id']) ? mysql_real_escape_string( $_POST['user_id'] ) : false;
// get the encrtpted admin_id
$admin_id = isset($_POST['admin_id']) ? mysql_real_escape_string( $_POST['admin_id'] ) : false;
// allow for user tokens
$user_token = isset($_POST['user_token']) ? mysql_real_escape_string( $_POST['user_token'] ) : false;
// decrypt the info
require_once( dirname(__FILE__) . '/encrypt.php');

$result = false;
// authenticate the user
if ( $admin_id && $user_id ) {
    $admin_id = encrypt_decrypt('decrypt', $admin_id);
    $query = mysql_query( 
        "SELECT * FROM admin_auths WHERE admin_id = " . $admin_id . " AND id = " . $user_id . ";"
    );
    $result = mysql_num_rows($query) > 0;
} else if ( $user_token ) {
    $user_id = encrypt_decrypt( 'decrypt', $user_token );
    $query = mysql_query(
        "SELECT * FROM users WHERE user_id = '$user_id' AND user_registered IS NOT NULL"
    );
    $result = mysql_num_rows($query) > 0;
}

// version 1, return a 1 or a 0
if ( !isset($_POST['version']) ) {
    echo $result ? 1 : 0;
// version 2+, use JSON
} else if ( $_POST['version'] == "2") {
    echo json_encode([
        "success" => $result
    ]);
}
?>