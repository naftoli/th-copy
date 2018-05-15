<?php
define("AUTHORIZE_NET_SANDBOX", true);
// info
include_once( dirname(__FILE__) . "/../header.php" );
require_once( dirname(__FILE__) . "/../../../../class.globalSettings.php" );
// campaigns
require_once( dirname(__FILE__) . "/../../../../class.campaignEnrollment.php" );
// payments
require_once( dirname(__FILE__) . "/../../../../classes/authorize/CustomerProfile.php" );
require_once( dirname(__FILE__) . "/../../../../classes/authorize/PaymentProfile.php" );
// birthdays
require_once( dirname(__FILE__) . "/../../../../class.birthday.php" );
require_once( dirname(__FILE__) . "/../../../../class.birthdayYi.php" );
require_once( dirname(__FILE__) . "/../../../../class.heDob.php" );

use classes\authorize\CustomerProfile as CustomerProfile;
use classes\authorize\PaymentProfile as PaymentProfile;

if ( $_SERVER['REQUEST_METHOD'] != "POST" )
    render_json_error( "Invalid Request", "Invalid Request Type. Expecting POST" );

$year = GlobalSettings::getCurrentYear();
$description = "User Registration For $year: " . implode(", ", array_map( function( $user ){
    return $user['user_serial'];
}, $_POST['users']));
$amount = intval( $_POST['total'] );
$shipping_charges = intval( $_POST[ 'shipping_charges' ] );
$zip = mysql_real_escape_string( $_POST[ 'zip' ] );
/******************************** ADMIN INFO ********************************/
// get the email for the account
$admin_info_query = mysql_query(
    "SELECT admin_email, authorize_customer_profile_id FROM admins WHERE admin_id = '$admin_id'"
);
$admin_info = mysql_fetch_assoc( $admin_info_query );
$admin_email = $admin_info[ 'admin_email' ];
$customer_profile_id = isset( $admin_info['authorize_customer_profile_id'] ) ? $admin_info['authorize_customer_profile_id'] : false;

if ( !$admin_email )
    render_json_error(
        "It appears that you do not have an email address on file. "
        ."Please go to your settings and add one before registering your children. "
        ."Thank you!"
    );

/******************************** PAYMENT ********************************/
if ( !$customer_profile_id ){
    $payment_profile = PaymentProfile::createBasicArray(
        $_POST['cc-number'], $_POST['cc-exp'], $_POST['x_card_code']
    );
    $customer_profile = CustomerProfile::create( "cth_$admin_id", $admin_email, false, $payment_profile );
    
    if ( !($customer_profile instanceof CustomerProfile) ) render_json_error( $customer_profile["message"] );
    if ( count( $customer_profile->paymentProfiles ) == 0 ) render_json_error( "Invalid Payment Method" );
    // update the DBS
    mysql_query(
         " UPDATE admins SET authorize_customer_profile_id = $customer_profile->customerProfileId "
        ." WHERE admin_id = '$admin_id'; "
    );
    $payment_profile_id = $customer_profile->paymentProfiles[0]['customerPaymentProfileId'];
} else {
    $customer_profile = new CustomerProfile( $customer_profile_id );
    // if it is invalid, delete the record from the table
    if ( !($customer_profile instanceof CustomerProfile) ) {
        mysql_query( "UPDATE admins SET authorize_customer_profile_id = NULL WHERE admin_id = '$admin_id';" );
        render_json_error( "Could not load User Payment Info. Please try again." );
    }
    if ( count( $customer_profile->paymentProfiles ) == 0 ) 
        render_json_error( "Invalid Payment Method" );
    $payment_profile_id = $customer_profile->paymentProfiles[0]['customerPaymentProfileId'];
}
// charge the card
$response = $customer_profile->chargeCard(
    $amount, $payment_profile_id, null, null, $description
);
// Let the user know if the transaction fails
if ( !is_array( $response ) ) render_json_error( $response );

/******************************** REGISTER USERS ********************************/
// save the transaction
$responseString = json_encode( $response );
$transactions_query = mysql_query(
     " INSERT INTO transactions SET trans_date = NOW(), "
    ." admin_id = '$admin_id', "
    ." description = '$description', "
    ." amount = '$amount', "
    ." reg_amount = '" . ( $amount - $shipping_charges ) . "', "
    ." ship_amount = '$shipping_charges', "
    ." zip = '$zip', "
    ." users_registered = '" . implode(", ", array_map( function( $user ) { return $user['user_id']; }, $_POST['users'])) . "', "
    ." response = '" . $responseString . "'"
);

// save the registration
$errors = [];
foreach ( $_POST['users'] as $user ){
    $user_id = $user['user_id'];
    $user_amount = $user[ 'registration_fee' ];
    $school_id = $user[ 'school_id' ];
    $user_registration_sql = 
        " INSERT INTO user_registration (user_id, admin_id, year, reg_date, paid, school_id) "
        ." VALUES ( '$user_id', '$admin_id', '$year', NOW(), '$user_amount', '$school_id') ";
    $user_registration_query = mysql_query( $user_registration_sql );
    
    if ( !$user_registration_query ){
        $errors[] = "Could not insert into user_registration.\nSQL: $user_registration_sql\nError: " . mysql_error();
    }
    // update the user info
    $users_query_1 = mysql_query(
        "UPDATE users SET user_registered = NOW() WHERE user_id = '$user_id';"
    );
    $users_query_2 = mysql_query(
         " UPDATE users SET user_start_date = ".unixtojd()
        ." WHERE user_id = '$user_id' AND user_start_date IS NULL;"
    );
    if ( !$users_query_1 || !$users_query_2 ){
        $errors[] = ("Could not update users table for user_id: " . $user['user_id']);
    }

    // make sure that they have a rank
    $rank_query = mysql_query( "SELECT * FROM rank_marks WHERE user_id = '$user_id'" );
    if ( mysql_num_rows( $rank_query ) == 0 ) {
        mysql_query(
            "INSERT INTO rank_marks (rank_ord, user_id, date_promoted) "
            ." VALUES ( 1, '$user_id', '" . unixtojd() . "');"
        );
    }

    try {
        $c = new CampaignEnrollment($user_id);
        $c->enroll();
    } catch (EnrollmentException $e) {
        $errors[] = "Campaign Enrollment Error: " . $e->getMessage();
    }

    // create birthday missions
    $b = new Birthday( $user_id );      $b->setBirthday();
    $bi = new BirthdayYi( $user_id );   $bi->setBirthday();
    $hdob = new HeDob( $user_id );      $hdob->setHeDob();
}

if ( count( $errors ) > 0 ){
    $to = "bugs@tzivoshashem.org";
    $subject = "Mobile Registration Error(s)";
    $msg = implode( "\n\n", $errors );
    mail( $to, $subject, $msg );
    render_json_response([ 
        "msg" => "It appears that there have been " . count( $errors ) . " error(s) while registering your children. "
                ."We have sent details directly to our technical team who will work to resolve the issues as soon as possible."
    ]);
} else {
    render_json_response( false );
}