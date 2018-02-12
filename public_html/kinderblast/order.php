<?
require '../db.php';

//check for spammers    
include '../check_for_spammers.php';

foreach ($_POST as $k => $v) {
	$_POST[$k] = mysql_real_escape_string($v);
}

$card_num = $_POST['ccnum'];
$exp_date = $_POST['ccexp'];
$amount = $_POST['ccamount'];
$description = $_POST['qty'] . " Pesach booklets purchased.";
$first_name = $_POST['fname'];
$last_name = $_POST['lname'];
$address = isset($_POST['address']) ? $_POST['address'] : '';
$city = isset($_POST['city']) ? $_POST['city'] : '';
$state = isset($_POST['state']) ? $_POST['state'] : '';
$zip = isset($_POST['zip']) ? $_POST['zip'] : '';

require '../../includes/authorize.php';

$response = "";
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
    $response = "There was an error. Please try again.";
}

if ($charged) {	
	// To send HTML mail, the Content-type header must be set
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'From: Tzivos Hashem <cth@jcm.museum>' . "\r\n";
	
	$subject = "Pesach Coloring Books Purchase";	
	
	$message = "Thank you for your purchase of " . $_POST['qty'] . " coloring books.<br />";
	$message .= "You have been charged $" . $amount . ".<br />";
	if ($_POST['choice'] == 2) {
		$message .= "Your order will be ready for pickup at the Museum.<br />Thank you.";
	} else if ($_POST['choice'] == 1) {
		$message .= "Your order will be sent to:<br />" . 
					$address . "<br />" . 
					$city . ", " . $state . " " . $zip . "<br />" . $_POST['country'];
	}
	$to = $_POST['email'];
	mail($to, $subject, $message, $headers);
	
	$message = "Purchase Details:<br />";
	$message .= "name: " . $first_name . " " . $last_name . "<br />
				email: " . $_POST['email'] . "<br /> 
				phone: " . $_POST['phone'] . "<br /> 
				quantity: " . $_POST['qty'] . " booklets<br />
				shipping: ";
	if ($_POST['choice'] == 2) {
		$message .= " Pick up from Museum<br />";
	} else if ($_POST['choice'] == 1) {
		$message .= " Ship to following address:<br />" . 
					$address . "<br />" . 
					$city . ", " . $state . " " . $zip . "<br />" . $_POST['country'];
	}
	$to = "Shimmy@jcm.museum";
	mail($to, $subject, $message, $headers);
	
	header("Location: index.php?m=1");
	exit;	
} else {
	header("Location: index.php?m=" . urlencode($response));
	exit;
}
?>