<?php
ini_set('display_errors',1);
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/class.globalSettings.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/classes/authorize/CustomerProfile.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/classes/authorize/PaymentProfile.php');
use classes\authorize\CustomerProfile;
use classes\authorize\PaymentProfile;

$school_id = mysql_real_escape_string( $_POST['school'] );
$sql = "select * from schools where school_id = " . $school_id;
$result = mysql_query( $sql );
$school = mysql_fetch_assoc( $result );

if ($school['authorize_customer_profile_id']) {
    $customerProfile = new CustomerProfile($school['authorize_customer_profile_id']);
    if ($customerProfile->invalid) { 
        // remove profileId
        $sql = "delete authorize_customer_profile_id, authorize_payment_profile_id from schools where school_id = " . $school_id;
        mysql_query( $sql );
        $school['authorize_customer_profile_id'] = null;
        $school['authorize_payment_profile_id'] = null;
    } else {
        $paymentProfile = new PaymentProfile($school['authorize_payment_profile_id'], $school['authorize_customer_profile_id']);
        if ($paymentProfile->invalid) {
            // remove payment profile
            $paymentProfile->delete();
            $sql = "delete authorize_payment_profile_id from schools where school_id = " . $school_id;
            mysql_query( $sql );
            $school['authorize_payment_profile_id'] = null;
        }
    }
}

$year = GlobalSettings::getChidonYear();
$sql = "select * from th_chidon_schools where year = " . $year . " and school_id = " . $school_id;
$result = mysql_query( $sql );
$schoolInfo = mysql_fetch_assoc( $result );
$school['info'] = $schoolInfo;

echo json_encode( $school );
?>