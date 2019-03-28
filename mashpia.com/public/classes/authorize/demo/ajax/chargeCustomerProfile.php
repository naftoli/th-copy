<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

define("AUTHORIZE_NET_SANDBOX", true);

require_once('../../AuthorizeAPIRequest.php');
require_once('../../CustomerProfile.php');
require_once('../../PaymentProfile.php');

use classes\authorize\AuthorizeAPIRequest;
use classes\authorize\CustomerProfile;

if ($_POST['command'] === "chargeCustomerProfile" && !!$_POST["profileId"] && !!$_POST["amount"]) {
    $customerProfile = new CustomerProfile($_POST["profileId"], false);
    
    // get the paymentProfile
    if (!!$_POST["paymentProfileId"]) {
        print_r($customerProfile->chargeCard($_POST["amount"], $_POST["paymentProfileId"]));
    } else {
        print_r($customerProfile->chargeCard($_POST["amount"]));
    }
    
} else {
    echo "Invalid Request.";
}


?>