<?php
// DBS connection.....
require_once( $_SERVER['DOCUMENT_ROOT'].'/db.php' );
require_once(dirname(__FILE__)."/functions/header.php");
// Encryption scheme
require_once( $_SERVER['DOCUMENT_ROOT'].'/mobile/reg/ajax/encrypt.php' );
// Username And password....
$username = isset($_POST['username']) ? clean_post_param( 'username' ) : false;
$password = isset($_POST['password']) ? clean_post_param( 'password' )  : false;

if(!$username || !$password) {
    render_json_error("Invalid Login 1");
}

$sql = "SELECT * FROM th_chidon_staff WHERE username = '" . $username . "' AND password = '" . $password . "'";
$result = mysql_query( $sql );

if (mysql_num_rows( $result )) {
    $row = mysql_fetch_assoc( $result );
    $admin_id = $row['staff_id'];
    echo json_encode([
        "success" => true,
        "login" => encrypt_decrypt('encrypt', $admin_id)
    ]);
} else {
    render_json_error("Invalid Login");
}