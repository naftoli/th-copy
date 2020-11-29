<?php
require_once( __DIR__ . '/../header/header.php' );

class ProfilesRouter {

    // functions as update
    public function create() {
        
        // validate that an email was sent
        if ( !isset( $_POST['email'] ) || !$_POST['email'] ) {
            $msg = 'No E-mail Address';
            $msg = "נא למלא המייל שלך";
            return json_error( $msg );
        }
        // stop calling post all the time
        $email = $_POST['email'];
        // check for a valid email
        if ( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
            $msg = "Invalid Email Address";
            if (isset($_COOKIE['lang']) && $_COOKIE['lang'] == 'he') $msg = "מייל שגוי";
            return json_error( $msg );
        }
        // find the admin_id
        $admin = \Admin::find_by_email( $email );
        // if we have an admin, reset the password
        if ( $admin && $admin instanceof \Admin ) {
            if ($admin->resetPassword()) {
                // tell the client that we sent the email
                $msg = "Account Reset Email Sent";
                if (isset($_COOKIE['lang']) && $_COOKIE['lang'] == 'he') $msg = "נשלח מייל לאיפוס החשבון";
                return json_response( $msg );
            } else {
                $msg = 'Error sending email.';
                if (isset($_COOKIE['lang']) && $_COOKIE['lang'] == 'he') $msg = "שגיאה בשליחת הדו\"א";
                return json_error( $msg );
            }
        }
    }

}

rest_router( new ProfilesRouter );
