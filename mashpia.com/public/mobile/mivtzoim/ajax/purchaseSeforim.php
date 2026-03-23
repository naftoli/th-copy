<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mobile/reg/ajax/encrypt.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mivtzoim_purchases/classes/MivtzoimPurchases.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

//*************** LOAD AUTHORIZE FUNCTIONS *********************/
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/CustomerProfile.php';
use classes\authorize\CustomerProfile;

// ****************** PAYMENT FUNCTIONS ***************************/
function startPayment() {
    global $admin_id;
    $sql = "INSERT INTO payment_processing (admin_id) VALUES ($admin_id)";
    mysql_query($sql);
}

function endPayment() {
    global $admin_id;
    $sql = "DELETE FROM payment_processing WHERE admin_id = $admin_id";
    mysql_query($sql);
}

function paymentInProgress() {
    global $admin_id;
    $sql = "SELECT * FROM payment_processing WHERE admin_id = $admin_id";
    $result = mysql_query($sql);
    return mysql_num_rows($result) > 0;
}
// ************** END PAYMENT FUNCTIONS ***************************/


// **************** FUNCTIONS **************** //
function purchaseItems() {
    global $total, $admin_id, $cc_info, $m, $purchase_id;

    $amount = $total; // the authorize script expects a variable called amount
    $address = "";
    $state = "";

//    $description = "Yahadus Book Sale (end of yr) - $$amount, Admin ID: " . $admin_id;
    $description = "5 Teves Book Sale - $$amount, Admin ID: " . $admin_id;

    if ($total > 0) {
        // figure out if we are charging a credit card on file or a new one
        if (intval($cc_info->on_file) == 1) {
            $customer_id = getCustomerID($admin_id);
            if (!$customer_id || empty($customer_id)) {
                $m->deletePurchase($purchase_id);
                endPayment();
                echo json_encode([
                    'success' => false,
                    'msg' => 'You do not have a valid credit card on file, please enter a new credit card and try again.'
                ]);
                return false;
            }

            // charge credit card on file
            $cp = new CustomerProfile( $customer_id );
            $response = $cp->chargeCard( $total, $cc_info->card_id, null, null, $description );
            if (! is_array($response)) {
                $m->deletePurchase($purchase_id);
                endPayment();
                echo json_encode([
                    'success'   => false,
                    'error'     => $response
                ]);
                return false;
            }

            $msg = $response['transactionResponse']['messages'][0]['description'] . ':' .
                $response['transactionResponse']['authCode'] . ':' . $response['transactionResponse']['transId'] . ':' .
                $total;
            return $msg;
        } else {
            // get the credit card info and charge it
            $card_num = $cc_info->num;
            $exp_date = $cc_info->exp;
            $cvv = $cc_info->cvv;
            $name = $cc_info->name;
            $nameArr = explode(' ', $name);
            $first_name = $nameArr[0];
            if (count($nameArr) > 2) {
                for ($i = 1; $i < count($nameArr) - 1; $i++) {
                    $first_name .= ' ' . $nameArr[$i];
                }
            }
            $last_name = $nameArr[count($nameArr) - 1];
            $zip = $cc_info->zip;

            chdir('../../../');
            require_once 'authorize.php';

            if ($response_array[0] == 1) { // success
                $strResponse = $response_array[3] . ':' .
                    $response_array[4] . ':' .
                    $response_array[6] . ':' .
                    $response_array[9];
                return $strResponse;
            } else {
                $m->deletePurchase($purchase_id);
                endPayment();
                echo json_encode([
                    'success'   => false,
                    'error'     => $response_array[3]
                ]);
                return false;
            }
        }       
    }
}

function getCustomerID($admin_id) {
    $sql = "select authorize_customer_profile_id from admins where admin_id = " . $admin_id;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    return $row['authorize_customer_profile_id'];
}

function saveToDb($info) {
    global $list, $m;

    $details = [];
    foreach ($list as $item) {
        $user_id = $item->user_id;
        $item_id = $item->item_id;
        $qty = $item->qty;
        $details[$user_id][$item_id] = $qty;
    }

    return $m->createPurchase( $info, $details );
}
// **************** END FUNCTIONS **************** //

$info = json_decode($_POST['info']);
$list = $info->purchases;
$total = $info->total;
$cc_info = $info->cc;
$type = $info->type;
$admin_id = encrypt_decrypt('decrypt', $info->admin);

// Check if there's already a payment in progress
if (paymentInProgress()) {
    // There's already a payment in progress
    echo json_encode([
        'success' => false,
        'error' => 'Your payment is already being processed. Please wait for it to complete.'
    ]);
    exit;
}

// Insert a record to indicate a payment is in progress
startPayment();

$m = new MivtzoimPurchases();

// first save to db
$purchaseInfo = [
    'year'      =>  $year,
    'admin'     =>  $admin_id,
    'amount'    =>  $total
];
$purchase_id = saveToDb($purchaseInfo);

if ($purchase_id > 0) {
    // charge the card
    $res = purchaseItems();
//    $res = false;
    if ($res) {
        // update db with authorization
        $m->updatePurchase($purchase_id, $res);
        endPayment();
        // send email
        $m->sendEmail($purchaseInfo, $list, $cc_info, $info->yom_tov);
        echo json_encode([
            'success'   => true
        ]);
//    } else {
//        $m->deletePurchase($purchase_id);
//        endPayment();
//        echo json_encode([
//            'success'   => false,
//            'error'     => 'There was an error charging your card. You have not been charged. Please try again.'
//        ]);
    }
} else {
    endPayment();
    echo json_encode([
        'success'   => false,
        'error'     => 'There was an error saving your purchase. You have not been charged. Please try again.'
    ]);
}