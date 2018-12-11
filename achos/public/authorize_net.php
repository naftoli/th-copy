<?php

    include("db.php");
    
    //check for spammers    
    include 'check_for_spammers.php';

    //------------------------------------------------------------------------------
    // authorize.net script
    //------------------------------------------------------------------------------
        // By default, this sample code is designed to post to our test server for
        // developer accounts: https://test.authorize.net/gateway/transact.dll
        // for real accounts (even in test mode), please make sure that you are
        // posting to: https://secure.authorize.net/gateway/transact.dll

        $post_url = "https://secure.authorize.net/gateway/transact.dll";
        $amount         = mysql_real_escape_string( $_POST['ccamount'] );
        $card_num       = mysql_real_escape_string( $_POST['ccnum'] );
        $exp_date       = mysql_real_escape_string( $_POST['ccexp'] );
        $first_name     = mysql_real_escape_string( $_POST['fname'] );
        $last_name      = mysql_real_escape_string( $_POST['lname'] );
        $email          = mysql_real_escape_string( $_POST['email'] );
        $address        = mysql_real_escape_string( $_POST['address'] );
        $state          = mysql_real_escape_string( $_POST['state'] );
        $zip            = mysql_real_escape_string( $_POST['zip'] );
        $description    = mysql_real_escape_string( $_POST['desc'] );
        $city           = mysql_real_escape_string( $_POST['city'] );
        $country        = mysql_real_escape_string( $_POST['country'] );
        $phone          = mysql_real_escape_string( $_POST['phone'] );
        $reason         = mysql_real_escape_string( $_POST['reason'] );
        $dedication     = mysql_real_escape_string( $_POST['dedication'] );
        $family         = mysql_real_escape_string( $_POST['family'] );
        
        //check for 'ali baba'
        if ( strtolower( $first_name ) == 'ali' && strtolower( $last_name ) == 'baba' ) {
            echo "unsuccessful";
            exit;
        }
        
        //variable to know whether to send email
        $charged = false;
        $post_values = array(
               
                // the API Login ID and Transaction Key must be replaced with valid values
               
                // for testing:
                //"x_login"               => "75sqQ96qHEP8",
                //"x_tran_key"            => "7r83Sb4HUd58Tz5p",

                // live site:               
                "x_login"            => "4FW7gsD8Tr",
                "x_tran_key"         => "6f7z4c79NMLU4293",

                "x_version"             => "3.1",
                "x_delim_data"          => "TRUE",
                "x_delim_char"          => "|",
                "x_relay_response"      => "FALSE",
         
                "x_type"                => "AUTH_CAPTURE",
                "x_method"              => "CC",
                "x_card_num"            => $card_num ,
                "x_exp_date"            => $exp_date ,

                "x_amount"              => $amount,
                "x_description"         => $description,

                "x_first_name"          => $first_name,
                "x_last_name"           => $last_name,
                "x_address"             => $address,
                "x_state"               => $state,
                "x_zip"                 => $zip
               
                // Additional fields can be added here as outlined in the AIM integration
                // guide at: http://developer.authorize.net               
        );

        // This section takes the input fields and converts them to the proper format
        // for an http post.  For example: "x_login=username&x_tran_key=a1B2c3D4"
        $post_string = "";
        foreach( $post_values as $key => $value )
                { $post_string .= "$key=" . urlencode( $value ) . "&"; }
        $post_string = rtrim( $post_string, "& " );

        // The following section provides an example of how to add line item details to
        // the post string.  Because line items may consist of multiple values with the
        // same key/name, they cannot be simply added into the above array.
        //
        // This section is commented out by default.
        /*
        $line_items = array(
                "item1<|>golf balls<|><|>2<|>18.95<|>Y",
                "item2<|>golf bag<|>Wilson golf carry bag, red<|>1<|>39.99<|>Y",
                "item3<|>book<|>Golf for Dummies<|>1<|>21.99<|>Y");
               
        foreach( $line_items as $value )
                { $post_string .= "&x_line_item=" . urlencode( $value ); }
        */

        // This sample code uses the CURL library for php to establish a connection,
        // submit the post, and record the response.
        // If you receive an error, you may want to ensure that you have the curl
        // library enabled in your php configuration
        $request = curl_init($post_url); // initiate curl object
                curl_setopt($request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
                curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
                curl_setopt($request, CURLOPT_POSTFIELDS, $post_string); // use HTTP POST to send form data
                curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response.
                $post_response = curl_exec($request); // execute curl post and store results in $post_response
                // additional options may be required depending upon your server configuration
                // you can find documentation on curl options at http://www.php.net/curl_setopt
        curl_close ($request); // close curl object
        
        $response = "";
        // This line takes the response and breaks it into an array using the specified delimiting character
        $response_array = explode($post_values["x_delim_char"],$post_response);     

        // ------------------------------------------------------------------------------------------------------
        // break out response from payment provider.
        // ------------------------------------------------------------------------------------------------------
        
        if($response_array)
        {
            // ***** SUCCESSFULL **** //
            if ($response_array[0] == 1) {  
                $response .= $response_array[0] . ":";
                $response .= $response_array[3] . ":";
                $response .= $response_array[4] . ":";
                $response .= $response_array[6] . ":";
                $response .= $response_array[9];
                $charged = true;
            }
            else {
                $response .= $response_array[3] . "\n";          
            }
        }
        if ($response == "") {
            $response = "unsuccessful";
        }
        echo $response; 
        
        //save to donations database
        if ( $charged ) {
            $sql = "insert into donations values( null, '$first_name', '$last_name', '$address', '$city', 
                '$state', '$zip', '$country', '$email', '$phone', $amount, '$response', '$reason', 
                '$dedication', '$family', now() )";
            @mysql_query( $sql );    
        
            // send confirmation email
            // if you want to modify who gets this email, then change lines following the BCC
            include_once("classes/send_mail.php");
            include_once("constant_file.php");
            
            $mail_parms = array();
            $mail_parms['to'] = "$email";   
            $mail_parms['subject'] = "Confirmation of Credit Card Transaction";
            $mail_parms['message'] = "A transaction for $" . $amount  ." has been applied to your credit card for Chayolei Tzivos Hashem.";
            $mail_parms['message'] .= "Description: " . $description;
            $mail_parms['headers'] = "BCC:" . $programmers_email . "\r\n" ;
            $mail_parms['headers'] .= "From: DONOTREPLY@mashpia.com\r\nReply-To: DONOTREPLY@mashpia.com". "\r\n" ;
            
            $send_mail = new MailClass();
            $success = $send_mail->send_mail($mail_parms);
        }
?>