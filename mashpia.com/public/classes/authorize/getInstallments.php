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

require_once 'Installments.php';
use \classes\authorize\Installments as Installments;

$installments = [];
$info = Installments::getSubscriptions($year);
foreach ($info as $row) {
    $installment_info = [];
    $id = $row['subscription_id'];
    $subscription_amount = $row['total_amount'];
    $total_num_installments = $row['number_of_installments'];
    $installment_info['subscription_id'] = $id;
    $installment_info['subscription_amount'] = $subscription_amount;
    $installment_info['total_num_installments'] = $total_num_installments;

    $installment = new Installments();
    $response = $installment->getSubscriptionInfo($id);
    if ($response != null && $response->getMessages()->getResultCode() == 'Ok') {
        $installment_info['name'] = $response->getSubscription()->getName();
        $installment_info['description'] = $response->getSubscription()->getDescription();
        $installment_info['status'] = $response->getSubscription()->getStatus();
        $transactions = $response->getSubscription()->getArbTransactions();
        if ($transactions != null) {
            $installment_info['num_transactions'] = count($transactions);
            $paid = 0;
            $installment_info['completed'] = 0;
            $installment_info['failed'] = 0;
            foreach ($transactions as $transaction) {
                // get last word of response
                $resArr = explode(' ', $transaction->getResponse());
                $approved = $resArr[count($resArr) - 1];
                if ($approved == 'approved.') {
                    $paid += floatval($response->getSubscription()->getAmount());
                    $installment_info['completed']++;
                } else {
                    $installment_info['failed']++;
                }
            }
            $installment_info['paid'] = $paid;
            $installment_info['error'] = false;
        } else {
            $installment_info['paid'] = 0;
            $installment_info['error'] = 'No transactions found';
        }
    } else {
        $installment_info['paid'] = 0;
        $installment_info['error'] = 'No subscription found';
    }
    $installments[] = $installment_info;
}

echo json_encode($installments);