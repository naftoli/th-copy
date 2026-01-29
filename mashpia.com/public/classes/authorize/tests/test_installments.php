<?php
/**
 * Test file for creating installments using admin ID 1264
 * This file tests the Installments class functionality
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include the autoloader directly
$root_path = dirname(dirname(dirname(dirname(__DIR__))));
require_once $root_path . '/vendor/autoload.php';
require_once $root_path . '/includes/authorize_constants.php';

// Include necessary files with absolute paths
$class_dir = dirname(__DIR__); // Get the parent directory (classes/authorize)
require_once $class_dir . '/CustomerProfile.php';
require_once $class_dir . '/Installments.php';
require_once $class_dir . '/PaymentProfile.php';

use \classes\authorize\CustomerProfile;
use \classes\authorize\Installments;
use \classes\authorize\PaymentProfile;

// Set admin ID
$admin_id = 1264;

// Connect to database to get customer profile ID
// Check if running from command line or web server
if (isset($_SERVER['DOCUMENT_ROOT']) && !empty($_SERVER['DOCUMENT_ROOT'])) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
} else {
    // For command line execution
    $root_path = dirname(dirname(dirname(__DIR__)));
    require_once $root_path . '/db.php';
    require_once $root_path . '/api/header/db.php';
}

// Get customer profile ID for admin
$sql = "SELECT authorize_customer_profile_id, first, last, admin_email FROM admins WHERE admin_id = $admin_id";
$result = mysql_query($sql);

if (!$result) {
    die("Database query failed: " . mysql_error());
}

$row = mysql_fetch_assoc($result);
// $customer_profile_id = $row['authorize_customer_profile_id'];
$customer_profile_id = 931063847;
$first_name = $row['first'];
$last_name = $row['last'];
$email = $row['admin_email'];

// For testing, we'll check if the profile exists in sandbox and create it if needed
$use_production = false; // Always use sandbox for testing

echo "<h2>Testing Installments for Admin ID: $admin_id</h2>";
echo "Original Customer Profile ID from DB: $customer_profile_id<br>";

// Try to load the customer profile in sandbox
try {
    $customer_profile = new CustomerProfile($customer_profile_id, true, null, true); // true for sandbox
    
    if ($customer_profile->invalid) {
        echo "<p>Error: Customer profile not found in sandbox environment.</p>";
        echo "<p>Error details: " . print_r($customer_profile->error_return, true) . "</p>";
        
        die("Cannot proceed without a valid customer profile");
    } else {
        echo "<p>Successfully loaded existing customer profile from sandbox.</p>";
    }
} catch (Exception $e) {
    echo "<p>Exception when loading/creating customer profile: " . $e->getMessage() . "</p>";
    die("Cannot proceed due to exception");
}

echo "Customer Profile ID for testing: $customer_profile_id<br>";

// We already have the customer profile object from above
try {
    echo "Successfully loaded customer profile for: " . $customer_profile->description . "<br>";
    
    // Get payment profile ID - we need to make sure we're using a valid payment profile
    if (empty($customer_profile->paymentProfiles)) {
        echo "<p>Error: No payment profiles found for this customer. Cannot proceed.</p>";
        die("Please add a payment method to this customer profile first.");
    }
    
    // Use the first payment profile
    $payment_profile_id = $customer_profile->paymentProfiles[0]['customerPaymentProfileId'];
    echo "Using payment profile ID: $payment_profile_id<br><br>";
    
    // Create Installments object with the correct payment profile ID
    $installments = new Installments($customer_profile, $payment_profile_id, false, true);
    
    // Test parameters
    $amount = 100.00; // Total amount
    $num_installments = 2; // Number of installments
    $start_date = date('Y-m-d'); // Start today for testing
    
    echo "Creating subscription with the following parameters:<br>";
    echo "Amount: $" . $amount . "<br>";
    echo "Number of installments: " . $num_installments . "<br>";
    echo "Start date: " . $start_date . "<br>";
    echo "<br />";
    
    // Create subscription with specific start date
    $response = $installments->createSubscription($amount, $num_installments, $start_date, $admin_id);
    
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
