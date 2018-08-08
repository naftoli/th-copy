<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

use classes\authorize\CustomerProfile as CustomerProfile;

class ProfilesRouter {
    
    function index() {
        global $current_user;
        
        if ( $current_user->login['code'] === 'BC' ) {
            $school = School::find( $current_user->login['id'] );
            $customer_profile = $school->customerProfile();
        } else {
            $customer_profile = $current_user->customerProfile();
        }
        
        if ( !$customer_profile )
            json_response( false );

        json_response( $customer_profile->paymentProfiles );
    }

}

rest_router( new ProfilesRouter );