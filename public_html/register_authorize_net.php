<?php

include("db.php");

include 'check_for_spammers.php'; // just blocks one ip address for now. (39.53.201.236)

foreach ($_GET as $k => $v) {
	$_GET[$k] = mysql_real_escape_string(trim($v));
}

$school_id 		= mysql_real_escape_string(isset($_GET['school_id'])? $_GET['school_id'] : "");
$amount 		= mysql_real_escape_string($_GET['cc_amount']);
$card_num 		= mysql_real_escape_string($_GET['ccnum']);
$exp_date 		= mysql_real_escape_string($_GET['ccexp']);
$first_name 	= mysql_real_escape_string($_GET['cc_first_name']);
$last_name 		= mysql_real_escape_string($_GET['cc_last_name']);
$email 			= mysql_real_escape_string(isset($_GET['email']) ? $_GET['email'] : "");
$address 		= mysql_real_escape_string($_GET['cc_address']);
$state 			= mysql_real_escape_string($_GET['cc_state']);
$zip 			= mysql_real_escape_string($_GET['cc_zip']);
$description 	= mysql_real_escape_string($_GET['cc_description']);

//if ($school_id != 82) {
    
    require 'authorize.php'; // loads ../includes/authorize.php
	
	//print_r($response_array);
    
    // ------------------------------------------------------------------------------------------------------
    // break out response from payment provider.
    // ------------------------------------------------------------------------------------------------------
    $response = "";
    //variable to know whether to send email
    $charged = false;
    
    if($response_array)
    {
    	// include("constant_file.php");
    	// @mail($programmers_email2, 'Credit Card transaction attempted', serialize($response_array) );
    	// $response = $response_array[0] . "\n";
    
    	
    	// ***** SUCCESSFULL **** //
    	if ($response_array[0] == 1) {	
    		$response = $response . $response_array[0] . "\n";
    		$response = $response . $response_array[3] . "\n";
    		$response = $response . $response_array[4] . "\n";
    		$response = $response . $response_array[6] . "\n";
    		$response = $response . $response_array[9] . "\n";
    		$charged = true;
    	}
    	else {
    		$response .= $response_array[3] . "\n";
    	
    		// -> to test payment network's response 
    		// foreach ($response_array as $value)
    		// {
    			 // echo "<LI>" . $i ." " . $value . " </LI>\n";
    			 // $i++;
    		// }			
    	}
		
		echo $response;
    	
    	if ($charged) {
    		// write record to to SQL transactions table:
    		$sql = "INSERT INTO transactions SET
    				user_id = '$email',
    				school_id = '$school_id',
    				trans_date = CURRENT_TIMESTAMP ,
    				description = '$description',
    				amount = '$amount',
    				first = '$first_name',
    				last = '$last_name',
    				address = '$address',
    				state = '$state',
    				zip = '$zip',
    				response = '$post_response' ";
    			 
    		$result = mysql_query($sql);
    		
    		if (!$result) {
    			include("constant_file.php");
    			@mail($programmers_email2, 'Insert into transactions failed', 'Error: ' . $sql . " -- ". mysql_error() );
    			//	die('Error: ' . mysql_error());		
    			die();
    		}
    	}
    }
//} else {
//    $response = "1\nsuccessful";
//}
//echo $response;	
	

// send confirmation email
// if you want to modify who gets this email, then change lines following the BCC
if ($charged) {								
	include_once("classes/send_mail.php");
	include_once("constant_file.php");
    
	$mail_parms = array();
	$mail_parms['to'] = "$email";	
	$mail_parms['subject'] = "Confirmation of Credit Card Transaction";
	$mail_parms['message'] = "A transaction for $" . $amount  ." has been applied to your credit card for the Chayolei Tzivos Hashem School Program.";
	$mail_parms['message'] .= "Description: " . $description;
	$mail_parms['headers'] = "BCC:" . $programmers_email . "\r\n" ;
	$mail_parms['headers'] .= " " . $programmers_email2 . "\r\n" ;
	$mail_parms['headers'] .= "From: DONOTREPLY@mashpia.com\r\nReply-To: DONOTREPLY@mashpia.com". "\r\n" ;
	
	$send_mail = new MailClass();
	$success = $send_mail->send_mail($mail_parms);
}
?>