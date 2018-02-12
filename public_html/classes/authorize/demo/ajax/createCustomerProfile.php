<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

define("AUTHORIZE_NET_SANDBOX", true);

require_once('../../AuthorizeAPIRequest.php');
require_once('../../CustomerProfile.php');
require_once('../../PaymentProfile.php');

use classes\authorize\AuthorizeAPIRequest;
use classes\authorize\CustomerProfile;
use classes\authorize\PaymentProfile;

if ($_POST['command'] === "createCustomerProfile") {
    
    if (!$_POST["merchantCustomerId"] || !$_POST["description"] || !$_POST["email"] ||
        !$_POST["cardNumber"] || !$_POST["expirationDate"] || !$_POST["cardCode"]) {
        echo "Invalid Submission";
    } else {
        $paymentProfile = PaymentProfile::createBasicArray($_POST["cardNumber"], $_POST["expirationDate"], $_POST["cardCode"]);
        $customerProfile = CustomerProfile::create($_POST["merchantCustomerId"], $_POST["email"], $_POST["description"], $paymentProfile, false);
        
        if($customerProfile instanceof CustomerProfile ) {
            echo "Success: the new customer profileId is " . $customerProfile->customerProfileId;
        } else if (is_array ($customerProfile)) {
            print_r($customerProfile);
        } else {
            print_r($customerProfile);
        }
    }
    
} else {
    echo "No Result";
}


?>