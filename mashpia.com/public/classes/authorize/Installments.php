<?php
namespace classes\authorize;

// load the constants
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/authorize_constants.php';
use includes\authorize\AuthorizeConstants as Constants;

require $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php';

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

    public function __construct($customerProfile, $payment_profile_id, $live = true) {
        // for live use \net\authorize\api\constants\ANetEnvironment::PRODUCTION;
        // for testing use \net\authorize\api\constants\ANetEnvironment::SANDBOX;
        if ($live) $this->endpoint = \net\authorize\api\constants\ANetEnvironment::PRODUCTION;
        else $this->endpoint = \net\authorize\api\constants\ANetEnvironment::SANDBOX;

        $this->cp = $customerProfile;
        $this->payment_profile_id = $payment_profile_id;
        if (! $this->updateBillingInfo()) {
            throw new \Exception("Error updating billing info");
        }
    }

    public function setAuth() {
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(Constants::GetMerchantLoginID());
        $merchantAuthentication->setTransactionKey(Constants::GetMerchantTransactionKey());
        return $merchantAuthentication;
    }

    private function updateBillingInfo() {
        $merchantAuthentication = $this->setAuth();
        $refId = 'ref' . time();

        $name = explode(" ", $this->cp->description);
        $last_name = array_pop($name);
        $first_name = implode(" ", $name);
        $billto = new AnetAPI\CustomerAddressType();
        $billto->setFirstName($first_name);
        $billto->setLastName($last_name);

        // get payment profile
        foreach ($this->cp->paymentProfiles as $profile) {
            if ($profile['customerPaymentProfileId'] == $this->payment_profile_id) break;
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
        $response = $controller->executeWithApiResponse($this->endpoint);

        $res = $this->parseResponse($response);
        if (strpos($res, "Success") !== false) return true;
        else return false;
    }

    public function createSubscription($amount, $numInstallments) {
        $this->total_amount = $amount;
        $this->number_of_installments = $numInstallments;
        $this->installment_amount = round(floatval($amount / $numInstallments), 2);
        $this->start_date = date('Y-m-d', strtotime("+1 month"));

        $merchantAuthentication = $this->setAuth();
        // Set the transaction's refId
        $refId = 'ref' . time();

        // Subscription Type Info
        $subscription = new AnetAPI\ARBSubscriptionType();
        $subscription->setName("Subscription for " . $this->cp->description);

        $interval = new AnetAPI\PaymentScheduleType\IntervalAType();
        $interval->setLength($numInstallments);
        $interval->setUnit("months");

        $paymentSchedule = new AnetAPI\PaymentScheduleType();
        $paymentSchedule->setInterval($interval);
        $paymentSchedule->setStartDate(new \DateTime(date('Y-m-d', strtotime("+1 month"))));
        $paymentSchedule->setTotalOccurrences($numInstallments);

        $subscription->setPaymentSchedule($paymentSchedule);
        $subscription->setAmount($this->installment_amount);

        $profile = new AnetAPI\CustomerProfileIdType();
        $profile->setCustomerProfileId($this->cp->customerProfileId);
        $profile->setCustomerPaymentProfileId($this->payment_profile_id);
        $subscription->setProfile($profile);

        $request = new AnetAPI\ARBCreateSubscriptionRequest();
        $request->setmerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setSubscription($subscription);
        $controller = new AnetController\ARBCreateSubscriptionController($request);
        $response = $controller->executeWithApiResponse($this->endpoint);

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

    public function saveToDb($dbHandle, $admin_id) {
        $stmt = $dbHandle->prepare(
            "INSERT INTO `subscriptions` (`admin_id`, `subscription_id`, `installment_amount`, `number_of_installments`, `total_amount`, `start_date`) 
                VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $admin_id, $this->subscription_id, $this->installment_amount, $this->number_of_installments, $this->total_amount, $this->start_date
        ]);
    }
}