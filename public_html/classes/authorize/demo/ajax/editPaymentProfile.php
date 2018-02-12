<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

define("AUTHORIZE_NET_SANDBOX", true);

require_once('../../CustomerProfile.php');
require_once('../../PaymentProfile.php');

use classes\authorize\CustomerProfile;
use classes\authorize\PaymentProfile;

if ($_POST['command'] === "editPaymentProfile") {
    
    if(!$_POST["cardNumber"] || !$_POST["expirationDate"] || !$_POST["cardCode"] || !$_POST["profileId"] || !$_POST["paymentProfileId"]) {
        echo "Invalid Request";
    } else {
        $default = !!$_POST["default"];
        $paymentProfile = new PaymentProfile($_POST["paymentProfileId"], $_POST["profileId"]);
        // update the information
        $paymentProfile->cardNumber = $_POST["cardNumber"];
        $paymentProfile->expirationDate = $_POST["expirationDate"];
        $paymentProfile->cardCode = $_POST["cardCode"];
        $paymentProfile->default_card = !!$_POST["default"];
        print_r($paymentProfile->update());
    }
    
} else {
    echo "No Result";
}