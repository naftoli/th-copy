<?php
// start the session for Helpdesk login
session_start();
// error_reporting(E_ALL);
ini_set('display_errors',1);
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$username = mysql_real_escape_string($_POST['username']);
$password = mysql_real_escape_string($_POST['password']);

$sql = "SELECT admin_id, admin_email FROM admins WHERE username = '" . $username . "' AND password = '" . $password . "'";
$result = mysql_query($sql);

if (mysql_num_rows($result) > 0) {
	$row = mysql_fetch_assoc($result);
	$admin = $row['admin_id'];
	
	// log the user into the helpdesk system as well
	if ( $_SERVER['SERVER_NAME'] != 'tzivos.local' ) {
		require_once($_SERVER["DOCUMENT_ROOT"].'/helpdesk/control/connect.php');
		require_once($_SERVER["DOCUMENT_ROOT"].'/helpdesk/control/functions.php');
		if(mswIsValidEmail($row['admin_email'])) {
			$portal_login_sql = mysql_query("SELECT * FROM tickets.msp_portal WHERE email='".$row['admin_email']."';");
			if(mysql_num_rows($portal_login_sql) > 0){
				$_SESSION[mswEncrypt(SECRET_KEY) . '_msw_support'] = $row['admin_email']; // set the correct key in the session to the users email address
			}
		}
	}
	
	// encrypt admin id
	require( dirname(__FILE__) . '/encrypt.php' );
	echo encrypt_decrypt('encrypt', $admin);
} else {
	echo 0;
}
?>