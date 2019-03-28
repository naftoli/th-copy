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

if ($_POST['command'] === "createPaymentProfile") {
    
    if(!$_POST["cardNumber"] || !$_POST["expirationDate"] || !$_POST["cardCode"] || !$_POST["profileId"]) {
        echo "Invalid Request";
    } else {
        $default = !!$_POST["default"];
        $api = new AuthorizeAPIRequest();
        $paymentProfile = PaymentProfile::create($_POST["cardNumber"], $_POST["expirationDate"],
                                                 $_POST["cardCode"], $_POST["profileId"], null, $default);
        print_r($paymentProfile);
    }
    
} else {
    echo "No Result";
}