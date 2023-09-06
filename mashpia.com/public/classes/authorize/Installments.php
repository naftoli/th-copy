<?php
namespace classes\authorize;

// load the constants
require_once( dirname(__FILE__) . "/../../../includes/authorize_constants.php" );
use includes\authorize\AuthorizeConstants as Constants;

require $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php';

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

require_once 'CustomerProfile.php';

class Installments
{
    private $cp;
    private $customerPaymentProfileId;
    private $endpoint;

    public function __construct($customerProfileId, $live = true) {
        // for live use \net\authorize\api\constants\ANetEnvironment::PRODUCTION;
        // for testing use \net\authorize\api\constants\ANetEnvironment::SANDBOX;
        if ($live) $this->endpoint = \net\authorize\api\constants\ANetEnvironment::PRODUCTION;
        else $this->endpoint = \net\authorize\api\constants\ANetEnvironment::SANDBOX;

        $this->cp = new CustomerProfile($customerProfileId);
//        echo "<pre>"; print_r($this->cp); echo "</pre>";
        // set customer payment profile id
        $lastIndex = count($this->cp->paymentProfiles) - 1;
        $this->customerPaymentProfileId = $this->cp->paymentProfiles[$lastIndex]['customerPaymentProfileId'];
        $this->updateBillingInfo();
    }

    public function setAuth() {
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(Constants::GetMerchantLoginID());
        $merchantAuthentication->setTransactionKey(Constants::GetMerchantTransactionKey());
        return $merchantAuthentication;
    }

    public function updateBillingInfo() {
        $merchantAuthentication = $this->setAuth();

        foreach ($this->cp->paymentProfiles as $profile) {
            $refId = 'ref' . time();

            $name = explode(" ", $this->cp->description);
            $billto = new AnetAPI\CustomerAddressType();
            $billto->setFirstName($name[0]);
            $billto->setLastName($name[1]);

            $creditCard = new AnetAPI\CreditCardType();
            $creditCard->setCardNumber($profile['payment']['creditCard']['cardNumber']);
            $creditCard->setExpirationDate($profile['payment']['creditCard']['expirationDate']);

            $paymentCreditCard = new AnetAPI\PaymentType();
            $paymentCreditCard->setCreditCard($creditCard);

            $paymentprofile = new AnetAPI\CustomerPaymentProfileExType();
            $paymentprofile->setBillTo($billto);
            $paymentprofile->setDefaultPaymentProfile(true);
            $paymentprofile->setCustomerPaymentProfileId($this->customerPaymentProfileId);
            $paymentprofile->setPayment($paymentCreditCard);

            // Submit a UpdatePaymentProfileRequest
            $request = new AnetAPI\UpdateCustomerPaymentProfileRequest();
            $request->setMerchantAuthentication($merchantAuthentication);
            $request->setCustomerProfileId($this->cp->customerProfileId);
            $request->setPaymentProfile($paymentprofile);
            $request->setRefId($refId);

            $controller = new AnetController\UpdateCustomerPaymentProfileController($request);
            $response = $controller->executeWithApiResponse($this->endpoint);

//            return $this->parseResponse($response);
        }
    }

    public function createSubscription($amount, $numInstallments, $customerPaymentProfileId) {
        if ($customerPaymentProfileId) $this->customerPaymentProfileId = $customerPaymentProfileId;

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
        $subscription->setAmount(round(floatval($amount / $numInstallments), 2));

        $profile = new AnetAPI\CustomerProfileIdType();
        $profile->setCustomerProfileId($this->cp->customerProfileId);
        $profile->setCustomerPaymentProfileId($this->customerPaymentProfileId);
        $subscription->setProfile($profile);

        $request = new AnetAPI\ARBCreateSubscriptionRequest();
        $request->setmerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setSubscription($subscription);
        $controller = new AnetController\ARBCreateSubscriptionController($request);
        $response = $controller->executeWithApiResponse($this->endpoint);

        return $this->parseResponse($response);
    }

    public function parseResponse($response) {
        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok") ) {
            $Message = $response->getMessages()->getMessage();
//            echo "SUCCESS: " . $Message[0]->getCode() . "  " .$Message[0]->getText() . "<br />";
            return true;
        } else if ($response != null) {
            $errorMessages = $response->getMessages()->getMessage();
//            echo "ERROR:  " . $errorMessages[0]->getCode() . "  " .$errorMessages[0]->getText() . "<br />";
            return false;
        }
//        return $response;
    }
}