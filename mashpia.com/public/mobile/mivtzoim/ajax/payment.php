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
$first_name = $info['cc']['first'];
$last_name = $info['cc']['last'];
$zip = $info['zip'];
$address = "";
$state = "";

$description = "Mivtza Chanuka " . $year . " purchase - Admin ID: " . $admin_id;

if ( $amount > 0 ) {
    chdir('../../../');
    require_once 'authorize.php';
    chdir('mobile/reg/ajax/');

    if ($response_array[0] == 1) { // success
        $strResponse =  $response_array[3] . ':' . 
                        $response_array[4] . ':' . 
                        $response_array[6] . ':' . 
                        $response_array[9];	

        // create arrays to use for purchasing function
        $purchase = [
            'year'  =>  $year, 
            'admin' =>  $admin_id, 
            'amount' =>  $amount, 
            'auth'  =>  $strResponse
        ];

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
        if ( $p->createPurchase( $purchase, $details ) ) {
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
    } else {
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
