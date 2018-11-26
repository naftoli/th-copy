<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class SchoolRegistrationRouter {
    public function register(){
        global $current_user; global $MASHPIA_DB;
        // get POST params
        $school_id 				= mysql_real_escape_string(isset($_POST['school_id'])? $_POST['school_id'] : "");
        $amount 				= mysql_real_escape_string($_POST['amount']);
        $description 			= mysql_real_escape_string($_POST['description']);
        $customer_profile_id	= mysql_real_escape_string($_POST['customer_profile_id']);
        $payment_profile_id 	= mysql_real_escape_string($_POST['payment_profile_id']);
        // charge the card
        $customer_profile = new classes\authorize\CustomerProfile($customer_profile_id, false);
        
        if ( $amount > 0 ) {
            $response = $customer_profile->chargeCard(
                $amount, $payment_profile_id, null, null, $description
            );
            if ( $response == 'Error (E00040): Customer Profile ID or Customer Payment Profile ID not found.' )
                $response = "Error (E00040): Invalid Card on File, Please go back and update it.";
            
            if ( !is_array( $response ) )
                json_error( $response, false, 200 );

            // save to transactions table.
            $transaction_query = $MASHPIA_DB->prepare(
                "INSERT INTO transactions (trans_date, school_id, admin_id, description, amount, first, last, zip, response) "
                ."VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $statusTransaction = $transaction_query->execute([
                $school_id, $current_user->admin_id, $description, $amount, 
                $current_user->first, $current_user->last,
                $current_user->admin_postal, json_encode( $response )
            ]);
        } else {
            $statusTransaction = true;
            $response = false;
        }
        // get the current registration info
        $year = GlobalSettings::getRegistrationYear();
        $school = School::find( $school_id, [ 'include' => 'school_registrations' ] );
        
        $statusRegistration = $school->register( $year, $amount, $current_user->admin_id );
        $registration = $school->registration( $year );

        // send email to office
        $to = "cth@tzivoshashem.org";
        $subject = "School Registration $year";
        $msg = $school->school_name. " has just registered their school for $year. ";
        $headers = 'From: admin@mashpia.com' . "\r\n";
        $headers .= 'Reply-to: cth@tzivoshashem.org' . "\r\n";

        if ($registration->type == 1)
            $msg .= " They have indicated that they are a tuition school that pays for all their students.";
        if ($registration->type == 2)
            $msg .= " They have indicated that they are a school that guarentees that all children will register by " . $school->earlyBird()->format('F j') . ".";
        if ($registration->type == 3)
            $msg .= " They have indicated that they are not a tuition school and parents need to pay complete fee.";
        @mail($to, $subject, $msg, $headers);

        json_response([
            "payment" => $response,
            "saved" => $statusTransaction && $statusRegistration
        ]);
    }
}

rest_router( new SchoolRegistrationRouter );