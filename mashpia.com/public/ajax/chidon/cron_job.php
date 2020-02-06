<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/CustomerProfile.php';
use classes\authorize\CustomerProfile;

// find all schools that have a hold that expired and create new hold until after chidon 
$now = date('m-d-Y');
if ( $now <= '04-05-2020' ) {
    $info = [];
    $sql = "select school_id, authorize_customer_profile_id, authorize_payment_profile_id, chidon_hold_id, chidon_hold_date 
            from schools 
            where chidon_hold_id is not null 
            and chidon_hold_date is not null";
    $result = mysql_query( $sql );
    while ( $row = mysql_fetch_assoc( $result ) ) {
        $info[] = $row;
    }

    foreach ( $info as $school ) {
        $d1 = new DateTime();
        $d2 = new DateTime( $school['chidon_hold_date'] );
        if ( $d1 - $d2 > 30 ) {
            // make new hold
            $cp = new CustomerProfile( $school['authorize_customer_profile_id'] );
            $response = $cp->chargeCard( 500, $school['authorize_payment_profile_id'], null, null, $desc, "authOnlyTransaction" );
            if ( is_array( $response ) ) {
                // all good, save to db
                $id = $response['transactionResponse']['transId'];
                $sql = "update schools set chidon_hold_id = " . $id . ", chidon_hold_date = now() where school_id = " . $school['school_id'];
                @mysql_query( $sql );
            } else {
                $to = "chidon@tzivoshashem.org; accounting@tzivoshashem.org";
                $subject = "School Hold Error";
                $message = "There was an error re-adding the $500 hold on school with ID: " . $school_id;
                $headers = [
                    'From'      => 'cth@tzivoshashem.org',
                    'Reply-To'  => 'cth@tzivoshashem.org'
                ];
                @mail($to, $subject, $message, $headers);
            }
        }
    }
}