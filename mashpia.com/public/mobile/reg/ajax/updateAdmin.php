<?
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
session_start(); // start the session in case we need to change the email for helpdesk

require '../../../db.php';
// log the user into the helpdesk system as well
require_once($_SERVER["DOCUMENT_ROOT"].'/helpdesk/control/connect.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/helpdesk/control/functions.php');
// load up the functions for adding users to the DBS
require_once($_SERVER["DOCUMENT_ROOT"].'/tasks/forms/functions/helpdesk_account_migration.php');

$json_response = isset($_GET['json']) && $_GET['json'] == true;

$admin_id 	= mysql_real_escape_string( $_POST['admin_id'] 	);
$father 	= mysql_real_escape_string( $_POST['father'] 	);
$mother 	= mysql_real_escape_string( $_POST['mother'] 	);
$last 		= mysql_real_escape_string( $_POST['last'] 		);
$email 		= mysql_real_escape_string( $_POST['email'] 	);
$phone 		= mysql_real_escape_string( $_POST['phone'] 	); 
$phone2 	= mysql_real_escape_string( $_POST['phone2'] 	); 
$username 	= mysql_real_escape_string( $_POST['username'] 	);
$password 	= mysql_real_escape_string( $_POST['pwd'] 		);
$address 	= mysql_real_escape_string( $_POST['address'] 	);
$city 		= mysql_real_escape_string( $_POST['city'] 		);
$state 		= mysql_real_escape_string( $_POST['state'] 	);
$zip 		= mysql_real_escape_string( $_POST['zip'] 		);
$country 	= mysql_real_escape_string( $_POST['country'] 	);
$fatherPic 	= mysql_real_escape_string( $_POST['fatherPic'] );
$motherPic 	= mysql_real_escape_string( $_POST['motherPic'] );

require 'encrypt.php';
$admin_id = encrypt_decrypt('decrypt', $admin_id);

$old_email_query = mysql_query("SELECT admin_email FROM admins WHERE admin_id='$admin_id'");
$old_email = mysql_fetch_assoc($old_email_query)['admin_email'];

if(!mswIsValidEmail($email)){
	die();
}

$sql = "UPDATE admins SET "
	."father = '$father', "
	."mother = '$mother', "
	."last = '$last', "
	."admin_email = '$email', "
	."admin_phone_mobile = '$phone', "
	."admin_phone_mobile2 = '$phone2', "
	."username = '$username', "
	."admin_address1 = '$address', "
	."admin_city = '$city', "
	."admin_state = '$state', "
	."admin_postal = '$zip', "
	."admin_country = '$country', "
	."father_pic = '" . $fatherPic . "', "
	."mother_pic = '" . $motherPic . "'";

if (!empty( $password )) {
	$sql .= ", password = '" . $password . "'";
}
$sql .= " where admin_id = " . $admin_id;

$success = mysql_query($sql);

$portal_login_query     = mysql_query("SELECT * FROM tickets.msp_portal WHERE email='".$email."';");
if($success && mysql_num_rows($portal_login_query) > 0){
	// update helpdesk password if password changed
	if(mswIsValidEmail($email) && !empty($password)) {
		$userPass = mswEncrypt(SECRET_KEY . $password); // sha1 or md5 with secret key
		$update_helpdesk_password = mysql_query("UPDATE tickets.msp_portal SET userPass='$userPass' WHERE email='$old_email'"); // accounts in helpdesk are email based
	}
	// if the email changed update the login info for helpdesk
	if($email !== $old_email && mswIsValidEmail($email)) {
		$update_helpdesk_email = mysql_query("UPDATE tickets.msp_portal SET email='$email' WHERE email='$old_email'"); // accounts in helpdesk are email based
		$_SESSION[mswEncrypt(SECRET_KEY) . '_msw_support'] = $email; // set the correct session token to the new email...
	}
} elseif($success) {
	$admin_info_query = mysql_query("SELECT first, last, admin_email, password FROM admins WHERE admin_id = $admin_id");
	$admin = mysql_fetch_assoc($admin_info_query);
	create_admin($admin);
}

echo $success;
?>