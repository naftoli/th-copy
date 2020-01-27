<?php
// ini_set('display_errors',1);
require_once __DIR__ . '/../../../api/header/db.php';
require_once __DIR__ . '/../../../class.globalSettings.php';

$year = GlobalSettings::getChidonYear();
$paypalInfo = json_decode( $_POST['paypalInfo'] );
$info = $_POST['info'];
$type = $_POST['type'];

// prepare variables for donor table
$name = $info['name'];
$display_name = $info['display_name'];
$anonymous = $info['anonymous'];
$email = $info['email'];
$phone = $info['phone'];
$notes = $info['dedication'];

// prepare variables for donation table
$amount = $info['amount'];
if ( $type == 'family' ) {
    $forFamily = $info['family'];
    $forChild = $info['forChild'];
    $children = $info['children'];
}

// insert into transactions table
$trans_id = $paypalInfo->id;
$trans_info = 'Paypal transaction ID: ' . $trans_id . ' Payer ID: ' . $paypalInfo->payer->payer_id . ' Email Address: ' . $paypalInfo->payer->email_address . 
    ' Name: ' . $paypalInfo->payer->name->given_name . ' ' . $paypalInfo->payer->name->surname . ' More Info: ' . $paypalInfo->links[0]->href;
    
// send email
include_once 'sendEmail.php'; 
sendEmail( $amount, $trans_id, $email );

if ( $type == 'family' ) {
    // add to donations table 
    $stmt = $MASHPIA_DB->prepare("
        INSERT INTO chidon_donations 
        SET 
            name = :name, 
            display_name = :display_name, 
            anonymous = :anonymous, 
            chidon_year = :year, 
            donation_amount = :amount, 
            for_family_id = :family_id, 
            transaction_id = :trans_id, 
            transaction_info = :trans_info, 
            email = :email, 
            phone = :phone, 
            notes = :notes
    ");
    $res = $stmt->execute([
        ':name'         =>  $name, 
        ':display_name' =>  $display_name, 
        ':anonymous'    =>  $anonymous,
        ':year'         =>  $year, 
        ':amount'       =>  $amount, 
        ':family_id'    =>  $forFamily, 
        ':trans_id'     =>  $trans_id, 
        ':trans_info'   =>  $trans_info, 
        ':email'        =>  $email,
        ':phone'        =>  $phone, 
        ':notes'        =>  $notes
    ]);
    $donation_id = $MASHPIA_DB->lastInsertId();

    // prepare entry into user_subsidies table
    $stmt = $MASHPIA_DB->prepare("
        INSERT INTO chidon_user_subsidies 
        SET 
            chidon_donation_id = :donation_id, 
            chidon_year = :year, 
            user_id = :user_id, 
            subsidy_amount = :amount
    ");
    // if it was for specific child, then put the entire amount for that child
    $regCost = 350;
    if ( $forChild ) {
        if ( $amount > $regCost ) $amount = $regCost;
        $stmt->execute([
            ':donation_id'  =>  $donation_id, 
            ':year'         =>  $year, 
            ':user_id'      =>  $forChild, 
            ':amount'       =>  $amount
        ]);
    } else {
        // divide amount by number of children
        $perChildAmount = number_format($amount / count( $children ), 2);
        if ( $perChildAmount > $regCost ) $perChildAmount = $regCost;
        foreach ( $children as $user_id ) {
            $stmt->execute([
                ':donation_id'  =>  $donation_id, 
                ':year'         =>  $year, 
                ':user_id'      =>  $user_id, 
                ':amount'       =>  $perChildAmount
            ]);
        }
    }
} else if ( $type == 'donation' ) {
    $stmt = $MASHPIA_DB->prepare("
        INSERT INTO chidon_donations 
        SET 
            name = :name, 
            display_name = :display_name, 
            anonymous = :anonymous,
            chidon_year = :year, 
            donation_amount = :amount, 
            transaction_id = :trans_id, 
            transaction_info = :trans_info, 
            email = :email, 
            phone = :phone 
    ");
    $res = $stmt->execute([
        ':name'         =>  $name,
        ':display_name' =>  $display_name, 
        ':anonymous'    =>  $anonymous,
        ':year'         =>  $year, 
        ':amount'       =>  $amount, 
        ':trans_id'     =>  $trans_id, 
        ':trans_info'   =>  $trans_info, 
        ':email'        =>  $email,
        ':phone'        =>  $phone
    ]);
}

echo json_encode([
    'success'   =>  true
]);