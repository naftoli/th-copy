<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mobile/reg/ajax/encrypt.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mivtzoim_purchases/classes/MivtzoimPurchases.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();

// **************** FUNCTIONS **************** //
function purchaseItems() {
    global $total, $admin_id, $year, $cc_info;

    $amount = $total; // the authorize script expects a variable called amount
    $card_num = $cc_info->num;
    $exp_date = $cc_info->exp;
    $cvv = $cc_info->cvv;
    $first_name = $cc_info->first;
    $last_name = $cc_info->last;
    $zip = $cc_info->zip;
    $address = "";
    $state = "";

    $description = "Hei Teves purchase ($year) - Admin ID: " . $admin_id;

    if ($total > 0) {
        chdir('../../../');
        require_once 'authorize.php';
        chdir('/mobile/reg/ajax/');

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

function saveToDb($info) {
    global $list, $response_array;

    $details = [];
    foreach ($list as $item) {
        $user_id = $item->user_id;
        $item_id = $item->item_id;
        $qty = $item->qty;
        $details[$user_id][$item_id] = $qty;
    }

    $m = new MivtzoimPurchases();
    if ( $m->createPurchase( $info, $details ) ) {
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
}
// **************** END FUNCTIONS **************** //

$info = json_decode($_POST['info']);
$list = $info->purchases;
$total = $info->total;
$cc_info = $info->cc;
$admin_id = encrypt_decrypt('decrypt', $info->admin);
$response_array = [];

if ($res = purchaseItems()) {
    $purchaseInfo = [
        'year'      =>  $year,
        'admin'     =>  $admin_id,
        'amount'    =>  $total,
        'auth'      =>  $res
    ];
    saveToDb($purchaseInfo);
}