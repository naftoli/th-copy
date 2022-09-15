<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', 1);

require_once '../../../api/header/header.php';
require_once '../../../api/header/db.php';

$info = $_POST['info'];

require 'encrypt.php';
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

//$description = "Mivtza Lulav " . $year . " purchase - Admin ID: " . $admin_id . "; Users: " . implode(',', $info['users']);
$description = "000:00 #lulav " . $amount;

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
            "INSERT into mashpia_purchases.purchases 
            SET admin_id = :admin, 
            amount_paid = :amount, 
            authorization = :auth, 
            year = :year"
        );
        $qry->execute([
            ':admin'    => $admin_id, 
            ':amount'   => $amount, 
            ':auth'     => $strResponse,
            ':year'     => $year
        ]);
        $purchase_id = $MASHPIA_DB->lastInsertId();

        $qry = $MASHPIA_DB->prepare(
            "INSERT INTO mashpia_purchases.purchase_details 
                SET purchase_id = :id, 
                user_id = :user, 
                item_id = 1, 
                qty = 1"
        );

        foreach ($info['users'] as $user_id) {
            $qry->execute([
                ':id'       => $purchase_id,
                ':user'     => $user_id,
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
} else {
    echo json_encode([
        'success'   => false,
        'error'     => "You have not selected anything to purchase."
    ]);
}
