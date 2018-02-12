<?php
// load the api
require_once("../../classes/authorize/PaymentProfile.php");
use \classes\authorize\PaymentProfile;
// default return values
$success = false;
$response = "Error (400): Bad Request";
// get the post values
$customer_profile_id = $_POST["customer_profile_id"];
$payment_profile_id = $_POST["payment_profile_id"];
$cc_number = $_POST["cc_number"];
$cc_exp = $_POST["cc_exp"];
$cc_cvv = $_POST["cc_cvv"];
$billing_address = $_POST["customer_profile_id"];
$billing_city = $_POST["billing_city"];
$billing_state = $_POST["billing_state"];
$billing_postal = $_POST["billing_postal"];
// if they are present
if(!!$customer_profile_id && !!$payment_profile_id && !!$cc_number && !!$cc_exp && !!$cc_cvv  && !!$billing_address  && !!$billing_city && !!$billing_state  && !!$billing_postal){
    // create the bill to array
    $billTo = ["address"=>$billing_address, "city"=>$billing_city, "state"=>$billing_state, "zip"=>$billing_postal];
    // create a payment profile
    $payment_profile = new PaymentProfile($payment_profile_id, $customer_profile_id, false);
    // update the CC info
    $payment_profile->cardNumber = $cc_number;
    $payment_profile->expirationDate = $cc_exp;
    $payment_profile->cardCode = $cc_cvv;
    // update the billing info
    $payment_profile->billTo = $billTo;
    // update it and get any errors
    $errors = $payment_profile->update();
    // if there are no errors update the status
    if(!$errors) {
        $success = true;
        $response = "Success";
    } else { // if there are errors, return the code.
        $code = $errors['messages']['message'][0]["code"];
        $msg = $errors['messages']['message'][0]["text"];
        $response = "Error ($code): $msg";
    }
} else if(!!$customer_profile_id && !!$payment_profile_id && !!$cc_number && !!$cc_exp) {
    $success = true;
    $response = "No Update";
}

echo json_encode([
    "success" => $success,
    "response" => $response
]);

?>