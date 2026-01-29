<?php
namespace classes\authorize;

// We'll skip autoloading here since our test scripts will handle it
// This prevents duplicate autoloading and path issues

// If this file is included directly (not through a test script), we'll need to load constants
if (!class_exists('includes\\authorize\\AuthorizeConstants')) {
    // Check if running from command line or web server
    if (isset($_SERVER['DOCUMENT_ROOT']) && !empty($_SERVER['DOCUMENT_ROOT'])) {
        // Web server execution
        require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/authorize_constants.php';
    } else {
        // Command line execution
        $mashpia_root = dirname(dirname(dirname(dirname(__DIR__))));
        require_once $mashpia_root . '/includes/authorize_constants.php';
    }
}
use includes\authorize\AuthorizeConstants as Constants;

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class Installments
{
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
        $this->sandbox = $sandbox;
        // Store the environment constant directly
        if ($sandbox) {
            $this->endpoint = \net\authorize\api\constants\ANetEnvironment::SANDBOX;
        } else {
            $this->endpoint = \net\authorize\api\constants\ANetEnvironment::PRODUCTION;
        }

        if ($customerProfile instanceof CustomerProfile) {
            $this->cp = $customerProfile;
            $this->payment_profile_id = $payment_profile_id;
            if ($updateBilling && !$this->updateBillingInfo()) {
                throw new \Exception('Error updating billing info');
            }
        }
    }

    public function setAuth() {
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(Constants::GetMerchantLoginID($this->sandbox));
        $merchantAuthentication->setTransactionKey(Constants::GetMerchantTransactionKey($this->sandbox));
        return $merchantAuthentication;
    }

    public function getEndpoint() {
        return $this->endpoint;
    }

    private function updateBillingInfo() {
        $merchantAuthentication = $this->setAuth();
        $refId = 'ref' . time();

        // Extract first and last name from description or use defaults
        if (!empty($this->cp->description)) {
            $name = explode(" ", $this->cp->description);
            if (count($name) > 1) {
                $last_name = array_pop($name);
                $first_name = implode(" ", $name);
            } else {
                // If only one word, use it as first name and 'Customer' as last name
                $first_name = $this->cp->description;
                $last_name = 'Customer';
            }
        } else {
            // Default names if description is empty
            $first_name = 'Default';
            $last_name = 'Customer';
        }

        $billto = new AnetAPI\CustomerAddressType();
        $billto->setFirstName($first_name);
        $billto->setLastName($last_name);

        // get payment profile
        if (! count($this->cp->paymentProfiles)) {
            throw new \Exception("No payment profiles found");
        } else {
            foreach ($this->cp->paymentProfiles as $profile) {
                if ($profile['customerPaymentProfileId'] == $this->payment_profile_id) break;
            }
        }

        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($profile['payment']['creditCard']['cardNumber']);
        $creditCard->setExpirationDate($profile['payment']['creditCard']['expirationDate']);

        $paymentCreditCard = new AnetAPI\PaymentType();
        $paymentCreditCard->setCreditCard($creditCard);

        $paymentprofile = new AnetAPI\CustomerPaymentProfileExType();
        $paymentprofile->setBillTo($billto);
        $paymentprofile->setCustomerPaymentProfileId($this->payment_profile_id);
        $paymentprofile->setPayment($paymentCreditCard);

        // Submit a UpdatePaymentProfileRequest
        $request = new AnetAPI\UpdateCustomerPaymentProfileRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setCustomerProfileId($this->cp->customerProfileId);
        $request->setPaymentProfile($paymentprofile);
        $request->setRefId($refId);

        $controller = new AnetController\UpdateCustomerPaymentProfileController($request);
        
        // Use the SDK constants directly instead of the stored endpoint
        if ($this->sandbox) {
            $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
        } else {
            $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
        }

        $res = $this->parseResponse($response);
        if (strpos($res, "Success") !== false) return true;
        else return false;
    }

    public function createSubscription($amount, $numInstallments, $start_date = null, $admin_id = 0) {
        $this->total_amount = $amount;
        $this->number_of_installments = $numInstallments;
        $this->installment_amount = round(floatval($amount / $numInstallments), 2);
        $this->start_date = $start_date ?? date('Y-m-d', strtotime("+1 month"));

        $merchantAuthentication = $this->setAuth();
        // Set the transaction's refId
        $refId = 'ref' . time();

        // Subscription Type Info
        $subscription = new AnetAPI\ARBSubscriptionType();
        $subscription->setName("Subscription for " . $this->cp->description);

        $interval = new AnetAPI\PaymentScheduleType\IntervalAType();
        $interval->setLength(1);
        $interval->setUnit("months");

        $paymentSchedule = new AnetAPI\PaymentScheduleType();
        $paymentSchedule->setInterval($interval);
        $paymentSchedule->setStartDate(new \DateTime($this->start_date));
        $paymentSchedule->setTotalOccurrences($numInstallments);

        $subscription->setPaymentSchedule($paymentSchedule);
        $subscription->setAmount($this->installment_amount);

        if ($admin_id > 0) {
            $order = new AnetAPI\OrderType();
            $desc = "F" . $admin_id . ":RRFAM-" . $this->installment_amount;
            $order->setDescription($desc); 
            $subscription->setOrder($order); 
        }

        // When using a customer profile, we don't need to include billing information
        // Set up customer profile - this contains the billing information already
        $profile = new AnetAPI\CustomerProfileIdType();
        $profile->setCustomerProfileId($this->cp->customerProfileId);
        $profile->setCustomerPaymentProfileId($this->payment_profile_id);
        $subscription->setProfile($profile);

        $request = new AnetAPI\ARBCreateSubscriptionRequest();
        $request->setmerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setSubscription($subscription);
        $controller = new AnetController\ARBCreateSubscriptionController($request);
        
        // Use the SDK constants directly instead of the stored endpoint
        if ($this->sandbox) {
            $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
        } else {
            $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
        }

        $res = $this->parseResponse($response);
        if (strpos($res, "Success") !== false) $this->subscription_id = $response->getSubscriptionId();
        return $res;
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

    public function saveToDb($dbHandle, $admin_id, $year) {
        $stmt = $dbHandle->prepare(
            "INSERT INTO `th_chidon_installments` (`admin_id`, `subscription_id`, `installment_amount`, `number_of_installments`, `total_amount`, `start_date`, `year`) 
                VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $res = $stmt->execute([
            $admin_id, $this->subscription_id, $this->installment_amount, $this->number_of_installments, $this->total_amount, $this->start_date, $year
        ]);
        // if (!$res) echo $stmt->debugDumpParams();
        return $res;
    }

    public function getSubscriptionId() {
        return $this->subscription_id;
    }

    public function removeFromDb($dbHandle) {
        $stmt = $dbHandle->prepare("DELETE FROM `th_chidon_installments` WHERE `subscription_id` = ?");
        $res = $stmt->execute([$this->subscription_id]);
        if (!$res) echo $stmt->debugDumpParams();
        return $res;
    }

    public function cancelSubscription() {
        $merchantAuthentication = $this->setAuth();
        // Set the transaction's refId
        $refId = 'ref' . time();

        $request = new AnetAPI\ARBCancelSubscriptionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setSubscriptionId($this->subscription_id);

        $controller = new AnetController\ARBCancelSubscriptionController($request);
        
        // Use the SDK constants directly instead of the stored endpoint
        if ($this->sandbox) {
            $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
        } else {
            $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
        }

        return $this->parseResponse($response);
    }

    public function getName() {
        // Use safer defaults instead of trying to query the database
        // This avoids potential issues with database connections
        if (!empty($this->cp->description)) {
            $name = explode(" ", $this->cp->description);
            if (count($name) > 1) {
                $last_name = array_pop($name);
                $first_name = implode(" ", $name);
                return [$first_name, $last_name];
            } else {
                return [$this->cp->description, 'Customer'];
            }
        }
        return ['Default', 'Customer'];
    }

    // get info about subscription from authorize
    public function getSubscriptionInfo($id) {
        $merchantAuthentication = $this->setAuth();
        $refId = 'ref' . time();

        $request = new AnetAPI\ARBGetSubscriptionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setSubscriptionId($id);
        $request->setIncludeTransactions(true);
        $controller = new AnetController\ARBGetSubscriptionController($request);

        // Add timeout configuration (if supported by the SDK)
        try {
            // Use the SDK constants directly instead of the stored endpoint
            if ($this->sandbox) {
                $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX, 10);
            } else {
                $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION, 10);
            }
            return $response;
        } catch (\Exception $e) {
            // error
            return "API Call failed for subscription ID $id: " . $e->getMessage();
        }
    }

    public static function getSubscriptions($year) {
        $sql = "select * from th_chidon_installments where year = " . $year;
        $result = mysql_query($sql);
        $rows = [];
        while ($row = mysql_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }
}