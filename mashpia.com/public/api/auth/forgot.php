<?php
require_once( __DIR__ . '/../header/header.php' );

class ProfilesRouter {

    // functions as update
    public function create() {
        
        // validate that an email was sent
        if ( !isset( $_POST['email'] ) || !$_POST['email'] )
            return json_error('No E-mail Address');
        // stop calling post all the time
        $email = $_POST['email'];
        // check for a valid email
        if ( !filter_var($email, FILTER_VALIDATE_EMAIL) )
            return json_error('Invalid Email Address');
        // find the admin_id
        $admin = \Admin::find_by_email( $email );
        // if we have an admin, reset the password
        if ( $admin && $admin instanceof \Admin )
            $admin->resetPassword();
        // tell the client that we sent the email regardless
        return json_response('Account Reset Email Sent');
    }

}

rest_router( new ProfilesRouter );
