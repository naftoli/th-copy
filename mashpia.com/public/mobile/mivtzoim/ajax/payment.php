<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mobile/reg/ajax/encrypt.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mivtzoim_purchases/classes/MivtzoimPurchases.php';

$info = $_POST['info'];
$admin_id = encrypt_decrypt('decrypt', $info['admin']);

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

if ( $amount > 0 ) {
    // first save to db
    // create arrays to use for purchasing function
    $purchase = [
        'year'  =>  $year, 
        'admin' =>  $admin_id, 
        'amount' =>  $amount, 
    ];

    // 2 and 3 refer to mivtzoim item id
    $details = [];
    foreach ( $info['menorah'] as $detail ) {
        $id = $detail['user'];
        $qty = $detail['qty'];
        $details[$id][2] = $qty;
    }
    foreach ( $info['brochure'] as $detail ) {
        $id = $detail['user'];
        $qty = $detail['qty'];
        $details[$id][3] = $qty;
    }

    $p = new MivtzoimPurchases();
    $purchase_id = $p->createPurchase( $purchase, $details );

    // now process cc
    $description = "Mivtza Chanuka " . $year . " purchase - Admin ID: " . $admin_id;
    
    chdir('../../../');
    require_once 'authorize.php';
    chdir('mobile/reg/ajax/');

    if ($response_array[0] == 1) { // success
        $strResponse =  $response_array[3] . ':' . 
                        $response_array[4] . ':' . 
                        $response_array[6] . ':' . 
                        $response_array[9];	

        $p->updatePurchase($purchase_id, $strResponse);
        echo json_encode([
            'success'   => true 
        ]);
    } else {
        // delete from db
        $p->deletePurchase($purchase_id);
        echo json_encode([
            'success' => false,
            'error' => $response_array[3]
        ]);
    }
} else {
    echo json_encode([
        'success'   => false,
        'error'     => "You have not selected anything to purchase."
    ]);
}