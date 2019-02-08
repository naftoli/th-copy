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
$bus = mysql_real_escape_string( $_POST['bus'] );
$food = mysql_real_escape_string( $_POST['food'] );
$food = ($food == 'true' ? 1 : 0);
$cc = isset( $_POST['cc_info'] ) ? $_POST['cc_info'] : [];

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
        $customerProfile = CustomerProfile::create( 'TH_' . $school_id, $email, $description, $paymentProfile);
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
    $school_exists = mysql_query(
        " SELECT th_chidon_schools_id FROM th_chidon_schools "
        ." WHERE school_id = " . $school_id . " "
        ." AND year = " . $year . " "
        ." AND registered = 1 "
    );
    if (mysql_num_rows($school_exists) == 0) {
        $sql = 
        " INSERT INTO th_chidon_schools "
        ." SET school_id = " . $school_id . ", "
        ." year = " . $year . ", "
        ." bus = " . $bus . ", "
        ." food = " . $food . ", "
        ." payment_profile_id = " . $payment_id . ", "
        ." registered = 1";
    } else {
        $sql = 
        " UPDATE th_chidon_schools "
        ." SET bus = " . $bus . ", "
        ." food = " . $food . ", "
        ." payment_profile_id = " . $payment_id . " "
        ." WHERE school_id = " . $school_id . " "
        ." AND year = " . $year . " "
        ." AND registered = 1";
    }
    $res = mysql_query( $sql );
    if ( !$res ) echo "Error registering school for Chidon Shabbaton " . $year;
}