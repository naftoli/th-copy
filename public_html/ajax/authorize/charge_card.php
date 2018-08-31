<?php

require_once("../../classes/authorize/CustomerProfile.php");

use \classes\authorize\CustomerProfile;

include("../../db.php"); // we need to eventually save transactions in the transactions table

include '../../check_for_spammers.php'; // just blocks one ip address for now. (39.53.201.236)

$school_id 				= mysql_real_escape_string(isset($_POST['school_id'])? $_POST['school_id'] : "");
$amount 				= mysql_real_escape_string($_POST['amount']);
$description 			= mysql_real_escape_string($_POST['description']);
$user_ids               = isset( $_POST['user_ids'] ) ? $_POST['user_ids'] : [];
$customer_profile_id	= mysql_real_escape_string($_POST['customer_profile_id']);
$payment_profile_id 	= mysql_real_escape_string($_POST['payment_profile_id']);

$admin_id = isset( $_COOKIE['admin_id'] ) ? mysql_real_escape_string( $_COOKIE['admin_id'] ) : 0;

$success = false;

if (!!$amount && !!$customer_profile_id && !!$payment_profile_id){

    $user_ids = implode( ', ', $user_ids );

    $transaction_query = mysql_query(
         " INSERT INTO transactions "
        ." (school_id, trans_date, amount, description, admin_id, users_registered) "
        ." VALUES ($school_id, NOW(), $amount, '$description', $admin_id, '$user_ids') "
    );
    $trans_id = mysql_insert_id(); // get the transaction id
    $description .= ". Transaction #: $trans_id";
    // load up the customer profile
    $customer_profile = new CustomerProfile($customer_profile_id, false);
	// charge the customer
    $response = $customer_profile->chargeCard($amount, $payment_profile_id, null, null, $description);
    
    if (is_array($response)) { // the response was good
        $success = true;

        // Update Transaction
        $response_text = json_encode( $response );
        mysql_query(
            "UPDATE transactions SET response = '$response_text' WHERE trans_id = $trans_id"
        );

        // save invoice
        $invoice = "INSERT INTO invoice_items VALUES($school_id, null, $amount, now(), 'charge', null, '$description', '')";
        @mysql_query($invoice);

    } else {
        mysql_query("DELETE FROM transactions WHERE transaction_id = $trans_id");
    }
} else { // the whole request is invalid
    $response = "Invalid Request";
}

// print the result for the user

echo json_encode([
    "success" => $success,
    "response" => $response
]);

