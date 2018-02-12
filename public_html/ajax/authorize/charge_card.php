<?php

require_once("../../classes/authorize/CustomerProfile.php");

use \classes\authorize\CustomerProfile;

include("../../db.php"); // we need to eventually save transactions in the transactions table

include '../../check_for_spammers.php'; // just blocks one ip address for now. (39.53.201.236)

foreach ($_POST as $k => $v) {
	$_POST[$k] = mysql_real_escape_string(trim($v));
}

$school_id 				= mysql_real_escape_string(isset($_POST['school_id'])? $_POST['school_id'] : "");
$amount 				= mysql_real_escape_string($_POST['amount']);
$description 			= mysql_real_escape_string($_POST['description']);
$customer_profile_id	= mysql_real_escape_string($_POST['customer_profile_id']);
$payment_profile_id 	= mysql_real_escape_string($_POST['payment_profile_id']);

$success = false;

if (!!$amount && !!$customer_profile_id && !!$payment_profile_id){
	// load up the customer profile
    $customer_profile = new CustomerProfile($customer_profile_id, false);
	// charge the customer
    $response = $customer_profile->chargeCard($amount, $payment_profile_id, null, null, $description);
    
    if (is_array($response)) { // the response was good
		$success = true;
		
		//TODO save response in transactions table?
		
		//TODO send email to customer?
		
    }
} else { // the whole request is invalid
    $response = "Invalid Request";
}

// print the result for the user

echo json_encode([
    "success" => $success,
    "response" => $response,
]);

