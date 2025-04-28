<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    die('Access denied!');
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

// Get the POST data
//$postData = json_decode(file_get_contents('php://input'), true);
//$year = isset($postData['year']) ? intval($postData['year']) : GlobalSettings::getChidonYear();

// ... rest of your existing code using $year ...
require_once 'Installments.php';
use \classes\authorize\Installments as Installments;

$installments = [];
$info = Installments::getSubscriptions($year);
foreach ($info as $key => $row) {
    $installment_info = [];
    $id = $row['subscription_id'];
    $subscription_amount = $row['total_amount'];
    $total_num_installments = $row['number_of_installments'];
    $installment_info['subscription_id'] = $id;
    $installment_info['subscription_amount'] = $subscription_amount;
    $installment_info['total_num_installments'] = $total_num_installments;

    $installment = new Installments();
    $response = $installment->getSubscriptionInfo($id);
    if (is_object($response) && $response->getMessages()->getResultCode() == 'Ok') {
        $subscription = $response->getSubscription();
        $installment_info['name'] = $subscription->getName();
        $amount = $subscription->getAmount();
        $installment_info['status'] = $subscription->getStatus();
        $transactions = $subscription->getArbTransactions();

        $installment_info['completed'] = 0;
        $installment_info['failed'] = 0;
        if (! is_null($transactions)) {
            $paid = 0;
            $installment_info['num_transactions'] = count($transactions);
            foreach ($transactions as $transaction) {
                // get last word of response
                $resArr = explode(' ', $transaction->getResponse());
                $approved = $resArr[count($resArr) - 1];
                if ($approved == 'approved.') {
                    $paid += floatval($amount);
                    $installment_info['completed']++;
                } else {
                    $installment_info['failed']++;
                }
            }
            $installment_info['paid'] = $paid;
            $installment_info['error'] = false;
        } else {
            $installment_info['num_transactions'] = 0;
            $installment_info['paid'] = 0;
            $installment_info['error'] = 'No transactions found';
        }
    } else if (is_string($response)) {
        $installment_info['paid'] = 0;
        $installment_info['error'] = $response;
    } else {
        $installment_info['paid'] = 0;
        $installment_info['error'] = 'No subscription found';
    }
//    echo '<pre>';
//    print_r($installment_info);
//    echo '</pre>';
    $installments[] = $installment_info;
}
echo json_encode($installments);