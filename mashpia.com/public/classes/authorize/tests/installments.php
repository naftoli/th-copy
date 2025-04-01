<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require '../Installments.php';
use \classes\authorize\Installments as Installments;

//$installments = new Installments(1601946728);
//$response = $installments->createSubscription(50, 2, '2025-05-01');
//echo "Creating Subscription: ";
//echo "<pre>"; print_r($response); echo "</pre>";

// find out info about an installment
$installment = new Installments();
$response = $installment->getSubscriptionInfo(66663982);

if ($response != null) {
    if ($response->getMessages()->getResultCode() == 'Ok') {
        // Displaying the details
        echo 'Subscription Name: ' . $response->getSubscription()->getName() . "<br />";
        echo 'Subscription amount: ' . $response->getSubscription()->getAmount() . "<br />";
        echo 'Subscription status: ' . $response->getSubscription()->getStatus() . "<br />";
        echo 'Subscription Description: ' . $response->getSubscription()->getProfile()->getDescription() . "<br />";
        echo 'Customer Profile ID: ' . $response->getSubscription()->getProfile()->getCustomerProfileId() . "<br />";
        echo 'Customer payment Profile ID: ' . $response->getSubscription()->getProfile()->getPaymentProfile()->getCustomerPaymentProfileId() . "<br />";
        $transactions = $response->getSubscription()->getArbTransactions();
        if ($transactions != null) {
            $paid = 0;
            foreach ($transactions as $transaction) {
                echo 'Transaction ID : ' . $transaction->getTransId() . ' -- ' . $transaction->getResponse() . ' -- Pay Number : ' . $transaction->getPayNum() . "<br />";
                // get last word of response
                $resArr = explode(" ", $transaction->getResponse());
                $approved = $resArr[count($resArr) - 1];
                if ($approved == "approved.") {
                    $paid += floatval($response->getSubscription()->getAmount());
                }
            }
            echo 'Total Amount Paid: ' . $paid . "<br />";
        }
    } else {
        // Error
        echo "ERROR :  Invalid response<br />";
        $errorMessages = $response->getMessages()->getMessage();
        echo 'Response : ' . $errorMessages[0]->getCode() . '  ' . $errorMessages[0]->getText() . "<br />";
    }
} else {
    // Failed to get response
    echo 'Null Response Error';
}
