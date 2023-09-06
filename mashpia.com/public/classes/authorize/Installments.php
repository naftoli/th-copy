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
    function createSubscription($amount, $numInstallments, $customerProfileId, $customerPaymentProfileId, $live = true) {
        // get customer payment profile id if needed
        if (!$customerPaymentProfileId) $customerPaymentProfileId = $this->getCustomerPaymentProfileId($customerProfileId);

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(Constants::GetMerchantLoginID());
        $merchantAuthentication->setTransactionKey(Constants::GetMerchantTransactionKey());

        // Set the transaction's refId
        $refId = 'ref' . time();

        // Subscription Type Info
        $subscription = new AnetAPI\ARBSubscriptionType();
        $subscription->setName("Subscription for " . $customerProfileId);

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
        $profile->setCustomerProfileId($customerProfileId);
        $profile->setCustomerPaymentProfileId($customerPaymentProfileId);

        $subscription->setProfile($profile);

        $request = new AnetAPI\ARBCreateSubscriptionRequest();
        $request->setmerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setSubscription($subscription);
        $controller = new AnetController\ARBCreateSubscriptionController($request);

        // for live use \net\authorize\api\constants\ANetEnvironment::PRODUCTION; for testing use \net\authorize\api\constants\ANetEnvironment::SANDBOX;
        if ($live) {
            $response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::PRODUCTION);
        } else {
            $response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::SANDBOX);
        }

        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok") )
        {
            echo "SUCCESS: Subscription ID : " . $response->getSubscriptionId() . "\n";
        }
        else
        {
            echo "ERROR :  Invalid response\n";
            $errorMessages = $response->getMessages()->getMessage();
            echo "Response : " . $errorMessages[0]->getCode() . "  " .$errorMessages[0]->getText() . "\n";
        }

        return $response;
    }

    function getCustomerPaymentProfileId($customerProfileId) {
        $cp = new CustomerProfile($customerProfileId);
        return $cp->paymentProfiles[0]['customerPaymentProfileId'];
    }
}