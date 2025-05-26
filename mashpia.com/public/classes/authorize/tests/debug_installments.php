<?php
/**
 * Debug script for Installments class
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include the autoloader first to avoid issues
$root_path = dirname(dirname(dirname(dirname(__DIR__))));
require_once $root_path . '/vendor/autoload.php';

// Include necessary files
require_once dirname(__DIR__) . '/CustomerProfile.php';
require_once dirname(__DIR__) . '/Installments.php';
require_once dirname(__DIR__) . '/PaymentProfile.php';

use \classes\authorize\CustomerProfile;
use \classes\authorize\Installments;
use \classes\authorize\PaymentProfile;

// Customer profile ID and payment profile ID from previous successful tests
$customer_profile_id = 931063847;
$payment_profile_id = 930354017;

echo "<h2>Debugging Installments Class</h2>";

// First, let's check what the endpoint constants actually contain
echo "<h3>Checking SDK Endpoint Constants:</h3>";
echo "SANDBOX constant: " . \net\authorize\api\constants\ANetEnvironment::SANDBOX . "<br>";
echo "PRODUCTION constant: " . \net\authorize\api\constants\ANetEnvironment::PRODUCTION . "<br>";

// Load the customer profile
try {
    echo "<h3>Loading Customer Profile:</h3>";
    $customer_profile = new CustomerProfile($customer_profile_id, true, null, true); // true for sandbox
    
    if ($customer_profile->invalid) {
        echo "<p style='color:red;'>Error loading customer profile: " . print_r($customer_profile->error_return, true) . "</p>";
        die("Cannot proceed without a valid customer profile");
    } else {
        echo "<p style='color:green;'>Successfully loaded customer profile: " . $customer_profile->description . "</p>";
    }
    
    // Create a modified version of the Installments class for debugging
    echo "<h3>Creating Modified Installments Object:</h3>";
    
    class DebugInstallments extends \classes\authorize\Installments {
        public function __construct($customerProfile = null, $payment_profile_id = 0, $updateBilling = true, $sandbox = false) {
            parent::__construct($customerProfile, $payment_profile_id, $updateBilling, $sandbox);
        }
        
        // Override createSubscription to add debugging
        public function createSubscription($amount, $numInstallments, $start_date = null) {
            echo "<p>Creating subscription with amount: $amount, installments: $numInstallments, start date: " . ($start_date ?? "default") . "</p>";
            echo "<p>Using endpoint: " . $this->endpoint . "</p>";
            
            $this->total_amount = $amount;
            $this->number_of_installments = $numInstallments;
            $this->installment_amount = round(floatval($amount / $numInstallments), 2);
            $this->start_date = $start_date ?? date('Y-m-d', strtotime("+1 month"));
            
            $merchantAuthentication = $this->setAuth();
            echo "<p>Merchant Authentication Name: " . $merchantAuthentication->getName() . "</p>";
            echo "<p>Merchant Authentication Transaction Key: " . substr($merchantAuthentication->getTransactionKey(), 0, 4) . "...</p>";
            
            // Set the transaction's refId
            $refId = 'ref' . time();
            
            // Subscription Type Info
            $subscription = new \net\authorize\api\contract\v1\ARBSubscriptionType();
            $subscription->setName("Subscription for " . $this->cp->description);
            
            $interval = new \net\authorize\api\contract\v1\PaymentScheduleType\IntervalAType();
            $interval->setLength(1);
            $interval->setUnit("months");
            
            $paymentSchedule = new \net\authorize\api\contract\v1\PaymentScheduleType();
            $paymentSchedule->setInterval($interval);
            $paymentSchedule->setStartDate(new \DateTime($this->start_date));
            $paymentSchedule->setTotalOccurrences($numInstallments);
            
            $subscription->setPaymentSchedule($paymentSchedule);
            $subscription->setAmount($this->installment_amount);
            
            $profile = new \net\authorize\api\contract\v1\CustomerProfileIdType();
            $profile->setCustomerProfileId($this->cp->customerProfileId);
            $profile->setCustomerPaymentProfileId($this->payment_profile_id);
            $subscription->setProfile($profile);
            
            $request = new \net\authorize\api\contract\v1\ARBCreateSubscriptionRequest();
            $request->setmerchantAuthentication($merchantAuthentication);
            $request->setRefId($refId);
            $request->setSubscription($subscription);
            
            echo "<p>Request prepared. About to execute with endpoint: " . $this->endpoint . "</p>";
            
            // Try both ways of executing the request
            echo "<h4>Test 1: Using this->endpoint directly:</h4>";
            try {
                $controller = new \net\authorize\api\controller\ARBCreateSubscriptionController($request);
                $response = $controller->executeWithApiResponse($this->endpoint);
                echo "<p style='color:green;'>API call executed successfully!</p>";
                $res = $this->parseResponse($response);
                echo "<p>Response: " . $res . "</p>";
                if (strpos($res, "Success") !== false) $this->subscription_id = $response->getSubscriptionId();
            } catch (\Exception $e) {
                echo "<p style='color:red;'>Error executing API call: " . $e->getMessage() . "</p>";
            }
            
            echo "<h4>Test 2: Using ANetEnvironment constant directly:</h4>";
            try {
                $controller = new \net\authorize\api\controller\ARBCreateSubscriptionController($request);
                $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
                echo "<p style='color:green;'>API call executed successfully!</p>";
                $res = $this->parseResponse($response);
                echo "<p>Response: " . $res . "</p>";
                if (strpos($res, "Success") !== false) $this->subscription_id = $response->getSubscriptionId();
                return $res;
            } catch (\Exception $e) {
                echo "<p style='color:red;'>Error executing API call: " . $e->getMessage() . "</p>";
                return "Error: " . $e->getMessage();
            }
        }
    }
    
    // Create the debug installments object
    $installments = new DebugInstallments($customer_profile, $payment_profile_id, false, true);
    
    // Test creating a subscription
    echo "<h3>Testing Subscription Creation:</h3>";
    $amount = 100.00;
    $num_installments = 2;
    $start_date = date('Y-m-d');
    
    $response = $installments->createSubscription($amount, $num_installments, $start_date);
    
    echo "<h3>Final Result:</h3>";
    echo "<p>" . $response . "</p>";
    
    // If successful, cancel the subscription
    if (strpos($response, "Success") !== false) {
        $subscription_id = $installments->getSubscriptionId();
        echo "<p>Created subscription ID: " . $subscription_id . "</p>";
        
        echo "<h3>Cancelling Test Subscription:</h3>";
        $cancel_response = $installments->cancelSubscription();
        echo "<p>Cancel response: " . $cancel_response . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>Exception: " . $e->getMessage() . "</p>";
}
?>
