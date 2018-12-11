<?
include("../db.php");

//check for spammers    
include '../check_for_spammers.php';

foreach ($_POST as $k => $v) {
	$_POST[$k] = mysql_real_escape_string(trim($v));
}

$card_num = $_POST['ccnum'];
$exp_date = $_POST['exp'];
$amount = $_POST['total'];
$description = " Yud Aleph Nissan Rally " . $_POST['year'] . " seat reservation by " . $_POST['name'] . " family for " . 
	($_POST['boys'] ? $_POST['boys'] . " boy seats, " : '') .  
	($_POST['girls'] ? $_POST['girls'] . " girl seats." : '');

$first_name = '';
$last_name = $_POST['ccname'];

$address = '';
$state = '';
$zip = $_POST['zip'];

require_once '../../includes/authorize.php';

$response = '';
$charged = false;
if ($response_array) {
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

//save to reservations database
$res_number = 1;
$desc = "Yud Aleph Nissan 5776";
if ( $charged ) {
    $sql = "insert into reservations
    		set family = '" . $_POST['name'] . "', 
    		email = '" . $_POST['email'] . "', 
    		phone = '" . $_POST['phone'] . "', 
    		boys = " . (int)$_POST['boys'] . ", 
    		girls = " . (int)$_POST['girls'] . ", 
    		auth_code = '" . $response . "', 
    		description = '" . $desc . "', 
			res_number = " . $res_number;
	//echo $sql;
    @mysql_query( $sql );    

    // send confirmation email
    // if you want to modify who gets this email, then change lines following the BCC
    include_once("../classes/send_mail.php");
    include_once("../constant_file.php");
    
    $mail_parms = array();
    $mail_parms['to'] = $_POST['email'];   
    $mail_parms['subject'] = "Confirmation of Credit Card Transaction";
    $mail_parms['message'] = "You have been charged $" . $amount  ." for the " . $description;
    $mail_parms['headers'] = "BCC:" . $programmers_email . "\r\n" ;
    $mail_parms['headers'] .= "From: rally@tzivoshashem.org\r\nReply-To: cth@tzivoshashem.org". "\r\n" ;
    
    $send_mail = new MailClass();
    $success = $send_mail->send_mail($mail_parms);
}

echo json_encode($response); 
?>