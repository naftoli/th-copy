<?php
/***************** AUTHENTICATION **********************/
$admin_auth = array('school');
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

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

$school_id = mysql_real_escape_string( $_POST['school_id'] );
$cc = isset( $_POST['cc_info'] ) ? $_POST['cc_info'] : [];
$info = isset( $_POST['info'] ) ? $_POST['info'] : [];

// find out if the school has a customer id and profile id
$sql = "select * from schools where school_id = " . $school_id;
$result = mysql_query( $sql );
$school = mysql_fetch_assoc( $result );
$customer_id = $school['authorize_customer_profile_id'];
$payment_id = $school['authorize_payment_profile_id'];

if ( $cc ) {
    $expArr = explode('/', $cc['exp']);
    $exp = '20' . $expArr[1] . '-' . $expArr[0];
}

// flags to know if we need to update school table
$newCustomerId = $customer_id ? false : true;
$newPaymentId = $payment_id ? false : true;

if ( $customer_id ) {
    if ( $cc ) {
        // create a new payment profile
        $paymentProfile = PaymentProfile::create( $cc['card'], $exp, $cc['cvc'], $customer_id );
        if ( $paymentProfile instanceof PaymentProfile ) {
            $payment_id = $paymentProfile->customerPaymentProfileId;
        } else {
            // get error
            $error = $paymentProfile['messages']['message'][0]['text'];
            echo $error;
            exit;
        }
    }
} else {
    // create customer profile and payment profile
    // need to get admin email
    $admin_id = $admin_user['admin_id'];
    $sql = "select admin_email from admins where admin_id = " . $admin_id;
    $result = mysql_query( $sql );
    $row = mysql_fetch_assoc( $result );
    $email = $row['admin_email'];
    $description = "Customer profile for " . $school['school_name'];

    if ( !$customer_id ) {
        $paymentProfile = PaymentProfile::createBasicArray( $cc['card'], $exp, $cc['cvc'] );
        $customerProfile = CustomerProfile::create( 'TH_' . $school_id, $email, $description, $paymentProfile );
        if ( $customerProfile instanceof CustomerProfile ) {
            $customer_id = $customerProfile->customerProfileId;
            $payment_id = $customerProfile->paymentProfiles[0]['customerPaymentProfileId'];
        } else {
            echo $customerProfile['message'];
        }
    }

    if ( !($customer_id && $payment_id) ) {
        echo "Error creating customer profile.";
        exit;
    }
}

if ( $customer_id && $payment_id ) {
    // make sure to save customer and payment profiles to schools table if needed
    if ( $newCustomerId || $newPaymentId ) {
        $sql = "update schools set authorize_payment_profile_id = " . $payment_id;
        if ( $newCustomerId ) $sql .= ", authorize_customer_profile_id = " . $customer_id;
        $sql .= " where school_id = " . $school_id;
        @mysql_query( $sql );
    }

    //***************** REGISTER SCHOOL **********************/
    $success = true;
    mysql_query('set autocommit = 0');
    mysql_query('begin');

    $sql =
        " INSERT INTO th_chidon_schools "
        ." SET school_id = " . $school_id . ", "
        ." year = " . $year . ", "
        ." payment_profile_id = " . $payment_id . ", "
        ." choice = '" . $info['choice'] . "', "
        ." registered = 1";
    $res = mysql_query( $sql );
    if ( !$res ) {
        $success = false;
    }
    else {
        // put hold money on hold
        if ($info['hold'] > 0) {
            $amount = intval($info['hold']);
            $desc = "$" . $amount . " hold on School: " . $school_id . " for VR Goggle Rental.";
            $cp = new CustomerProfile($customer_id);
            $response = $cp->chargeCard($amount, $payment_id, null, null, $desc, "authOnlyTransaction");
            if (is_array($response)) {
                // all good, save to db
                $id = $response['transactionResponse']['transId'];
                $sql = "update schools set chidon_hold_id = " . $id . ", chidon_hold_date = now() where school_id = " . $school_id;
                @mysql_query($sql);
            } else {
                $success = false;
                $to = "chidon@tzivoshashem.org; accounting@tzivoshashem.org";
                $subject = "School Hold Error";
                $message = "There was an error putting a $" . $amount . " hold on school with ID: " . $school_id;
                $headers = [
                    'From' => 'cth@tzivoshashem.org',
                    'Reply-To' => 'cth@tzivoshashem.org'
                ];
                @mail($to, $subject, $message, $headers);
            }
        }
        // charge money
        if ($info['charge'] > 0) {
            $amount = intval($info['charge']);
            $desc = "$" . $amount . " charge on School: " . $school_id . " for VR Goggle Purchase.";
            $cp = new CustomerProfile($customer_id);
            $response = $cp->chargeCard($amount, $payment_id, null, null, $desc);
            if (is_array($response)) {
                // all good, save to db
                $response_text = json_encode( $response );
                $transaction_query = mysql_query(
                    " INSERT INTO transactions "
                    ." (school_id, trans_date, amount, description, response) "
                    ." VALUES ($school_id, NOW(), $amount, '$desc', '$response_text') "
                );
                @mysql_query($transaction_query);
            } else {
                $success = false;
                $to = "chidon@tzivoshashem.org; accounting@tzivoshashem.org";
                $subject = "School Hold Error";
                $message = "There was an error charging $" . $amount . " to school with ID: " . $school_id . " for VR Goggles.";
                $headers = [
                    'From' => 'cth@tzivoshashem.org',
                    'Reply-To' => 'cth@tzivoshashem.org'
                ];
                @mail($to, $subject, $message, $headers);
            }
        }
    }
    if ($success) {
        mysql_query('commit');
        mysql_query('set autocommit = 1');
    } else {
        mysql_query('rollback');
        mysql_query('set autocommit = 1');
        echo "Error registering school for Chidon Shabbaton " . $year;
    }
}