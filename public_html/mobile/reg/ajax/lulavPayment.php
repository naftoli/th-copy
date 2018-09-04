<?php
require_once '../../../api/header/header.php';
require_once '../../../api/header/db.php';

$info = $_POST['info'];

require 'encrypt.php';
$admin_id = encrypt_decrypt('decrypt', $info['admin']);

$amount = $info['amount'];
$card_num = $info['cc']['num'];
$exp_date = $info['cc']['exp'];
$first_name = $info['cc']['first'];
$last_name = $info['cc']['last'];
$zip = $info['zip'];
$address = "";
$state = "";

$num_sets = 0;
foreach ( $info['sets'] as $set ) {
    $num_sets += $set['num'];
}
$description = "Mivtza Lulav 5779 purchase - Admin ID: " . $admin_id . " Number of sets purchased: " . $num_sets;

chdir('../../../');
require_once 'authorize.php';
chdir('mobile/reg/ajax/');

if ($response_array[3] == 1) { // success
    $strResponse =  $response_array[3] . ':' . 
					$response_array[4] . ':' . 
					$response_array[6] . ':' . 
                    $response_array[9];	

    $qry = $MASHPIA_DB->prepare(
        "INSERT into lulav_purchases 
        SET admin_id = :admin, 
        amount_paid = :amount, 
        authorization = :auth"
    );
    $qry->execute([
        ':admin'    => $admin_id, 
        ':amount'   => $amount, 
        ':auth'     => $strResponse
    ]);

    $purchase_id = $MASHPIA_DB->lastInsertId();
    $qry = $MASHPIA_DB->prepare(
        "INSERT INTO lulav_purchase_details 
        SET purchase_id = :id, 
        user_id = :user, 
        num_sets = :num"
    );
    foreach ( $info['sets'] as $set ) {
        $qry->execute([
            ':id'       => $purchase_id, 
            ':user'     => $set['id'], 
            ':num'      => $set['num']
        ]);
    }
    
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
    
    echo json_encode([
        'success' => true
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => $response_array[3]
    ]);
}
