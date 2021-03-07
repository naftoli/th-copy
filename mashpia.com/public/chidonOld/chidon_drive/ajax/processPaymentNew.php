<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

//***************** LOAD CURRENT YEAR **********************/
require_once($_SERVER['DOCUMENT_ROOT']."/class.globalSettings.php");
$year = GlobalSettings::getChidonYear();

//*************** LOAD AUTHORIZE FUNCTIONS *********************/
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/AuthorizeAPIRequest.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/CustomerProfile.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/PaymentProfile.php';

use classes\authorize\AuthorizeAPIRequest;
use classes\authorize\CustomerProfile;
use classes\authorize\PaymentProfile;

require __DIR__ . '/../encrypt.php';
$admin = $_COOKIE['chidon_admin'];
$admin_id = encrypt_decrypt('decrypt', $admin);

$name = $_POST['name'];
$email = $_POST['email'];
$amount = $_POST['amount'];
$method = $_POST['method'];
$cc = $_POST['cc'];
$cart = json_decode($_POST['details']);
$prizes = json_decode($_POST['prizes']);

function getCustomerID() {
    global $admin_id;

    $sql = "select authorize_customer_profile_id from admins where admin_id = " . $admin_id;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    return $row['authorize_customer_profile_id'];
}

function processCC( $customer_id )
{
    global $cc, $method, $admin_id, $year, $amount, $name;

    $response = '';
    $desc = "Chidon Shabbaton Registration " . $year . " for family (admin_id): " . $admin_id;
    if ( $customer_id ) {
        $cp = new CustomerProfile( $customer_id );
        if ($method == 'charge') $response = $cp->chargeCard( $amount, null, null, null, $desc );
        else if ($method == 'hold') $response = $cp->chargeCard( $amount, null, null, null, $desc, 'authOnlyTransaction' );
    } else {
        $cc_info = [];
        $cc_info['number'] = $cc['num'];
        $cc_info['exp'] = $cc['exp'];
        $cc_info['cvc'] = $cc['cvv'];
        $cc_info['skip'] = isset($cc['skip']) ? $cc['skip'] : 0;
        $cc_info['desc'] = $desc;
        $cc_info['last'] = $name;
        $cc_info['first'] = '';

        // prepare billing address
        $billing = $cc['billing'];
        $cc_info['address'] = $billing['address'] . ' ' . $billing['apt'];
        $cc_info['city'] = $billing['city'];
        $cc_info['state'] = $billing['state'];
        $cc_info['zip'] = $billing['zip'];
        $cc_info['country'] = $billing['country'];

        require 'authorize.php';
        if ($method == 'charge') $response = chargeCreditCard($amount, $cc_info);
        else if ($method == 'hold') $response = chargeCreditCard($amount, $cc_info, 'authOnlyTransaction');
    }

    return checkResponse($response);
}

function checkResponse( $response ) {
    // check response
    $msg = '';
    $error_msg = '';
    $trans_id = 0;
    $trans_info = '';

    if ($response != null) {
        // Check to see if the API request was successfully received and acted upon
        if ($response->getMessages()->getResultCode() == "Ok") {
            // Since the API request was successful, look for a transaction response
            // and parse it to display the results of authorizing the card
            $tresponse = $response->getTransactionResponse();

            if ($tresponse != null && $tresponse->getMessages() != null) {
                $msg .= " Successfully created transaction with Transaction ID: " . $tresponse->getTransId() . "\n";
                $msg .= " Transaction Response Code: " . $tresponse->getResponseCode() . "\n";
                $msg .= " Message Code: " . $tresponse->getMessages()[0]->getCode() . "\n";
                $msg .= " Auth Code: " . $tresponse->getAuthCode() . "\n";
                $msg .= " Description: " . $tresponse->getMessages()[0]->getDescription() . "\n";

                $trans_id = $tresponse->getTransId();
                $trans_info = $trans_id . ":" . $tresponse->getResponseCode() . ":" . $tresponse->getMessages()[0]->getCode() . ":". $tresponse->getAuthCode() . ":" . $tresponse->getMessages()[0]->getDescription();
            } else {
                $error_msg .= "Transaction Failed \n";
                if ($tresponse->getErrors() != null) {
                    $error_msg .= " Error Code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
                    $error_msg .= " Error Message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";
                }
            }
            // Or, print errors if the API request wasn't successful
        } else {
            $error_msg .= "Transaction Failed \n";
            $tresponse = $response->getTransactionResponse();

            if ($tresponse != null && $tresponse->getErrors() != null) {
                $error_msg .= " Error Code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
                $error_msg .= " Error Message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";
            } else {
                $error_msg .= " Error Code  : " . $response->getMessages()->getMessage()[0]->getCode() . "\n";
                $error_msg .= " Error Message : " . $response->getMessages()->getMessage()[0]->getText() . "\n";
            }
        }
    } else {
        $error_msg .= 'There was a problem processing your credit card.';
    }

    return [
        'msg'   =>  $msg,
        'error' =>  $error_msg,
        'id'    =>  $trans_id,
        'info'  =>  $trans_info
    ];
}

