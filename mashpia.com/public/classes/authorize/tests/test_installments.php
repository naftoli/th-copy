<?php
/**
 * Test file for creating installments using admin ID 1264
 * This file tests the Installments class functionality
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include necessary files
require_once '../CustomerProfile.php';
require_once '../Installments.php';

use \classes\authorize\CustomerProfile;
use \classes\authorize\Installments;

// Set admin ID
$admin_id = 1264;

// Connect to database to get customer profile ID
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

// Get customer profile ID for admin
$sql = "SELECT authorize_customer_profile_id FROM admins WHERE admin_id = $admin_id";
$result = mysql_query($sql);

if (!$result) {
    die("Database query failed: " . mysql_error());
}

$row = mysql_fetch_assoc($result);
$customer_profile_id = $row['authorize_customer_profile_id'];
$customer_profile_id = 931063847;

if (empty($customer_profile_id)) {
    die("No customer profile ID found for admin ID $admin_id");
}

echo "<h2>Testing Installments for Admin ID: $admin_id</h2>";
echo "Customer Profile ID: $customer_profile_id<br>";

// Create CustomerProfile object
try {
    // Set to false for sandbox/testing environment
    $use_production = false;
    
    // Load the customer profile
    $customer_profile = new CustomerProfile($customer_profile_id);
    
    if ($customer_profile->invalid) {
        die("Error loading customer profile: " . print_r($customer_profile->error_return, true));
    }
    
    echo "Successfully loaded customer profile for: " . $customer_profile->description . "<br><br>";
    
    // Create Installments object
    $installments = new Installments($customer_profile, 0, $use_production, false);
    
    // Test parameters
    $amount = 100.00; // Total amount
    $num_installments = 2; // Number of installments
    
    echo "Creating subscription with the following parameters:<br>";
    echo "Amount: $" . $amount . "<br>";
    echo "Number of installments: " . $num_installments . "<br>";
    echo "<br />";
    
    // Create subscription
    $response = $installments->createSubscription($amount, $num_installments);
    
    echo "Response from creating subscription: " . $response . "<br>";
    
    if (strpos($response, "Success") !== false) {
        $subscription_id = $installments->getSubscriptionId();
        echo "Subscription ID: " . $subscription_id . "<br><br>";
        
        // Save to database - using current year
        $current_year = 5785;
        $db_result = $installments->saveToDb($MASHPIA_DB, $admin_id, $current_year);
        
        if ($db_result) {
            echo "Successfully saved subscription to database.<br>";
        } else {
            echo "Failed to save subscription to database.<br>";
        }
        
        // Get subscription info
        echo "<h3>Subscription Information:</h3>";
        $subscription_info = $installments->getSubscriptionInfo($subscription_id);
        
        if ($subscription_info != null) {
            if ($subscription_info->getMessages()->getResultCode() == 'Ok') {
                // Displaying the details
                echo 'Subscription Name: ' . $subscription_info->getSubscription()->getName() . "<br>";
                echo 'Subscription amount: ' . $subscription_info->getSubscription()->getAmount() . "<br>";
                echo 'Subscription status: ' . $subscription_info->getSubscription()->getStatus() . "<br>";
                echo 'Subscription Description: ' . $subscription_info->getSubscription()->getProfile()->getDescription() . "<br>";
                echo 'Customer Profile ID: ' . $subscription_info->getSubscription()->getProfile()->getCustomerProfileId() . "<br>";
                echo 'Customer payment Profile ID: ' . $subscription_info->getSubscription()->getProfile()->getPaymentProfile()->getCustomerPaymentProfileId() . "<br>";
            } else {
                // Error
                echo "ERROR: Invalid response<br>";
                $errorMessages = $subscription_info->getMessages()->getMessage();
                echo 'Response: ' . $errorMessages[0]->getCode() . ' ' . $errorMessages[0]->getText() . "<br>";
            }
        } else {
            // Failed to get response
            echo 'Null Response Error';
        }
        
        // Uncomment the following lines to cancel the subscription after testing
        echo "<h3>Cancelling Subscription:</h3>";
        $cancel_response = $installments->cancelSubscription();
        echo "Cancel response: " . $cancel_response . "<br>";
        
        if (strpos($cancel_response, "Success") !== false) {
            // Remove from database
            $remove_result = $installments->removeFromDb($MASHPIA_DB);
            
            if ($remove_result) {
                echo "Successfully removed subscription from database.<br>";
            } else {
                echo "Failed to remove subscription from database.<br>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
