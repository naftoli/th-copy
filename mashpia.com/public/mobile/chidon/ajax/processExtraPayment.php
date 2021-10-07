<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require __DIR__ . '/../../../db.php';
require __DIR__ . '/../../../api/header/db.php';

//***************** LOAD CURRENT YEAR **********************/
require_once __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

//*************** LOAD AUTHORIZE FUNCTIONS *********************/
require_once __DIR__ . '/../../../classes/authorize/AuthorizeAPIRequest.php';
require_once __DIR__ . '/../../../classes/authorize/CustomerProfile.php';

use classes\authorize\CustomerProfile;

require __DIR__ . '/encrypt.php';
$admin = $_POST['admin_id'];
$admin_id = encrypt_decrypt('decrypt', $admin);

$amount = $_POST['amount'];
$cc = $_POST['cc'];
$khkUsers = json_decode($_POST['khk_users']);

$customer_id = 0;
if (intval($cc['on_file']) == 1) {
    $customer_id = getCustomerID();
    if (!$customer_id || empty($customer_id)) {
        echo json_encode([
            'success' => false,
            'msg' => 'You do not have a credit card on file, please enter credit card info and try again.'
        ]);
        exit;
    }
}

$ccResult = processCC($customer_id);
if (is_string($ccResult)) {
    echo json_encode([
        'success'   => false,
        'msg'       => $ccResult
    ]);
} else {
    $trans_id = $ccResult->getTransactionResponse()->getTransId();
    $response = parseResponse($ccResult);
    if (!empty($response['error'])) {
        echo json_encode([
            'success' => false,
            'msg' => $response['error']
        ]);
    } else {
        // update tables
        $sql = "update th_chidon set khk_reg = 1 where user_id in (" . implode(',', $khkUsers) . ") and year = " . $year;
        if (mysql_quey($sql)) {
            echo json_encode([
                'success' => true,
                'msg' => $response['success']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'msg' => 'Your credit card was charged, however there was a problem saving it to our database. please contact HQ to rectify this issue.'
            ]);
        }

//        // get admin email
//        $sql = "select admin_email from admins where admin_id = " . $admin_id;
//        $result = mysql_query($sql);
//        $row = mysql_fetch_assoc($result);
//        $email = $row['admin_email'];
//        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
//            $headers[] = 'MIME-Version: 1.0';
//            $headers[] = 'Content-type: text/html; charset=iso-8859-1';
//            $headers[] = 'From: chidon@tzivoshashem.org';
//            $headers[] = 'Reply-to: chidon@tzivoshashem.org';
//
//            $subject = 'Chidon Registration Confirmation';
//
//            $msg = "Thank you for confirming your Chidon Registration details.
//            <br /><br />
//            Your payment of $$amount has been received. Your transaction ID is: $trans_id.
//            <br /><br />
//            Your registration is now complete.
//            <br /><br />
//            All orders and prizes will be sent out as soon as possible! Mechayil el chayil!";
//
//            // send email
//            @mail($email, $subject, $msg, implode("\r\n", $headers));
//        }
    }
}

function getCustomerID() {
    global $admin_id;

    $sql = "select authorize_customer_profile_id from admins where admin_id = " . $admin_id;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    return $row['authorize_customer_profile_id'];
}

function processCC( $customer_id )
{
    global $cc, $admin_id, $year, $amount;

    $response = '';
    $desc = "Chidon KHK Payment " . $year . " for family (admin_id): " . $admin_id;
    if ( $customer_id ) {
        $cp = new CustomerProfile( $customer_id );
        $response = $cp->chargeCard( $amount, null, null, null, $desc );
    } else {
        $cc_info = [];
        $cc_info['number'] = $cc['num'];
        $cc_info['exp'] = $cc['mm'] . '' . $cc['yy'];
        $cc_info['cvc'] = $cc['cvv'];
        $cc_info['desc'] = $desc;
        $cc_info['last'] = $cc['name'];
        $cc_info['first'] = '';
        $cc_info['address'] = $cc['address'];
        $cc_info['city'] = $cc['city'];
        $cc_info['state'] = $cc['state'];
        $cc_info['zip'] = $cc['zip'];
        $cc_info['country'] = $cc['country'];

        require __DIR__ . '/../../../chidonOld/chidon_drive/ajax/authorize.php';
        $response = chargeCreditCard($amount, $cc_info);
    }

    return $response;
}