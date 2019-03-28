<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

define("AUTHORIZE_NET_SANDBOX", false);

require_once('../../AuthorizeAPIRequest.php');
require_once('../../CustomerProfile.php');

use classes\authorize\AuthorizeAPIRequest;
use classes\authorize\CustomerProfile;

if ($_POST['command'] === "getCustomerProfile") {
    $customerProfile = new CustomerProfile($_POST["profileId"]);
    if ($customerProfile->invalid) {
        echo "Invalid profileId, Please try again.";
    } else {
        ?>
Merchant ID: <?php echo $customerProfile->merchantCustomerId;?><br>
Email: <?php echo $customerProfile->email;?><br>
Description: <?php echo $customerProfile->description;?><br>
PaymentProfiles: (<?php echo count($customerProfile->paymentProfiles)?>)
<?
    foreach ($customerProfile->paymentProfiles as $paymentProfile) {
        $cc = $paymentProfile["payment"]["creditCard"];
        $billTo = $paymentProfile["billTo"];
        echo "- " . $paymentProfile["customerPaymentProfileId"] .": " . $billTo['firstName'] . " " .
        $billTo["lastName"] . ": " . $cc["cardType"] . ", " . $cc["cardNumber"] ." ". ($billTo['zip']) ."<br>";
    }
?>
ShippingProfiles: (<?php echo count($customerProfile->shipToList)?>)
<?
if (count($customerProfile->shipToList) > 0) {
    foreach ($customerProfile->shipToList as $shippingProfile) {
        echo "- " . $shippingProfile["customerAddressId"] .": " . $shippingProfile['firstName'] . " " . $shippingProfile["lastName"] .
        ", " . $shippingProfile['address'] . " " . $shippingProfile['city'] . ", " . $shippingProfile['state'] . " " . $shippingProfile['zip'] ."<br>";
    }
} ?>
        <?php
    }
    
} else {
    echo "Invalid Request";
}