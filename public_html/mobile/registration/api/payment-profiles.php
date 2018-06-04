<?php
include_once( dirname(__FILE__) . "/header.php" );
require_once( dirname(__FILE__) . "/../../../classes/authorize/CustomerProfile.php" );
require_once( dirname(__FILE__) . "/../../../classes/authorize/PaymentProfile.php" );

use classes\authorize\CustomerProfile as CustomerProfile;

if ( $_SERVER['REQUEST_METHOD'] == "GET" ) {
    index();
}

/**
 * GET / (index)
 * 
 * Returns all payment profiles assigned to the account
 *
 * @return void
 */
function index() {
    global $admin_id;

    $customer_id_query = mysql_query(
        "SELECT authorize_customer_profile_id as id FROM admins WHERE admin_id = '$admin_id';"
    );

    if ( mysql_num_rows( $customer_id_query ) == 0 )
        render_json_error( "Invalid Account" );

    $customer_id = mysql_fetch_assoc( $customer_id_query )['id'];

    // return false if they do not have an account at all.
    if ( !$customer_id ) {
        render_json_response( false );
        die();
    }

    $customer_profile = new CustomerProfile( $customer_id );

    render_json_response( $customer_profile->paymentProfiles );
}