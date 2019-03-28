
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
  "http://www.w3.org/TR/html4/loose.dtd">
<HTML lang='en'>
<HEAD>
        <TITLE> Authorize.net Credit Card Authorization </TITLE>
</HEAD>
<BODY>

<h3>Authorize.net Credit Card Authorization </h3>
<h4>Live site</h4>
<br>

<form method='post'>
<table>
<tr><td>Amount:</td><td><input name='amount'  type='text'
value='.01'><td></tr>
<tr><td>Description:</td><td><input name='description'  type='text'
value='some description'><td></tr>
<tr><td>Card Number:</td><td><input name='card_num' type='text'
value='4111111111111111'><td></tr>
<tr><td>Expiry Date:</td><td><input name='exp_date'  type='text'
value='0115'><td></tr>
<tr><td>First Name:</td><td><input name='first_name'  type='text'
value='Avi'><td></tr>
<tr><td>Last Name:</td><td><input name='last_name'  type='text'
value='Webb'><td></tr>
<tr><td>Address:</td><td><input name='address'  type='text' value='145
Brooklyn Avenue'><td></tr>
<tr><td>State:</td><td><input name='stateCode'  type='text'
value='NY'><td></tr>
<tr><td>Zip:</td><td><input name='zip'  type='text'
value='11213'><td></tr>
<tr><td><input name='submit'  type='submit' value='Submit Now'><td></tr>
</table>
</form>

<?php

require_once 'db.php';

$submit = $_POST['submit'];

if ($submit)
{
        foreach ($_POST as $k => $v) {
			$_POST[$k] = mysql_real_escape_string(trim($v));
		} 	
        post_to_authorize_net();
}

//------------------------------------------------------------------------------
// authorize.net script
//------------------------------------------------------------------------------
function post_to_authorize_net()
{
        // By default, this sample code is designed to post to our test server for
        // developer accounts: https://test.authorize.net/gateway/transact.dll
        // for real accounts (even in test mode), please make sure that you are
        // posting to: https://secure.authorize.net/gateway/transact.dll

        $post_url = "https://secure.authorize.net/gateway/transact.dll";

        $amount = $_POST['amount'];
        $description = $_POST['description'];
        $card_num = $_POST['card_num'];
        $exp_date = $_POST['exp_date'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $address = $_POST['address'];
        $state = $_POST['state'];
        $zip = $_POST['zip'];

       
        $post_values = array(
               
                // the API Login ID and Transaction Key must be replaced with valid values
               
                // for testing:
                // "x_login"                       => "75sqQ96qHEP8",
                // "x_tran_key"            => "7r83Sb4HUd58Tz5p",

                // live site:
                "x_login"         => "4FW7gsD8Tr",
                "x_tran_key"      => "6f7z4c79NMLU4293",

                "x_version"             => "3.1",
                "x_delim_data"          => "TRUE",
                "x_delim_char"          => "|",
                "x_relay_response"      => "FALSE",
         
                "x_type"                        => "AUTH_CAPTURE",
                "x_method"                      => "CC",
                "x_card_num"            => $card_num ,
                "x_exp_date"            => $exp_date ,

                "x_amount"                      => $amount,
                "x_description"         => $description,

                "x_first_name"          => $first_name,
                "x_last_name"           => $last_name,
                "x_address"                     => $address,
                "x_state"                       => $state,
                "x_zip"                         => $zip
               
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

        // This line takes the response and breaks it into an array using the specified delimiting character
        $response_array = explode($post_values["x_delim_char"],$post_response);

        // The results are output to the screen in the form of an html numbered list.
        echo "<OL>\n";
       
        echo "Response code" . $response_array[0] . " = ";
       
        switch ($response_array[0]) {
    case 1:
        echo "Approved";
        break;
    case 2:
        echo "Declined";
        break;
    case 3:
        echo "Error";
        break;
        case 4:
        echo "Held for Review";
        break;          
}
        echo "<br>";
                       
        echo "Message: " . $response_array[3] . "<br>";
        echo "Authorization code: " . $response_array[4] . "<br>";
        echo "Transaction ID: " . $response_array[6] . "<br>";
        echo "<br><br><br><br>";
       
        foreach ($response_array as $value)
        {
                echo "<LI>" . $value . " </LI>\n";
        }
        echo "</OL>\n";
        // individual elements of the array could be accessed to read certain response
        // fields.  For example, response_array[0] would return the Response Code,
        // response_array[2] would return the Response Reason Code.
        // for a list of response fields, please review the AIM Implementation Guide
}      
       
?>


<!--
<br>
To view transactions goto site:<br>
<a href="https://test.authorize.net/">https://test.authorize.net/</a>
<br><br>
Login ID: cnptest8152010<br>
Password: Authnet001<br>
<a
href="http://developer.authorize.net/guides/AIM/Transaction_Response/Fields_in_the_Payment_Gateway_Response.htm">response
codes</a>
-->

</BODY>
</HTML>