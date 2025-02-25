<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

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
        updateCard($_POST['profile_id'], $_POST['expiry_date']);
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
    global $admin;

    $result = Payment::create($card['cardNumber'], $card['expiryDate'], $card['cvv'], $admin['authorize_customer_profile_id']);
    echo json_encode([
        'success'   => $result instanceof Payment && $result['messages']['resultCode'] == 'OK',
        'error'     => $result['messages']['message'][0]['text']
    ]);
}