<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mobile/reg/ajax/encrypt.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mivtzoim_purchases/classes/MivtzoimPurchases.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();

//*************** LOAD AUTHORIZE FUNCTIONS *********************/
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/CustomerProfile.php';
use classes\authorize\CustomerProfile;

// **************** FUNCTIONS **************** //
function purchaseItems() {
    global $total, $admin_id, $cc_info;

    $amount = $total; // the authorize script expects a variable called amount
    $address = "";
    $state = "";

    $description = "Yahadus - $$amount, Admin ID: " . $admin_id;

    if ($total > 0) {
        // figure out if we are charging a credit card on file or a new one
        if (intval($cc_info->on_file) == 1) {
            $customer_id = getCustomerID($admin_id);
            if (!$customer_id || empty($customer_id)) {
                echo json_encode([
                    'success' => false,
                    'msg' => 'You do not have a valid credit card on file, please enter a new credit card and try again.'
                ]);
                exit;
            }

            // charge credit card on file
            $cp = new CustomerProfile( $customer_id );
            $response = $cp->chargeCard( $total, $cc_info->profile_id, null, null, $description );
            if (! is_array($response)) {
                echo json_encode([
                    'success'   => false,
                    'error'     => $response
                ]);
                exit;
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
                echo json_encode([
                    'success'   => false,
                    'error'     => $response_array[3]
                ]);
                exit;
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
    global $list, $cc_info;

    $details = [];
    foreach ($list as $item) {
        $user_id = $item->user_id;
        $item_id = $item->item_id;
        $qty = $item->qty;
        $details[$user_id][$item_id] = $qty;
    }

    $m = new MivtzoimPurchases();
    if ( $m->createPurchase( $info, $details ) ) {
        // send email
        $m->sendEmail($info, $details, $cc_info);
        return true;
    }
    else return false;
}
// **************** END FUNCTIONS **************** //

$info = json_decode($_POST['info']);
$list = $info->purchases;
$total = $info->total;
$cc_info = $info->cc;
$type = $info->type;
$admin_id = encrypt_decrypt('decrypt', $info->admin);
$response_array = [];

$result = false;
if ($res = purchaseItems()) {
    $purchaseInfo = [
        'year'      =>  $year,
        'admin'     =>  $admin_id,
        'amount'    =>  $total,
        'auth'      =>  $res
    ];
    $result = saveToDb($purchaseInfo);
}

if ($result) {
    echo json_encode([
        'success'   => true
    ]);
} else {
    echo json_encode([
        'success'   =>  false,
        'error'     =>  'Your credit card was charged, however, there was an error saving your purchase. Please contact HQ (718-907-8884)
                                and keep the following authorization code as proof of purchase: ' . $response_array[6]
    ]);
}