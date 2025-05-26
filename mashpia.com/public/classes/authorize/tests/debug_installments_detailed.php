<?php
/**
 * Detailed debug script for the Installments class
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include the autoloader directly
require_once '/Users/naftolir/Sites/tzivos-hashem/mashpia.com/vendor/autoload.php';
require_once '/Users/naftolir/Sites/tzivos-hashem/mashpia.com/includes/authorize_constants.php';

use includes\authorize\AuthorizeConstants as Constants;

echo "<h2>Debugging Installments Class in Detail</h2>";

// Define a debug function
function debug_print($message, $data = null) {
    echo "<p><strong>$message</strong></p>";
    if ($data !== null) {
        echo "<pre>" . print_r($data, true) . "</pre>";
    }
}

// First, let's check the paths
debug_print("Current directory", __DIR__);
debug_print("Parent directory", dirname(__DIR__));
debug_print("Grandparent directory", dirname(dirname(__DIR__)));
debug_print("Great-grandparent directory", dirname(dirname(dirname(__DIR__))));
debug_print("Great-great-grandparent directory", dirname(dirname(dirname(dirname(__DIR__)))));

// Check if the vendor directory exists
$vendor_path = '/Users/naftolir/Sites/tzivos-hashem/mashpia.com/vendor';
debug_print("Vendor directory exists", file_exists($vendor_path) ? "Yes" : "No");

// Check if the autoload.php file exists
$autoload_path = $vendor_path . '/autoload.php';
debug_print("Autoload.php exists", file_exists($autoload_path) ? "Yes" : "No");

// Now let's create a modified version of the Installments class for debugging
debug_print("Creating modified Installments class for debugging");

// Include the CustomerProfile class
require_once dirname(__DIR__) . '/CustomerProfile.php';
use \classes\authorize\CustomerProfile;

// Define a debugging version of the Installments class
class DebugInstallments {
    private $cp;
    private $endpoint;
    private $payment_profile_id;
    private $subscription_id;
    private $total_amount;
    private $installment_amount;
    private $number_of_installments;
    private $start_date;
    private $sandbox;

    public function __construct($customerProfile = null, $payment_profile_id = 0, $updateBilling = true, $sandbox = false) {
        debug_print("Installments constructor called with", [
            'customerProfile' => $customerProfile ? "CustomerProfile object" : "null",
            'payment_profile_id' => $payment_profile_id,
            'updateBilling' => $updateBilling ? "true" : "false",
            'sandbox' => $sandbox ? "true" : "false"
        ]);
        
        $this->sandbox = $sandbox;
        debug_print("Setting sandbox to", $sandbox ? "true" : "false");
        
        if ($sandbox) {
            $this->endpoint = \net\authorize\api\constants\ANetEnvironment::SANDBOX;
            debug_print("Using SANDBOX endpoint", $this->endpoint);
        } else {
            $this->endpoint = \net\authorize\api\constants\ANetEnvironment::PRODUCTION;
            debug_print("Using PRODUCTION endpoint", $this->endpoint);
        }

        if ($customerProfile instanceof CustomerProfile) {
            $this->cp = $customerProfile;
            $this->payment_profile_id = $payment_profile_id;
            debug_print("Customer profile set", [
                'customerProfileId' => $this->cp->customerProfileId,
                'description' => $this->cp->description,
                'payment_profile_id' => $this->payment_profile_id
            ]);
            
            if ($updateBilling) {
                debug_print("Updating billing info");
                if (!$this->updateBillingInfo()) {
                    debug_print("Error updating billing info");
                    throw new \Exception('Error updating billing info');
                } else {
                    debug_print("Billing info updated successfully");
                }
            }
        } else {
            debug_print("No customer profile provided");
        }
    }

    public function setAuth() {
        debug_print("setAuth called");
        $merchantAuthentication = new \net\authorize\api\contract\v1\MerchantAuthenticationType();
        $merchantAuthentication->setName(Constants::GetMerchantLoginID($this->sandbox));
        $merchantAuthentication->setTransactionKey(Constants::GetMerchantTransactionKey($this->sandbox));
        
        debug_print("Auth credentials set", [
            'name' => Constants::GetMerchantLoginID($this->sandbox),
            'transactionKey' => substr(Constants::GetMerchantTransactionKey($this->sandbox), 0, 4) . "..."
        ]);
        
        return $merchantAuthentication;
    }

    public function getEndpoint() {
        return $this->endpoint;
    }

    private function updateBillingInfo() {
        debug_print("updateBillingInfo called");
        $merchantAuthentication = $this->setAuth();
        $refId = 'ref' . time();

        if (! empty($this->cp->description)) $name = explode(" ", $this->cp->description);
        else $name = $this->getName();
        $last_name = array_pop($name);
        $first_name = implode(" ", $name);

        $billto = new \net\authorize\api\contract\v1\CustomerAddressType();
        $billto->setFirstName($first_name);
        $billto->setLastName($last_name);

        debug_print("Customer name", [
            'first_name' => $first_name,
            'last_name' => $last_name
        ]);

        // get payment profile
        if (! count($this->cp->paymentProfiles)) {
            debug_print("No payment profiles found");
            throw new \Exception("No payment profiles found");
        } else {
            debug_print("Payment profiles found", count($this->cp->paymentProfiles));
            $profile = null;
            foreach ($this->cp->paymentProfiles as $p) {
                if ($p['customerPaymentProfileId'] == $this->payment_profile_id) {
                    $profile = $p;
                    break;
                }
            }
            
            if (!$profile) {
                debug_print("Payment profile not found with ID", $this->payment_profile_id);
                debug_print("Available payment profiles", array_column($this->cp->paymentProfiles, 'customerPaymentProfileId'));
                throw new \Exception("Payment profile not found with ID: " . $this->payment_profile_id);
            }
            
            debug_print("Using payment profile", [
                'customerPaymentProfileId' => $profile['customerPaymentProfileId'],
                'cardNumber' => isset($profile['payment']['creditCard']['cardNumber']) ? 
                    substr($profile['payment']['creditCard']['cardNumber'], -4) : "Not available"
            ]);
        }

        $creditCard = new \net\authorize\api\contract\v1\CreditCardType();
        $creditCard->setCardNumber($profile['payment']['creditCard']['cardNumber']);
        $creditCard->setExpirationDate($profile['payment']['creditCard']['expirationDate']);

        $paymentCreditCard = new \net\authorize\api\contract\v1\PaymentType();
        $paymentCreditCard->setCreditCard($creditCard);

        $paymentprofile = new \net\authorize\api\contract\v1\CustomerPaymentProfileExType();
        $paymentprofile->setBillTo($billto);
        $paymentprofile->setCustomerPaymentProfileId($this->payment_profile_id);
        $paymentprofile->setPayment($paymentCreditCard);

        // Submit a UpdatePaymentProfileRequest
        $request = new \net\authorize\api\contract\v1\UpdateCustomerPaymentProfileRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setCustomerProfileId($this->cp->customerProfileId);
        $request->setPaymentProfile($paymentprofile);
        $request->setRefId($refId);

        debug_print("Executing UpdateCustomerPaymentProfileRequest with endpoint", $this->endpoint);
        
        try {
            $controller = new \net\authorize\api\controller\UpdateCustomerPaymentProfileController($request);
            $response = $controller->executeWithApiResponse($this->endpoint);
            
            if ($response != null) {
                debug_print("Response received", [
                    'resultCode' => $response->getMessages()->getResultCode()
                ]);
                
                $res = $this->parseResponse($response);
                debug_print("Parsed response", $res);
                
                if (strpos($res, "Success") !== false) {
                    return true;
                } else {
                    return false;
                }
            } else {
                debug_print("Null response received");
                return false;
            }
        } catch (\Exception $e) {
            debug_print("Exception during API call", $e->getMessage());
            return false;
        }
    }

    public function createSubscription($amount, $numInstallments, $start_date = null) {
        debug_print("createSubscription called with", [
            'amount' => $amount,
            'numInstallments' => $numInstallments,
            'start_date' => $start_date
        ]);
        
        $this->total_amount = $amount;
        $this->number_of_installments = $numInstallments;
        $this->installment_amount = round(floatval($amount / $numInstallments), 2);
        $this->start_date = $start_date ?? date('Y-m-d', strtotime("+1 month"));
        
        debug_print("Calculated values", [
            'total_amount' => $this->total_amount,
            'number_of_installments' => $this->number_of_installments,
            'installment_amount' => $this->installment_amount,
            'start_date' => $this->start_date
        ]);

        $merchantAuthentication = $this->setAuth();
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
        
        debug_print("Executing ARBCreateSubscriptionRequest with endpoint", $this->endpoint);
        
        try {
            $controller = new \net\authorize\api\controller\ARBCreateSubscriptionController($request);
            
            // First try with the stored endpoint
            debug_print("First attempt: Using stored endpoint", $this->endpoint);
            $response = $controller->executeWithApiResponse($this->endpoint);
            
            if ($response != null) {
                debug_print("Response received", [
                    'resultCode' => $response->getMessages()->getResultCode()
                ]);
                
                $res = $this->parseResponse($response);
                debug_print("Parsed response", $res);
                
                if (strpos($res, "Success") !== false) {
                    $this->subscription_id = $response->getSubscriptionId();
                    debug_print("Subscription created with ID", $this->subscription_id);
                }
                
                return $res;
            } else {
                debug_print("Null response received");
                return "Error: Null response received";
            }
        } catch (\Exception $e) {
            debug_print("Exception during API call", $e->getMessage());
            
            // Try again with the SDK constant directly
            debug_print("Second attempt: Using SDK constant directly");
            try {
                $controller = new \net\authorize\api\controller\ARBCreateSubscriptionController($request);
                $response = $controller->executeWithApiResponse(
                    $this->sandbox ? \net\authorize\api\constants\ANetEnvironment::SANDBOX : 
                                    \net\authorize\api\constants\ANetEnvironment::PRODUCTION
                );
                
                if ($response != null) {
                    debug_print("Response received", [
                        'resultCode' => $response->getMessages()->getResultCode()
                    ]);
                    
                    $res = $this->parseResponse($response);
                    debug_print("Parsed response", $res);
                    
                    if (strpos($res, "Success") !== false) {
                        $this->subscription_id = $response->getSubscriptionId();
                        debug_print("Subscription created with ID", $this->subscription_id);
                    }
                    
                    return $res;
                } else {
                    debug_print("Null response received");
                    return "Error: Null response received";
                }
            } catch (\Exception $e2) {
                debug_print("Exception during second API call", $e2->getMessage());
                return "Error: " . $e2->getMessage();
            }
        }
    }

    private function parseResponse($response) {
        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
            $message = $response->getMessages()->getMessage();
            return "Success: " . $message[0]->getText() . " (" . $message[0]->getCode() . ")";
        } else if ($response != null) {
            $message = $response->getMessages()->getMessage();
            return "Error: " . $message[0]->getText() . " (" . $message[0]->getCode() . ")";
        }
    }

    public function getSubscriptionId() {
        return $this->subscription_id;
    }
}

// Now let's test with the debugging version
debug_print("Starting test with debugging version");

// Customer profile ID and payment profile ID from previous successful tests
$customer_profile_id = 931063847;
$payment_profile_id = 930354017;

try {
    // First, load the customer profile
    debug_print("Loading customer profile with ID", $customer_profile_id);
    
    // Create a customer profile object
    $customer_profile = new CustomerProfile($customer_profile_id, true, null, true); // true for sandbox
    
    if ($customer_profile->invalid) {
        debug_print("Error loading customer profile", $customer_profile->error_return);
        die("Cannot proceed without a valid customer profile");
    }
    
    debug_print("Customer profile loaded successfully", [
        'customerProfileId' => $customer_profile->customerProfileId,
        'description' => $customer_profile->description,
        'paymentProfiles' => count($customer_profile->paymentProfiles)
    ]);
    
    // Create the debug installments object
    debug_print("Creating DebugInstallments object");
    $installments = new DebugInstallments($customer_profile, $payment_profile_id, false, true);
    
    // Test creating a subscription
    debug_print("Testing subscription creation");
    $amount = 100.00;
    $num_installments = 2;
    $start_date = date('Y-m-d');
    
    $response = $installments->createSubscription($amount, $num_installments, $start_date);
    
    debug_print("Final result", $response);
    
    // If successful, get the subscription ID
    if (strpos($response, "Success") !== false) {
        $subscription_id = $installments->getSubscriptionId();
        debug_print("Created subscription ID", $subscription_id);
    }
    
} catch (Exception $e) {
    debug_print("Exception", $e->getMessage());
}
?>