function processReg( $qrys ) {
    $success = true;

    mysql_query('set autocommit=0');
    mysql_query('begin');
    foreach ($qrys as $qry) {
        if (!mysql_query($qry)) {
            $success = false;
            break;
        }
    }
    if ($success) {
        mysql_query('commit');
    } else {
        mysql_query('rollback');
    }
    mysql_query('set autocommit=1');

    return $success;
}

function processCart($auth_id, $auth_desc) {
    global $cart, $admin_id, $year, $method;

    $reg_qrys = [];
    foreach ($cart as $user_id => $items) {
        foreach ($items as $item) {
            if ($item->desc == 'reg') {
                $sql = "update th_chidon 
                        set paid = " . $item->amount . ", 
                        paid_by = " . $admin_id . ", 
                        date_paid = now()
                        where user_id = " . $user_id . "
                        and year = " . $year;
                $reg_qrys[] = $sql;
            } else if ($item->desc == 'yarmulka') {
                $sql = "update th_chidon 
                        set yarmulka = " . $item->size . " 
                        where user_id = " . $user_id . " 
                        and year = " . $year;
                $reg_qrys[] = $sql;
            }
        }
    }
    $reg_result = processReg( $reg_qrys );

    $qry = "insert into th_chidon_parent_purchases 
            set admin_id = " . $admin_id . ", 
            authorize_id = '" . $auth_id . "', 
            authorize_desc = \"" . $auth_desc . "\", 
            authorize_trans_type = '" . $method . "', 
            purchase_date = now()";
    foreach ($cart as $user_id => $items) {
        foreach ($items as $item) {
            if ($item->desc != 'reg') {
                $desc = $item->desc;
                switch ($desc) {
                    case 'celeb_box':
                        $qry .= ", " . $desc . " = " . $item->qty;
                        break;
                    case 'celeb_box_add':
                    case 'celeb_box_add_ship':
                    case 'sweater_mother_ship':
                    case 'sweater_father_ship':
                    case 'sweater_bubby_ship':
                    case 'sweater_zaidy_ship':
                        $qry .= ", " . $desc . " = 1";
                        break;
                    case 'sweater_mother':
                    case 'sweater_father':
                    case 'sweater_bubby':
                    case 'sweater_zaidy':
                        $qry .= ", " . $desc . " = '" . $item->size . "'";
                        break;
                    case 'celeb_box_add_addr':
                    case 'sweater_mother_ship_addr':
                    case 'sweater_father_ship_addr':
                    case 'sweater_bubby_ship_addr':
                    case 'sweater_zaidy_ship_addr':
                        $qry .= ", " . $desc . " = \"" . $item->address . "\"";
                        break;
                }
            }
        }
    }
//    echo $qry;
    $purchase_result = mysql_query($qry);

    // for sweater purchases update purchased amount in chidon_sweaters table
    $updates = [];
    $sizes = [
        'xs'    =>  'Adult XS',
        'small' =>  'Adult Small',
        'medium'=>  'Adult Medium',
        'large' =>  'Adult Large',
        'xl'    =>  'Adult XL'
    ];
    foreach ($cart as $user_id => $items) {
        foreach ($items as $item) {
            if ($item->desc != 'reg') {
                $desc = $item->desc;
                switch ($desc) {
                    case 'sweater_mother':
                        $updates[] = "update chidon_sweaters set purchased = purchased + 1 
                                      where sweater_name = 'Proud Chidon Mother' 
                                      and size = '" . $sizes[$item->size] . "'";
                        break;
                    case 'sweater_father':
                        $updates[] = "update chidon_sweaters set purchased = purchased + 1 
                                      where sweater_name = 'Proud Chidon Father' 
                                      and size = '" . $sizes[$item->size] . "'";
                        break;
                    case 'sweater_bubby':
                        $updates[] = "update chidon_sweaters set purchased = purchased + 1 
                                      where sweater_name = 'Proud Chidon Bubby' 
                                      and size = '" . $sizes[$item->size] . "'";
                        break;
                    case 'sweater_zaidy':
                        $updates[] = "update chidon_sweaters set purchased = purchased + 1 
                                      where sweater_name = 'Proud Chidon Zaidy' 
                                      and size = '" . $sizes[$item->size] . "'";
                        break;
                    default:
                        break;
                }
            }
        }
    }
    foreach ($updates as $update) {
        if (!mysql_query($update)) {
            $subject = "Errror in Sweater Purchase";
            $msg = "Sweater has been purchased but there was an error updating it in the database.";
            mail('chidon@tzivoshashem.org', $subject, $msg);
        }
    }

    return [
        'reg'   =>  $reg_result,
        'purchase'  =>  $purchase_result
    ];
}

