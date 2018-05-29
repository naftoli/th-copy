<?php 
session_start(); 
if ( !isset( $_SESSION['hschool'] ) ) 
    header( "Location: admin.php" );
$h_school = $_SESSION['hschool'];

include("db.php");
include("check_admin_id.php");

require_once('classes/authorize/CustomerProfile.php');
use \classes\authorize\CustomerProfile;

require_once('classes/authorize/PaymentProfile.php');
use \classes\authorize\PaymentProfile;

$next_page = "false";

// ***** GET THE ADMIN INFO ***** //
include("camps/includes/classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
$admin->get_school_id();
$school_id = $admin->school_id;

// ***** GET THE ADMIN INFO ***** //

$message = "";
$update_done = false;

// ***** GET PAYMENT INFO ***** //
$authorize_school_ids = mysql_query(
	"SELECT authorize_customer_profile_id as customer_id, authorize_payment_profile_id as payment_id "
	." FROM schools WHERE school_id = ".$admin->school_id
);
// load the info from the DBS
$authorize_school_ids = mysql_fetch_assoc($authorize_school_ids); // fetch the results from the DBS
$customer_id 	= $authorize_school_ids['customer_id'];
$payment_id 	= $authorize_school_ids['payment_id'];

if( $customer_id ) {
	$customer_profile = new CustomerProfile($customer_id);
	$payment_profile = $customer_profile->paymentProfiles[0];
	$cc = $customer_profile->paymentProfiles[0]["payment"]["creditCard"]; // get the CC info from the API response....
} else {
	$cc = false;
}

if (isset($_POST['action'])) {
	$action = $_POST['action'];
	
	foreach ($_POST as $k => $v) {
		$_POST[$k] = mysql_real_escape_string(trim($v));
	}
	
	if ($action == "update_credit_card") {
		$sql = "UPDATE schools SET "
			."cc_first 		='" . clean_character($_POST['cc_first_name']) . "', "
			."cc_last 		='" . clean_character($_POST['cc_last_name']) . "', "
			."cc_address 	='" . clean_character($_POST['cc_address']) . "', "
			."cc_state 		='" . clean_character($_POST['cc_state']) . "', "
			."cc_zip 		='" . clean_character($_POST['cc_zip']) . "', "
			."accounting_name = '" . mysql_real_escape_string($_POST['contact_name']) . "', "
			."accounting_number = '" . mysql_real_escape_string($_POST['contact_number']) . "', "
			."accounting_email = '" . mysql_real_escape_string($_POST['contact_email']) . "' "
			."WHERE school_id=" . $admin->school_id;
		$query = mysql_query($sql);
		// create the bill to array
		$billto = [
			"address" => clean_character($_POST['cc_address']),
			"state" => clean_character($_POST['cc_state']),
			"zip" => clean_character($_POST['cc_zip'])
		];
		
		// make sure the DBS was updated....
		if (!$query) {
			$message = "<span style='color:red;'>Update not performed. Please try again.</span>";			
		}
		else {
			$next_page = "true";
		}
		// check if we have payment info on file....
		if ($customer_id && $authorize_school_ids['payment_id'] && !preg_match("/^X{4}[0-9]{4}$/", $_POST['cc_number']) ) {
			// update the payment profile on file:
			$payment_profile = new PaymentProfile($payment_id, $customer_id);
			$payment_profile->cardNumber 		= clean_character($_POST['cc_number']);
			$payment_profile->expirationDate 	= clean_character($_POST['cc_exp']);
			$payment_profile->cardCode 			= clean_character($_POST['cc_cvv']);
			$payment_profile->billTo = $billto;
			// submit the updated info and check for errors....
			$errors = $payment_profile->update();
			
			if ($errors) {
				$errorCode = $errors['messages']['message'][0]['code'];
				$errorText = $errors['messages']['message'][0]['text'];
				$message = "<span style='color:red;'>Credit Card Error ($errorCode): $errorText</span>";
				$next_page = "false";
			}
		} else if ( !preg_match("/^X{4}[0-9]{4}$/", $_POST['cc_number']) ) { // if we do not have an authorize account on record...
			$payment_profile = PaymentProfile::createBasicArray(
				clean_character($_POST['cc_number']),
				clean_character($_POST['cc_exp']),
				clean_character($_POST['cc_cvv']),
			$billto, true);

			// get the email from the admin
			// create the payment profile
			$customer_profile = CustomerProfile::create("CTH_".$admin->school_id, $admin->admin_email, $admin->first . " " . $admin->last, $payment_profile);
			//// if it is a valid payment profile, update the system. (only bad case is a duplicate which then returns an array)
			if ($customer_profile instanceof CustomerProfile) {
				// insert the ids into the system....
				mysql_query("UPDATE schools SET authorize_customer_profile_id = ". $customer_profile->customerProfileId .
							", authorize_payment_profile_id = " . $customer_profile->paymentProfiles[0]["customerPaymentProfileId"] .
							" WHERE school_id = $admin->school_id"
				);
			} else {
                $message = "<span style='color:red;'>" . $customer_profile['message'] . "</span>";
				$next_page = "false";
			}
		} // end CC updating conditions....
	} // end if the action is update_cc_info...
}
else {
	header("/registration.php");
}

include("classes/school.php");
$sql = "SELECT * FROM schools WHERE school_id=" . $admin->school_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$school = new school($row);

// remove all but alpha numeric & spaces, dot
function clean_character($string)
{
	$new_string = preg_replace("/[^a-zA-Z0-9\.\s]/", "", $string);
	return $new_string;
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" dir="<?=$dir?>">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<title>School Registration</title>
		<link rel="alternate" media="print" href="index.php">
		<link href="admin_styles.css" rel="stylesheet" type="text/css" />
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
		<!--<script type="text/javascript" src="http://jzaefferer.github.com/jquery-validation/jquery.validate.js"></script>-->
		<script src="camps/scripts/jquery.tools.min.js"></script>		
		
		<script>
			var next_page = "<?=$next_page;?>";
			var admin_id = <?=$admin_id;?>;
			var school_id = <?=$school_id;?>;

			$( function(){ 
			    
			    $("#nav").height($("#content").height());
			     
				$('#submit_button').click(function(){ 
					if(perform_validation()){
						return true;
					}
					else{				
						return false;
					}
				});
				
				// generate some test data
				$("#point").click(function(){ 
					test_data();
				}); 
				
			}); 
			
			// number validation
			function number_validation(e) {
				var unicode = e.charCode ? e.charCode : e.keyCode
				if  (unicode != 8 && unicode != 9) {
					if (unicode < 48 || unicode > 57) 
						return false;
				}			
			}
			
			function isAlphabetic(sText) {
				var ValidChars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
				var IsAlphabetic=false;
				var Char;			
				for (i = 0; i < sText.length; i++){
					Char = sText.charAt(i);
					if (ValidChars.indexOf(Char) != -1){
						IsAlphabetic = true;
						break;
					}
				}
				return IsAlphabetic;
			}
			
			// perform validation
			function perform_validation()
			{			
				if ($("#cc_first_name").val() == "") {
					document.getElementById("cc_first_name").focus();
					alert("You must enter First Name as it appears on credit card");
					return false;					
				}
				else if ($("#cc_last_name").val() == "") {
					document.getElementById("cc_last_name").focus();
					alert("You must enter Last Name as it appears on credit card");
					return false;					
				}
				else if ($("#cc_address").val() == "") {
					document.getElementById("cc_address").focus();
					alert("You must enter Address.");
					return false;					
				}
				else if ($("#cc_state").val() == "") {
					document.getElementById("cc_state").focus();
					alert("You must enter State/Province code.");
					return false;					
				}
				else if ($("#cc_zip").val() == "") {
					document.getElementById("cc_zip").focus();
					alert("You must enter Zip/Postal Code.");
					return false;					
				}
				else if ($("#ccnum").val() == "") {
					document.getElementById("ccnum").focus();
					alert("You must enter Valid Credit Card Number.");
					return false;					
                }
                // make sure it is a number or the pre-filled info
				else if(IsNumeric($("#ccnum").val()) == false && !$("#ccnum").val().match(/^X{4}[0-9]{4}$/)) {
					document.getElementById("ccnum").focus();
					alert("Credit Card Must be Numeric.");
					return false;
				}
				else if ($("#ccexp").val() == "") {
					document.getElementById("ccexp").focus();
					alert("You must enter Valid Credit Card Expiry Date.");
					return false;					
				}			
				else if(IsNumeric($("#ccexp").val()) == false && !$("#ccexp").val().match(/^X{4}$/) ) {
					document.getElementById("ccexp").focus();
					alert("Expiry date must be numeric (format MMYY).");
					return false;					
				}
				else if ($("#cc_cvv").val() == "" && !$("#ccexp").val().match(/^X{4}$/) ) {
					document.getElementById("cc_cvv").focus();
					alert("CVV must be entered.");
					return false;					
				}			
				else if(IsNumeric($("#cc_cvv").val()) == false && !$("#ccexp").val().match(/^X{4}$/) ) {
					document.getElementById("cc_cvv").focus();
					alert("CVV must be numeric.");
					return false;					
				}
				
				var cname = $("#contact_name").val().trim();
				var cnumber = $("#contact_number").val().trim();
				var cemail = $("#contact_email").val().trim();
				var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
				
				if (cname == '' || cnumber == '' || cemail == '') {
					alert("You must enter name, number and email for contact person at accounting department.");
					return false;					
				}
				if (cname.length < 3) {
					alert("Contact name must be at least 3 characters.");
						return false;
				}
				if (cnumber.length < 9 || isAlphabetic(cnumber)) {
					alert("Contact number must be at least 9 digits and cannot contain alphabetic characters.");
					return false;
				}
				if (reg.test(cemail) !== true) {
					alert("Invalid contact email address.");
					return false;
				}
				
				return true;
			}
		
			// populate test data
			function test_data() {			
				$('#cc_first_name').val("Mordechai Moshe");
				$('#cc_last_name').val("Mutawalli");
				$('#cc_address').val("12345 Park Avenue");
				$('#cc_state').val("NY");
				$('#cc_zip').val("11213");
				$('#ccnum').val("4111111111111111");
				$('#ccexp').val("0115");
				$('#cccvv').val("123");
			}	

			function check_next_page() {
				if (next_page == "true") {
					var registration_form_eight = document.forms["registration_form_eight"];
					registration_form_eight.elements["admin_id"].value = admin_id;
					registration_form_eight.elements["school_id"].value = school_id;
					registration_form_eight.submit();
				}
			}									
			
			//  check for valid numeric strings	
			function IsNumeric(strString){			
				return parseFloat(strString)==strString;
			}

			
		</script>
		<style type="text/css">
		    label.error {
                color: red;
                font-weight: normal;
                float: left;
                font-size: 12px;
            }
            input.error {
                border: 2px solid red;
            }
		</style>
	</head>

	<body onload="check_next_page();">
		<FORM name="registration_form_eight" method="post" action="registration_8.php">
			<input type="hidden" name="admin_id" value="">
			<input type="hidden" name="school_id" value="">
		</FORM>
	
		<NOSCRIPT>
			<P STYLE="color: red; font-size: larger;">Notice: You have javascript disabled. Some parts of the site will not function without javascript.</P>
		</NOSCRIPT>
		
		<div id="wrapper">
		
			<div id="nav" class="wizard">
			
				<div class="col_title_bg"></div>
				
				<div class="col_title">Menu</div>
				
				<? include("registration_menu.php"); ?>				
			</div>
			
			<div id="content">
				<div class="col_title_bg">
				</div>
				
				<div class="slider_container">
				
					<div class="slider">
					
						<div class="col_title"></div>						
						<div class="col_content left">						
							<h1>School Registration</h1>
	 
							<form action="registration_7.php" id="ccform" method="post" accept-charset="UTF-8">
								<input type="hidden" name="action" value="update_credit_card">
								<input type="hidden" name="school_id" value="<?=$school_id;?>">
								<input type="hidden" name="admin_id" value="<?=$admin_id;?>">

								<? if ($message != "") : ?>
									<h1 style="color:red;"><?=$message;?></h1>
								<? endif; ?>
								
								<h2>Infomation about School's Accounting Department</h2>
								<div class="module" id="module-info">
									<div class="module_content">
										<div class="lists form">
											<ul>
												<li>
													<span class="label"><label for="contact_name">Name of Contact</label></span>
													<span class="input"><input id="contact_name" class="required" name="contact_name" type="text" value="<?=$row['accounting_name']?>"/></span>
												</li>
												<li>
													<span class="label"><label for="contact_number">Contact Number</label></span>
													<span class="input"><input id="contact_number" class="required" name="contact_number" type="text" value="<?=$row['accounting_number']?>"/></span>
												</li>
												<li>
													<span class="label"><label for="contact_email">Email</label></span>
													<span class="input"><input id="contact_email" class="required" name="contact_email" type="text" value="<?=$row['accounting_email']?>"/></span>
												</li>
											</ul>
										</div>
									</div>
								</div>
								
								<h2>School Credit Card Details</h2>
								<div class="module" id="module-info">
									<div class="module_content">
										<div class="lists form">
											<ul>
												<li>
													<input type="hidden" id="cc_amount" value="<?=$total_fee;?>"> 
													<span class="label"><label for="cc_first_name">First Name on Credit Card</label></span>
													<span class="input"><input id="cc_first_name" class="required" name="cc_first_name" type="text" value="<?=$row['cc_first']?>"/></span>
												</li>
												<li>
													<span class="label"><label for="cc_last_name">Last Name on Credit Card</label></span>
													<span class="input"><input id="cc_last_name" class="required" name="cc_last_name" type="text" value="<?=$row['cc_last']?>"  /></span>
												</li>
												<li>
													<span class="label"><label for="cc_address">Address</label></span>
													<span class="input"><input id="cc_address" name="cc_address" type="text" value="<?=$row['cc_address']?>" /></span>
												</li>
												<li>
													<span class="label"><label for="cc_state">State/Province Code</label></span>
													<span class="input"><input id="cc_state" name="cc_state" type="text" value="<?=$row['cc_state']?>" /></span>
												</li>
												<li>
													<span class="label"><label for="cc_zip">Zip/Postal Code</label></span>
													<span class="input"><input id="cc_zip" name="cc_zip" type="text" value="<?=$row['cc_zip']?>" /></span>
												</li>
												<li>
													<span class="label"><label for="ccnum">Credit Card Number</label></span>
													<span class="input">
														<input id="ccnum" class="required creditcard" name="cc_number" type="text" placeholder="<?=$cc ? $cc['cardNumber'] : ""?>"
															value="<?= isset($_POST['cc_number']) ? $_POST['cc_number'] : ( $cc ? $cc['cardNumber'] : "" )?>"/>
													</span>
												</li>
												<li>
													<span class="label"><label for="ccexp">Expiry Date<br>(format MMYY)</label></span>
													<span class="input">
														<input id="ccexp" class="required digits" name="cc_exp" type="text" placeholder="<?=$cc ? $cc['expirationDate'] : ""?>"
															value="<?= isset($_POST['cc_exp']) ? $_POST['cc_exp'] : ( $cc ? $cc['expirationDate'] : "" )?>"/>
													</span>
												</li>
												<li>
													<span class="label"><label for="cccvv">CVV<br>on back of card</label></span>
													<span class="input">
														<input id="cc_cvv" class="required digits" name="cc_cvv" type="text" placeholder = "XXX"
															value="<?= isset($_POST['cc_cvv']) ? $_POST['cc_cvv'] : ""?>"/>
													</span>
												</li>
											</ul>
											</div> 
										</div>
									</div>
										<input type="submit" value="Save & Continue" id='submit_button' class="button" style="float: right">
									</div>
									</form> 
										</div>
										<a ref="#" id='point'>&nbsp;</a>			
									</div>
								</div>
							</form> 				
						</div>				
					</div>				
				</div>				
			</div>								
		</div>
	</body>	
</html>
