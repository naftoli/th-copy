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
$cc = $_POST['cc'];

// find out if the school has a customer id and profile id
$sql = "select * from schools where school_id = " . $school_id;
$result = mysql_query( $sql );
$school = mysql_fetch_assoc( $result );
$customer_id = $school['authorize_customer_profile_id'];
$payment_id = $school['authorize_payment_profile_id'];

$expArr = implode('/', $cc['exp']);
$exp = '20' . $expArr[1] . '-' . $expArr[0];

if ( $customer_id ) {
    if ( $cc ) {
        // create a new payment profile
        $paymentProfile = PaymentProfile::create( $cc['card'], $exp, $cc['cvc'], $customer_id );
        $payment_id = $paymentProfile->customerPaymentProfileId;
        if ( !$customer_id ) {
            echo "Error creating payment profile.";
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

    $paymentProfile = PaymentProfile::createBasicArray( $cc['card'], $exp, $cc['cvc'] );
    $customerProfile = CustomerProfile::create( 'TH_' . $school_id, $email, $description, $paymentProfile);
    $customer_id = $customerProfile->customerProfileId;
    $payment_id = $paymentProfile->customerPaymentProfileId;
    if ( !($customer_id && $payment_id) ) {
        echo "Error creating customer and profile id.";
        exit;
    }
}

if ( $customer_id && $payment_id ) {
    //***************** REGISTER SCHOOL **********************/
    $school_exists = mysql_query(
        " SELECT th_chidon_schools_id FROM th_chidon_schools "
        ." WHERE school_id = " . $school_id . " "
        ." AND year = " . $year . " "
        ." AND registered = 1 "
    );
    if (mysql_num_rows($school_exists) == 0) {
        $res = mysql_query(
            " INSERT INTO th_chidon_schools "
            ." SET school_id = " . $school_id . ", "
            ." year = " . $year . ", "
            ." bus = " . $bus . ", "
            ." payment_profile_id = " . $payment_id . ", "
            ." registered = 1"
        );
        if ( !$res ) echo "Error registering school for Chidon Shabbaton " . $year;
    }
}