<?php
// ini_set('display_errors', 1);
// ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/CustomerProfile.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/PaymentProfile.php';

use classes\authorize\CustomerProfile as Customer;
use classes\authorize\PaymentProfile as Payment;

$admin = $_COOKIE['admin'];
// decode admin id
require_once $_SERVER['DOCUMENT_ROOT'] . '/mobile/reg/ajax/encrypt.php';
$admin_id = encrypt_decrypt('decrypt', $admin);

// get admin info
$stmt = $MASHPIA_DB->prepare("select * from admins where admin_id = :admin");
$stmt->execute([':admin' => $admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    echo json_encode([
        'success'   => false,
        'error'     => 'Admin not found.'
    ]);
    exit;
}


$action = $_POST['action'];

switch ($action) {
    case 'get_cards':
        getCards();
        break;
    case 'delete_card':
        deleteCard($_POST['profile_id']);
        break;
    case 'update_card':
        updateCard($_POST['profile_id'], $_POST['cardInfo']);
        break;
    case 'add_card':
        addCard($_POST['data']);
        break;
    default:
        break;
}

function getCards() {
    global $admin;

    if ($admin['authorize_customer_profile_id']) {
        $customer_profile = new Customer($admin['authorize_customer_profile_id']);
        $profiles = $customer_profile->paymentProfiles;
        echo json_encode([
            'success'   => true,
            'profiles'  => $profiles
        ]);
    } else {
        echo json_encode([
            'success'   => false, 
            'profileID' => 0,
            'error'     => 'No profile found.'
        ]);
    }
}

function deleteCard($profileID) {
    global $admin;

    $payment_profile = new Payment($profileID, $admin['authorize_customer_profile_id']);
    $deleted = $payment_profile->delete();

    echo json_encode([
        'success'   => $deleted,
        'profileID' => $profileID
    ]);
}

function updateCard($profileID, $card) {
    global $admin;

    $payment_profile = new Payment($profileID, $admin['authorize_customer_profile_id']);
    $payment_profile->cardNumber = $card['cardNumber'];
    $payment_profile->expirationDate = $card['expiryDate'];

    // add bill to
    $payment_profile->billTo = [
        'firstName' => $admin['first'],
        'lastName'  => $admin['last'],
        'address'   => $admin['admin_address1'], 
        'city'      => $admin['admin_city'],
        'state'     => $admin['admin_state'],
        'zip'       => $admin['admin_postal']
    ];

    $response = $payment_profile->update(); // returns api object if there's an error
    if ($response) {
        echo json_encode([
            'success'   => false,
            'error'     => $response['messages']['message'][0]['text'],
            'response'  => $response
        ]);
    } else {
        echo json_encode([
            'success'   => true,
            'profileID' => $profileID
        ]);
    }
}

function addCard($card) {
    global $admin, $MASHPIA_DB;

    if ($admin['authorize_customer_profile_id']) {
        $result = Payment::create($card['cardNumber'], $card['expiryDate'], $card['ccv'], $admin['authorize_customer_profile_id']);
        // if result is object, then the request succeeded, if it's an array, then there was an error
        if (is_array($result)) {
            echo json_encode([
                'success'   => false,
                'error'     => $result['messages']['message'][0]['text'], 
                'response'  => $result
            ]);
        } else {
            echo json_encode([
                'success'   => true,
            ]);
        }
    } else {
        $billTo = [
            'firstName' => $admin['first'],
            'lastName'  => $admin['last'],
            'address'   => $admin['admin_address1'], 
            'city'      => $admin['admin_city'],
            'state'     => $admin['admin_state'],
            'zip'       => $admin['admin_postal']
        ];

        // create profile with card info
        $customerID = 'cth_admin_' . $admin['admin_id'];
        $desc = ($admin['title'] ? $admin['title'] . ' ' : '') . $admin['first'] . ' ' . $admin['last'];
        $payment = Payment::createBasicArray($card['cardNumber'], $card['expiryDate'], $card['ccv'], $billTo, true);
        $customer = Customer::create($customerID, $admin['admin_email'], $desc, $payment, true);
        // if customer is an object then we are good, otherwise there was an error
        if (is_object($customer)) {
            // save customerID to db
            $customerID = $customer->customerProfileId;
            $stmt = $MASHPIA_DB->prepare("
                UPDATE admins SET authorize_customer_profile_id = ? WHERE admin_id = ?
            ");
            $stmt->execute([$customerID, $admin['admin_id']]);
            echo json_encode([
                'success'   => true
            ]);
        } else {
            echo json_encode([
                'success'   => false,
                'error'     => $customer['message'], 
                'response'  => $customer
            ]);
        }
    }
}