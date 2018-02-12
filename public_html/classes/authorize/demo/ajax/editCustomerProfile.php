<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

define("AUTHORIZE_NET_SANDBOX", true);

require_once('../../AuthorizeAPIRequest.php');
require_once('../../CustomerProfile.php');

use classes\authorize\AuthorizeAPIRequest;
use classes\authorize\CustomerProfile;

if ($_POST['command'] === "editCustomerProfile") {
    $customerProfile = new CustomerProfile($_POST["profileId"]);
    if ($customerProfile->invalid) {
        echo "Invalid profileId, Please try again.";
    } else {
        if ($_POST["merchantCustomerId"]) { $customerProfile->merchantCustomerId = $_POST["merchantCustomerId"];}
        if ($_POST["description"]) { $customerProfile->description = $_POST["description"];}
        if ($_POST["email"]) { $customerProfile->email = $_POST["email"];}
        
        echo "<pre>" . $customerProfile->update() . "</pre>";
    }
    
} else {
    echo "No Result";
}