<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mobile/reg/ajax/encrypt.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mivtzoim_purchases/classes/MivtzoimPurchases.php';

$info = $_POST['info'];
$admin_id = encrypt_decrypt('decrypt', $info['admin']);

// Check if there's already a payment in progress
$sql = "SELECT COUNT(*) FROM payment_processing WHERE admin_id = $admin_id";
$result = mysql_query($sql);
$row = mysql_fetch_row($result);

if ($row[0] > 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Your payment is already being processed. Please wait for it to complete.'
    ]);
    exit;
}

// Insert a record to indicate a payment is in progress
$sql = "INSERT INTO payment_processing (admin_id) VALUES ($admin_id)";
mysql_query($sql);

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

// After processing, remove the record
$sql = "DELETE FROM payment_processing WHERE admin_id = $admin_id";
mysql_query($sql);