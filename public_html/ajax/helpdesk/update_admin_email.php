<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();

/***************** AUTHENTICATION **********************/
$admin_auth = array('school', 'class', 'camp', 'user'); // everyone may hit this enpoint
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

function echo_json_error($error_msg){
    echo json_encode([
        "success"   => false,
        "error"     => $error_msg
    ]);
    die(); // end the script)
}

// get the username and password
$email      = mysql_real_escape_string($_POST['email']);
$admin_id   = $admin_user['admin_id'];

// make sure a username and password where submitted
if(!$email || !$admin_id){
    echo_json_error("Invalid Request");
}

// log the user into the helpdesk system as well
require_once($_SERVER["DOCUMENT_ROOT"].'/helpdesk/control/connect.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/helpdesk/control/functions.php');
// load up the functions for adding users to the DBS
require_once($_SERVER["DOCUMENT_ROOT"].'/tasks/forms/functions/helpdesk_account_migration.php');
// make sure the email is valid for HelpDesk
if(!mswIsValidEmail($email)) {
    echo_json_error("Invalid Email Address");
}

// make sure they do not have an account in the helpdesk system
$portal_login_query     = mysql_query("SELECT * FROM tickets.msp_portal WHERE email='".$email."';");
$admin_duplicate_query  = mysql_query("SELECT * FROM admins WHERE admin_email='".$email."';");
if(mysql_num_rows($portal_login_query) > 0 || mysql_num_rows($admin_duplicate_query) > 0){
    echo_json_error("Email Address is already in use");
}

// update their email address
$admin_update_query = mysql_query("UPDATE admins SET admin_email='$email' WHERE admin_id = $admin_id");
if(!$admin_update_query){
    echo_json_error("Could not update email. Please contact support");
}

$admin_info_query = mysql_query("SELECT first, last, admin_email, password FROM admins WHERE admin_id = $admin_id");
$admin = mysql_fetch_assoc($admin_info_query);
if(!create_admin($admin)){
    echo_json_error("Email Updated. Could not create Support Account. Please refresh the page and try again later.");
}

// log them into the helpdesk system
$_SESSION[mswEncrypt(SECRET_KEY) . '_msw_support'] = $email;

// tell the browser that it is good to go
echo json_encode([
    "success" => true
]);


