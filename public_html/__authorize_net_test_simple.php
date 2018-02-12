<!--
This sample code is designed to connect to Authorize.net using the SIM method.
For API documentation or additional sample code, please visit:
http://developer.authorize.net

Most of this page can be modified using any standard html. The parts of the
page that cannot be modified are noted in the comments.  This file can be
renamed as long as the file extension remains .php
-->

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" 
  "http://www.w3.org/TR/html4/loose.dtd">
<HTML lang='en'>
<HEAD>
	<TITLE> Sample SIM Implementation </TITLE>
</HEAD>
<BODY>
  
<!-- This section generates the "Submit Payment" button using PHP           -->
<?PHP
// This sample code requires the mhash library for PHP versions older than
// 5.1.2 - http://hmhash.sourceforge.net/
	
// the parameters for the payment can be configured here
// the API Login ID and Transaction Key must be replaced with valid values
// $loginID		= "API_LOGIN_ID";
// $transactionKey = "TRANSACTION_KEY";

// live
$loginID			= "4FW7gsD8Tr";
$transactionKey 	= "6f7z4c79NMLU4293";

// test 
$loginID			= "75sqQ96qHEP8";
$transactionKey 	= "7r83Sb4HUd58Tz5p";

$amount 		= "19.99";
$description 	= "Sample Transaction";
$label 			= "Submit Payment"; // The is the label on the 'submit' button
$testMode		= "false";
// By default, this sample code is designed to post to our test server for
// developer accounts: https://test.authorize.net/gateway/transact.dll
// for real accounts (even in test mode), please make sure that you are
// posting to: https://secure.authorize.net/gateway/transact.dll
$url			= "https://test.authorize.net/gateway/transact.dll";

// If an amount or description were posted to this page, the defaults are overidden
if ($_REQUEST["amount"])
	{ $amount = $_REQUEST["amount"]; }
if ($_REQUEST["description"])
	{ $description = $_REQUEST["description"]; }

// an invoice is generated using the date and time
$invoice	= date(YmdHis);
// a sequence number is randomly generated
$sequence	= rand(1, 1000);
// a timestamp is generated
$timeStamp	= time ();

// The following lines generate the SIM fingerprint.  PHP versions 5.1.2 and
// newer have the necessary hmac function built in.  For older versions, it
// will try to use the mhash library.
if( phpversion() >= '5.1.2' )
{	$fingerprint = hash_hmac("md5", $loginID . "^" . $sequence . "^" . $timeStamp . "^" . $amount . "^", $transactionKey); }
else 
{ $fingerprint = bin2hex(mhash(MHASH_MD5, $loginID . "^" . $sequence . "^" . $timeStamp . "^" . $amount . "^", $transactionKey)); }

// Print the Amount and Description to the screen.
echo "Amount: $amount <br />";
echo "Description: $description <br />";

// Create the HTML form containing necessary SIM post values
echo "<FORM method='post' action='$url' >";
// Additional fields can be added here as outlined in the SIM integration guide
// at: http://developer.authorize.net
echo "	<INPUT type='hidden' name='x_login' value='$loginID' />";
echo "	<INPUT type='hidden' name='x_amount' value='$amount' />";
echo "	<INPUT type='hidden' name='x_description' value='$description' />";
echo "	<INPUT type='hidden' name='x_invoice_num' value='$invoice' />";
echo "	<INPUT type='hidden' name='x_fp_sequence' value='$sequence' />";
echo "	<INPUT type='hidden' name='x_fp_timestamp' value='$timeStamp' />";
echo "	<INPUT type='hidden' name='x_fp_hash' value='$fingerprint' />";
echo "	<INPUT type='hidden' name='x_test_request' value='$testMode' />";
echo "	<INPUT type='hidden' name='x_show_form' value='PAYMENT_FORM' />";
echo "	<input type='submit' value='$label' />";
echo "</FORM>";
?>
<!-- This is the end of the code generating the "submit payment" button.    -->

</BODY>
</HTML>