function processPrizes() {
    global $prizes, $year;
    if (empty($prizes)) return false;

    foreach ($prizes as $user_id => $prize_items) {
        foreach ($prize_items as $prize) {
            $sql = "insert into chidon_user_prizes set user_id = " . $user_id . ", prize_id = " . $prize->id . ", year = " . $year;
            mysql_query($sql);
            $sql = "update chidon_prizes set purchased = purchased + 1 where prize_id = " . $prize->id;
            mysql_query($sql);
        }
    }
    return true;
}

// process payment / hold
if ($cc['skip']) {
    $trans_id = 1;
    $trans_info = "skipped credit card payment";
    $details = "skipped credit card payment";
} else {
    $customer_id = 0;
    if ($cc['on_file']) {
        $customer_id = getCustomerID();
        if (empty($customer_id)) {
            echo json_encode([
                'success' => false,
                'msg' => 'You do not have a credit card on file, please put enter credit card info and try again.'
            ]);
            exit;
        }
    }

    $ccResult = processCC($customer_id);
    if ($ccResult['error']) {
        echo json_encode([
            'success' => false,
            'msg' => $ccResult['error']
        ]);
        exit;
    }

    $trans_id = $ccResult['id'];
    $trans_info = $ccResult['info'];
    $details = $ccResult['msg'];
}

// go through cart and figure out who to register and which things to save as purchases
$cartProcessed = processCart($trans_id, $trans_info);

// process user prizes
$prizesProcessed = processPrizes();

// get email for admin
$sql = 'select admin_email from admins where admin_id = ' . $admin_id;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$admin_email = $row['admin_email'];

// send email
// To send HTML mail, the Content-type header must be set
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-type: text/html; charset=iso-8859-1';
$headers[] = 'From: chidon@tzivoshashem.org';
$headers[] = 'Reply-to: chidon@tzivoshashem.org';
$headers[] = 'Cc: ' . $admin_email;

$subject = "Chidon Shabbaton Registration for " . $year;
$message = "Thank you for your payment of $" . $amount . ". Your transaction id is: " . $trans_id . "<br />
    The details for your transaction is: <br />" . $details . "<br /><br />";
if ($cartProcessed['reg']) $message .= "Your child(ren) have been successfully registered for the shabbaton.<br /><br />";
else $message .= "There was an error registering your child(ren) for the shabbaton. Please contact HQ (718-907-8884).<br /><br />";
if ($cartProcessed['purchase']) $message .= "You extra purchases have been saved.<br /><br />";
else $message .= "There was an error saving your extra purchases. Please contact HQ (718-907-8884).<br /><br />";
if ($prizesProcessed) $message .= "Your prize selection has been saved.";

$message .= "The details of your purchases are as follows:<br /><br /><ul>";
foreach ($cart as $user_id => $items) {
    foreach ($items as $item) {
        $desc = $item->desc;
        if (strpos($desc, 'addr') !== false) continue;
        if ($desc == 'reg') $message .= "<li>Registration for User ID: " . $user_id . " - $" . $item->amount . "</li>";
        else if ($desc == 'yarmulka') $message .= "<li>Yarmulka for User ID: . " . user_id . ", Size: " . $item->size . " - $" . $item->amount . "</li>";
        else $message .= "<li>" . $desc . " - $" . $item->amount . "</li>";
    }
}
$message .= "</ul><br />";
$mail_success = mail($email, $subject, implode("\r\n", $headers));

$res_msg = "Your transaction has been processed. Your transaction ID is: " . $trans_id . ".\n";
if ($cartProcessed['reg']) $res_msg .= "Your child(ren) have been registered for the Shabbaton.\n";
if ($cartProcessed['purchase']) $res_msg .= "Your purchases are being processed.\n";
if ($prizesProcessed) $res_msg .= "Your prize selection has been saved.\n";
if ($mail_success) $res_msg .= "You should be getting a confirmation email shortly.\n";
$res_msg .= "Thank You!";

echo json_encode([
    'success'   =>  true,
    'msg'       =>  $res_msg
]);