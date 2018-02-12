<?
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
	header("Location: https://mashpia.com/teves.php");
}

$msg = "";
$showForm = true;
if (isset($_POST['submit'])) {
	
	if (!strpos($_POST['email'], '@')) {
		$msg .= "Your email address is invalid. Please try again.<br />";
		
	}
	if ($_POST['shipped']) {
		if (empty($_POST['address']) || empty($_POST['city']) || empty($_POST['state']) || empty($_POST['zip'])) {
			$msg .= "You have indicated that you would like to have your order shipped.<br >";
			$msg .= "Please fill out complete mailing info.<br />";
		}
	}
	
	$cc = $_POST['ccnum'];
	$exp = $_POST['ccexp'];
	if (!is_numeric($cc) || !is_numeric($exp)) {
		$msg .= "Your credit card number as well as your expiry date can only contain digits with no spaces.<br />Please try again.";
	}
	
	if ($msg == '') {
		$_POST['ccamount'] = $_POST['cd'] * 14.99;
		$_POST['country'] = 'USA';
		$_POST['desc'] .= " - purchased by " . $_POST['fname'] . ' ' . $_POST['lname'] . ", bought " . $_POST['cd'] . "cd(s)";
		if ($_POST['shipped']) $_POST['desc'] .= " - wants it shipped.";
		else $_POST['desc'] .= " - will pick up from Museum.";
		
		require_once 'authorize_teves.php';
		if ($response_array[0] != 1) {
			$msg .= $response;
		} else {
			$msg .= "You have successfully purchased " . $_POST['cd'] . " CD's.<br />Thank you for your order.<br />You should be receiving an email confirmation shortly";
			$showForm = false;
			
			//insert transaction into db
			$name = mysql_real_escape_string($_POST['fname'] . ' ' . $_POST['lname']);
			$email = mysql_real_escape_string($_POST['email']);
			$phone = mysql_real_escape_string($_POST['phone']);
			$qty = mysql_real_escape_string($_POST['cd']);
			$method = $_POST['shipped'] ? 'ship' : 'pickup';
			$address = mysql_real_escape_string($_POST['address']);
			$city = mysql_real_escape_string($_POST['city']);
			$state = mysql_real_escape_string($_POST['state']);
			$zip = mysql_real_escape_string($_POST['zip']);
			$sql = "insert into cd_purchases values(null, '$name', '$email', '$phone', $qty, '$response', now(), '', '$method', '$address', '$city', '$state', '$zip')";
			@mysql_query($sql);
			
			//send email confirmation
			$to = $_POST['email'];
			$subject = "Your purchase from Tzivos Hashem";
			$message = "Thank you " . $_POST['fname'] . ' ' . $_POST['lname'] . " for your purchase of " . $_POST['cd'] . " CD's.";
			if ($_POST['shipped']) {
				$message .= " Your order will soon be shipped.";
			}
			$headers = "Cc: shimmy@jcm.museum, cth@tzivoshashem.org" . "\r\n";
			$headers .= "Reply-To: cth@tzivoshashem.org";
			@mail($to, $subject, $message, $headers);
		}
	}
}
?>
<html>
	<head>
		<script type="text/javascript" src="//code.jquery.com/jquery-1.10.2.min.js"></script>
		<script>
			$(function() {
				$("#shipping").hide();
				
				$(".shipped").click( function() {
					if ($(this).val() == 1) {
						$("#shipping").show();
					} else {
						$("#shipping").hide();
					}
				});
				
				$("#cd").blur( function() {
					var num = $(this).val();
					var total = num * 14.99;
					if (!isNaN(total)) {
						$("#ccamount").text(total);
					}
				});
				
				$("#submit").click( function() {
					if ($("#shipping").attr('style').indexOf('block') != -1) {
						var address = $("#address").val();
						var city = $("#city").val();
						var state = $("#state").val();
						var zip = $("#zip").val();
						if (address == '' || city == '' || state == '' || zip == '') {
							alert("You have indicated that you want your CD's shipped.\nPlease fill out all shipping info.");
							$("#address").focus();
							return false;
						}
					}
				});
			});
		</script>
		
		<style>
			body {
				margin-left: 30%;
				margin-right: 30%;
				line-height: 1.6;
				font-family: Arial, Helvetica, sans-serif;
			}
			form label {
				width: 160px;
				float: left;	
			}
			form div {
				padding: 5px;
			}
			fieldset {
				margin: 20 0;
				-moz-border-radius: 10px;
                -webkit-border-radius: 10px;
                border-radius: 10px;
			}
			legend {
				margin-left: 10px;
				padding: 0 6;
			}
			.msg {
				color: red;
			}
		</style>
	</head>
	
	<body>
		<div align="center">
			<img src="images/24Teves.jpg" width="300px" /><br />
			<audio src="downloads/Trailer Chof Daled Teves.m4a" controls>
			    Your browser does not support the <video> element.   
			</audio>
		</div>
		<br />
		
		<? 
		if (!empty($msg)) {
			echo "<div class='msg'>" . $msg . "</div>";
			if (!$showForm) {
				exit;
			}
		} 
		?>
				
		<form action="teves.php" method="post" id="form">
			
			<div>
				Each CD costs $14.99.<br />
			    You can pick it up at the Museum or have it shipped within the Continental USA.<br />
			    If you choose to have it shipped, shipping charges will apply!
			</div>
			
			<fieldset>
				<legend>Personal Info</legend>
			    <div>
			        <div>
			            <label for="fname">First Name</label>
			            <input name="fname" type="text" placeholder="First Name" required="required" 
			            value="<?=isset($_POST['fname'])?$_POST['fname']:''?>" />
			        </div>
			        
			        <div>
			            <label for="lname">Last Name</label>
			            <input name="lname" type="text" placeholder="Last Name" required="required"
			            value="<?=isset($_POST['lname'])?$_POST['lname']:''?>" />
			        </div>
			        
			        <div>
			            <label for="email">Email</label>
			            <input name="email" type="text" placeholder="Email" required="required" size="50"
			            value="<?=isset($_POST['email'])?$_POST['email']:''?>" />
			        </div>
			        
			        <div>
			            <label for="phone">Phone</label>
			            <input name="phone" type="text" placeholder="Phone Number" required="required"
			            value="<?=isset($_POST['phone'])?$_POST['phone']:''?>" />
			        </div>
		       	</div>
	      	</fieldset>
	      	
	      	<div>
	      		<input type="radio" name="shipped" class="shipped" value="0" checked="checked"> I will pick it up from the Museum.<br />
				<input type="radio" name="shipped" class="shipped" value="1" /> I want to have it shipped.
	      	</div>
	      	
	      	<fieldset id="shipping">
	      		<legend>Shipping Info</legend>
	      		<div>					
					<div>
			            <label for="address">Address</label>
			            <input name="address" id="address" type="text" placeholder="Address" size="50"
						value="<?=isset($_POST['address'])?$_POST['address']:''?>" />
			        </div>
			        
			        <div>
			            <label for="city">City</label>
			            <input name="city" id="city" type="text" placeholder="City" size="20"
			            value="<?=isset($_POST['city'])?$_POST['city']:''?>" />
			        </div>
			        
			        <div>
			            <label for="state">State</label>
			            <input name="state" id="state" type="text" placeholder="State" size="10" 
			            value="<?=isset($_POST['state'])?$_POST['state']:''?>" />
			        </div>
			        
			        <div>
			            <label for="zip">Zip</label>
			            <input name="zip" id="zip" type="text" placeholder="Zip" size="6" 
			            value="<?=isset($_POST['zip'])?$_POST['zip']:''?>" />
			        </div>
			    </div>
			</fieldset>
			
			<fieldset>
				<legend>Payment Info</legend>    
			    <div>
			        <div >
			            <label for="cd">Number of CDs</label>
	            		<input name="cd" id="cd" type="text" placeholder="#CDs" size="3" required="required" 
	            		value="<?=isset($_POST['cd'])?$_POST['cd']:''?>" />
			        </div>
			        
	            	<div>
			            <label for="cctype">Credit Card Type</label>
	            		<select name="cctype">
	            			<option value="mc" selected="selected">MasterCard</option>
	            			<option value="visa">Visa</option>
	            			<option value="amex">American Express</option>
	            			<option value="discover">Discover</option>
	            			<option value="dinersclub">Diners Club</option>
	            		</select>
			        </div>
			        <div>
			            <label for="ccnum">Credit Card Number</label>
	            		<input name="ccnum" type="text" placeholder="Credit Card Number" required="required">
			        </div>
			        <div>
			            <label for="ccexp">Expiration Date</label>
	            		<input name="ccexp" type="text" placeholder="MMYY" required="required" size="4">
			        </div>
				</div>
			</fieldset>
			
			Total: $<span id="ccamount">0.00</span>
			<br /><br />			
			<input type="hidden" name="desc" value="Chod Daled Teves CD Purchase" />
			<button id="submit" name="submit" type="submit">Submit</button>
		</form>
	</body>
</html>