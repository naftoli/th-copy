<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', 1);

require_once '../../../api/header/header.php';
require_once '../../../class.globalSettings.php';
require_once '../../../mivtzoim_purchases/classes/MivtzoimPurchases.php';
$year = GlobalSettings::getRegistrationYear();

$info = $_POST['info'];

require 'encrypt.php';
$admin_id = encrypt_decrypt('decrypt', $info['admin']);

$amount = (float)$info['amount'];
$card_num = $info['cc']['num'];
$exp_date = $info['cc']['exp'];
$first_name = $info['cc']['first'];
$last_name = $info['cc']['last'];
$zip = $info['zip'];
$cvv = $info['cvv'];
$address = $info['address'];
$state = $info['state'];
$item_id = $info['item_id'] ?? 1;

$description = "000:00 #lulav " . $amount;

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

function saveToDb($purchaseInfo) {
    global $m, $info, $item_id;

    $details = [];
    $qty = 1;
    foreach ($info['users'] as $user_id) {
        $details[$user_id][$item_id] = $qty;
    }

    return $m->createPurchase( $purchaseInfo, $details );
}

function updateTransactions($strResponse) {
    global $MASHPIA_DB, $admin_id, $amount, $info;

    $description = "Mivtza Lulav " . $year . " - Admin ID: " . $admin_id . "; Users: " . implode(',', $info['users']);
    $qry = $MASHPIA_DB->prepare(
        "INSERT INTO transactions 
        SET trans_date = now(),
        admin_id = :admin, 
        description = :desc,
        amount = :amount,
        response = :response"
    );
    $qry->execute([ 
        ':admin'    => $admin_id, 
        ':desc'     => $description, 
        ':amount'   => $amount, 
        ':response' => $strResponse
    ]);
}

if ( $amount > 0 ) {
    // Guard: ensure this admin has no other payment in progress
    if (paymentInProgress()) {
        echo json_encode([
            'success' => false,
            'error' => 'Your payment is already being processed. Please wait for it to complete.'
        ]);
        exit;
    }

    // Mark payment as in progress
    startPayment();

    $m = new MivtzoimPurchases();

    // first save to db
    $purchaseInfo = [
        'year'      =>  $year,
        'admin'     =>  $admin_id,
        'amount'    =>  $amount
    ];
    $purchase_id = saveToDb($purchaseInfo);

    if ($purchase_id > 0) {
        // charge the card
        chdir('../../../');
        require_once 'authorize.php';
        chdir('mobile/reg/ajax/');

        if ($response_array[0] == 1) { // success
            $strResponse =  $response_array[3] . ':' . 
                            $response_array[4] . ':' . 
                            $response_array[6] . ':' . 
                            $response_array[9];
            // update db with authorization
            $m->updatePurchase($purchase_id, $strResponse);
            endPayment();
            // send email
            // $m->sendEmail($purchaseInfo, $list, $cc_info, $info->yom_tov);
            updateTransactions($strResponse);
            echo json_encode([
                'success'   => true
            ]);
        } else {
           $m->deletePurchase($purchase_id);
           endPayment();
           echo json_encode([
               'success'   => false,
               'error'     => $response_array[3]
           ]);
        }
    } else {
        endPayment();
        echo json_encode([
            'success'   => false,
            'error'     => 'There was an error saving your purchase. You have not been charged. Please try again.'
        ]);
    }
} else {
    echo json_encode([
        'success'   => false,
        'error'     => "You have not selected anything to purchase."
    ]);
}
