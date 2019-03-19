<?php
require_once __DIR__ . "/../../../db.php";
// Encryption scheme
require_once __DIR__ . "/../../../mobile/reg/ajax/encrypt.php";

// Username And password....
require_once __DIR__ . "/functions/header.php";
$username = isset($_POST['username']) ? clean_post_param( 'username' ) : false;
$password = isset($_POST['password']) ? clean_post_param( 'password' )  : false;

if(!$username || !$password) {
    render_json_error("Invalid Login 1");
}

require_once __DIR__ . '/../classes/staffManager.php';
$s = new StaffManager();
$success = $s->checkLogin( $username, $password );

if ( $success ) {
    $admin_id = $s->getID();
    echo json_encode([
        "success" => true,
        "login" => encrypt_decrypt('encrypt', $admin_id)
    ]);
} else {
    render_json_error("Invalid Login");
}