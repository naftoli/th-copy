<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mobile/reg/ajax/encrypt.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mivtzoim_purchases/classes/MivtzoimPurchases.php';

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

function getCustomerID($admin_id) {
    $sql = "select authorize_customer_profile_id from admins where admin_id = " . $admin_id;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    return $row['authorize_customer_profile_id'];
}

$info = $_POST['info'];
$admin_id = encrypt_decrypt('decrypt', $info['admin']);

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

$year = $info['year'];
$amount = (float)$info['amount'];
$card_num = $info['cc']['num'];
$exp_date = $info['cc']['exp'];
$cvv = $info['cc']['cvv'];
$first_name = $info['cc']['first'];
$last_name = $info['cc']['last'];
$zip = $info['zip'];
$address = "";
$state = "";
$on_file = $info['cc']['on_file'];

if ( $amount > 0 ) {
    // first save to db
    // create arrays to use for purchasing function
    $purchase = [
        'year'  =>  $year,
        'admin' =>  $admin_id,
        'amount' =>  $amount,
    ];

    // 132 refers to neshek item id
    $details = [];
    foreach ( $info['neshek'] as $detail ) {
        $id = $detail['user'];
        $qty = $detail['qty'];
        $details[$id][132] = $qty;
    }

    // first save to db
    $p = new MivtzoimPurchases();
    $purchase_id = $p->createPurchase( $purchase, $details );

    if ($purchase_id) {
        // now process cc
        $description = "Neshek " . $year . " purchase - Admin ID: " . $admin_id;

        // figure out if we are charging a credit card on file or a new one
        if (intval($on_file) == 1) {
            $customer_id = getCustomerID($admin_id);
            $card_id = isset($info['cc']['card_id']) ? $info['cc']['card_id'] : 0;
            if (!$customer_id || empty($customer_id) || !$card_id) {
                $p->deletePurchase($purchase_id);
                endPayment();
                echo json_encode([
                    'success' => false,
                    'msg' => 'You do not have a valid credit card on file, please enter a new credit card and try again.'
                ]);
            } else {
                // charge credit card on file
                try {
                    $cp = new CustomerProfile($customer_id);
                    $response = $cp->chargeCard($amount, $card_id, null, null, $description);
                    if (!is_array($response) || in_array($response['transactionResponse']['authCode'], [2, 3])) {
                        // delete from db
                        $p->deletePurchase($purchase_id);
                        endPayment();
                        echo json_encode([
                            'success' => false,
                            'error' => is_string($response) ? $response : $response['transactionResponse']['errors'][0]['errorText']
                        ]);
                    } else {
                        $msg = $response['transactionResponse']['messages'][0]['description'] . ':' .
                            $response['transactionResponse']['authCode'] . ':' . $response['transactionResponse']['transId'] . ':' .
                            $amount;
                        $p->updatePurchase($purchase_id, $msg);
                        endPayment();
                        echo json_encode([
                            'success' => true
                        ]);
                    }
                } catch (Exception $e) {
                    $p->deletePurchase($purchase_id);
                    endPayment();
                    echo json_encode([
                        'success' => false,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } else {
            try {
                chdir('../../../');
                require_once 'authorize.php';
                chdir('mobile/mivtzoim/ajax/');

                if (in_array($response_array[0], [1, 4])) { // success; 1 = approved; 4 = held for review
                    $strResponse = $response_array[3] . ':' .
                        $response_array[4] . ':' .
                        $response_array[6] . ':' .
                        $response_array[9];

                    $p->updatePurchase($purchase_id, $strResponse);
                    endPayment();
                    echo json_encode([
                        'success' => true
                    ]);
                } else {
                    // delete from db
                    $p->deletePurchase($purchase_id);
                    endPayment();
                    echo json_encode([
                        'success' => false,
                        'error' => $response_array[3]
                    ]);
                }
            } catch (Exception $e) {
                $p->deletePurchase($purchase_id);
                endPayment();
                echo json_encode([
                    'success'   => false, 
                    'error'     => 'There was an error processing your purchase.'
                ]);
            }
        }
    } else {
        endPayment();
        echo json_encode([
            'success'   => false,
            'error'     => 'There was an error saving your purchase. You have not been charged. Please try again.'
        ]);
    }
} else {
    endPayment();
    echo json_encode([
        'success'   => false,
        'error'     => "You have not selected anything to purchase."
    ]);
}