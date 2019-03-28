<?php
require '../../../db.php';
// log the user into the helpdesk system as well
require_once($_SERVER["DOCUMENT_ROOT"].'/helpdesk/control/connect.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/helpdesk/control/functions.php');
// post params
$email      = mysql_real_escape_string($_POST['email']);
$admin_id   = mysql_real_escape_string( $_POST['admin_id'] 	);
// decrypt the admin id
require 'encrypt.php';
$admin_id = encrypt_decrypt('decrypt', $admin_id);
// validate email address
if(!mswIsValidEmail($email)){
	echo json_encode([
        "valid" => false,
        "message" => "Invalid E-mail Address."
    ]);
    die();
}

$duplicate_check = mysql_query("SELECT admin_id FROM admins WHERE admin_email='$email' AND admin_id != '$admin_id'");
if(mysql_num_rows($duplicate_check) > 0){
    echo json_encode([
        "valid" => false,
        "message" => "This E-mail Address Is Already Taken."
    ]);
    die();
}

echo json_encode([
    "valid" => true
]);