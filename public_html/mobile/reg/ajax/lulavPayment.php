<?php
require_once '../../../api/header/header.php';
require_once '../../../api/header/db.php';

$info = $_POST['info'];

require 'encrypt.php';
$admin_id = encrypt_decrypt('decrypt', $info['admin']);

$year = $info['year'];
$amount = $info['amount'];
$card_num = $info['cc']['num'];
$exp_date = $info['cc']['exp'];
$first_name = $info['cc']['first'];
$last_name = $info['cc']['last'];
$zip = $info['zip'];
$address = "";
$state = "";

$description = "Mivtza Lulav 5779 purchase - Admin ID: " . $admin_id . "; Users: " . implode(',', $info['users']);

if ( $amount > 0 ) {
    chdir('../../../');
    require_once 'authorize.php';
    chdir('mobile/reg/ajax/');

    if ($response_array[0] == 1) { // success
        $strResponse =  $response_array[3] . ':' . 
                        $response_array[4] . ':' . 
                        $response_array[6] . ':' . 
                        $response_array[9];	

        $qry = $MASHPIA_DB->prepare(
            "INSERT into lulav_purchases 
            SET admin_id = :admin, 
            amount_paid = :amount, 
            authorization = :auth, 
            users = :users, 
            year = :year"
        );
        $qry->execute([
            ':admin'    => $admin_id, 
            ':amount'   => $amount, 
            ':auth'     => $strResponse, 
            ':users'    => implode(',', $info['users']), 
            ':year'     => $year
        ]);
        
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
} else {
    echo json_encode([
        'success'   => false,
        'error'     => "You have not selected anything to purchase.";
    ])
}
