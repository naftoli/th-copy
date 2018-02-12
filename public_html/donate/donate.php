<?php
//echo "<pre>";
//print_r( $_POST );
//echo "</pre>";
//ini_set('display_errors',1);

$ip = $_SERVER['REMOTE_ADDR']; 
if ($ip == '39.53.201.236') {
	$msg = 'Go Away!';
	header("Location: https://mashpia.com/donate/index.php?error=" . $msg);
    exit;
}

chdir('../');
require_once 'db.php';
foreach ($_POST as $k => $v) {
	$_POST[$k] = mysql_real_escape_string(trim($v));
}

// check captcha
$privatekey = '6LcPSR0UAAAAAMMBnZpu9a4Ru5sNmrfgeEYVmWPw';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'secret'   => $privatekey,
    'response' => $_POST['g-recaptcha-response'],
    'remoteip' => $_SERVER['REMOTE_ADDR']
]);

$resp = json_decode(curl_exec($ch));
curl_close($ch);

if (!$resp->success) {
    $error = "Please click on the reCAPTCHA box.";
	header("Location: https://mashpia.com/donate/index.php?error=" . urlencode($error));
	exit;
}

$amount = (int)$_POST['amount'];
if ($amount == -1) {
	$amount = (int)$_POST['other'];
}
if (!($amount > 0)) {
	$error = "You have not entered a valid amount!";
	header("Location: https://mashpia.com/donate/index.php?error=" . urlencode($error));
	exit;
}

$card_num 	= $_POST['ccnum'];
$exp_date 	= $_POST['ccexp'];
$first_name = $_POST['ccfname'];
$last_name	= $_POST['cclname'];
$description = "Donation from " . ucwords($first_name) . ' ' . ucwords($last_name) . " - " . $_POST['desc'];
$address	= $_POST['ccaddress'] . ' ' . $_POST['ccaddress2'];
$city		= $_POST['cccity'];
$state		= $_POST['ccstate'];
$zip		= $_POST['cczip'];
$email 		= $_POST['email'];
$phone		= $_POST['phone'];

//if ($email != 'naftolir@gmail.com') {
	require_once 'authorize.php';
	$charged = false;
	$response = '';
	if ($response_array) {
		if ($response_array[0] == 1) {
			$response .= $response_array[0] . ":";
			$response .= $response_array[3] . ":";
			$response .= $response_array[4] . ":";
			$response .= $response_array[6] . ":";
			$response .= $response_array[9];
			$charged = true;
		}
		else {
			$response .= $response_array[3] . "\n";          
		}
	}
//} else {
//	$charged = true;
//	$response = 'testing';
//}

if ($charged) {
	$sql = "insert into all_donations set 
			email = '$email', 
			phone = '$phone', 
			amount = $amount, 
			response = '$response', 
			name = \"" . $first_name . ' ' . $last_name . "\", 
			address = \"" . $address . ' ' . $city . ',' . $state . ' ' . $zip . ' ' . $country . "\"";
	@mysql_query($sql);	
	
	// send confirmation email
    // if you want to modify who gets this email, then change lines following the BCC
    include_once("classes/send_mail.php");
    include_once("constant_file.php");
    
    $mail_parms = array();
    $mail_parms['to'] = "$email";   
    $mail_parms['subject'] = "Confirmation of Credit Card Transaction";
    $mail_parms['message'] = "Thank you for donating to Tzivos Hashem. Your credit card has been charged $" . $amount  .".00. Your authorization ID is: " . $response_array[4];
    $mail_parms['headers'] .= "From: cth@mashpia.com" . "\r\n" ;
    
    $send_mail = new MailClass();
    $send_mail->send_mail($mail_parms);
	
	$str = "Thank you for your donation of $" . $amount . ".00. <br />You should receive an email confirmation shortly.";
	header("Location: https://mashpia.com/donate/index.php?msg=" . urlencode($str));
	exit;
} else {
	header("Location: https://mashpia.com/donate/index.php?error=" . urlencode($response));
	exit;
}
?>