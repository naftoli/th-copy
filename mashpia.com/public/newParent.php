<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<style>
			th, td {
				padding: 5px;
			}
			fieldset {
				margin-bottom: 20px;
				-moz-border-radius: 20px;
                -webkit-border-radius: 20px;
                border-radius: 20px;
                width: auto;
                padding: 10px;
			}
			legend {
				margin-left: 20px;
				font-size: 20px;
				font-style: italic;
				font-weight: bold;
			}
			#info {
				width: 650px;
				margin: auto;
				font-family: "sans-serif";
				font-size: 14px;
			}
		</style>	
	</head>
	
	<body>
	
	<div id="info">
		
		<p>
			<img src="https://myshliachcom.clhosting.org/media/images/570/DKoj5703940.jpg" 
				alt="" width="650" height="200" />
		</p>
	
		<h1>New Parent Account</h1>

		<form method="post" action="newParentPost.php" accept-charset="UTF-8" name="submit"  onsubmit="return validation();"> 								
			
			<fieldset>
				<legend>Create a Username</legend>
				<table>
					<tr>
						<td>Username</td>
						<td><input name="username" type="text"  id="username" /></td>
					</tr>
					<tr>
						<td>Password</td>
						<td><input name="password" type="password" id="password" /></td>
					</tr>
					<tr>
						<td>Confirm Password</td>
						<td><input name="password2" type="password"  id="password2" /></td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<legend>Personal Info</legend>
				<p><i>All fields are mandatory</i></p>
				<table>
					<tr>
						<td style='vertical-align: top'>Shliach</td>
						<td>
							<input type="radio" name="shliach" value="1" class="shliach" /> Yes<br />
							<input type="radio" name="shliach" value="0" class="shliach" /> No
						</td>
					</tr>
					<tr>
						<td>First Name</td>
						<td><input name="first" type="text" id='first' /></td>
					</tr>
					<tr>
						<td>Last Name</td>
						<td><input name="last" type="text" id='last' /></td>
					</tr>
					<tr>
						<td>Address</td>
						<td><input name="admin_address1" type="text" id='address' /></td>
					</tr>
					<tr>
						<td>Address 2</td>
						<td><input name="admin_address2" type="text" id='address2' /></td>
					</tr>
					<tr>
						<td>City</td>
						<td><input name="admin_city" type="text" id='city' /></td>
					</tr>
					<tr>
						<td>State</td>
						<td><input name="admin_state" type="text" id='state' /></td>
					</tr>
					<tr>
						<td>Zip</td>
						<td><input name="admin_postal" type="text" id='zip' /></td>
					</tr>
					<tr>
						<td>Country</td>
						<td><input name="admin_country" type="text" id='country' /></td>
					</tr>
					<tr>
						<td>Home Phone</td>
						<td><input name="admin_phone_home" type="text" id='hphone' /></td>
					</tr>
					<tr>
						<td>Cell Phone</td>
						<td><input name="admin_phone_mobile" type="text" id='cphone' /></td>
					</tr>
					<tr>
						<td>Email Address</td>
						<td><input name="admin_email" type="text" id='last' /></td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<legend>TH WhatsApp</legend>
				<p>Tzivos Hashem Whatsapp broadcast list:</p>
				<input type="radio" name="whatsapp" value="1" /> Please add me to the Tzivos Hashem Whatsapp broadcast list to receive updates and reminders<br />
				<input type="radio" name="whatsapp" value="0" /> No thank you, I would not like to be added to the Tzivos Hashem Whatsapp broadcast list
			</fieldset>
			
			<fieldset>
				<legend>Children</legend>
				<p id="children">
					Number of children registering: 
					<select name="numChildren" id="numChildren">
						<? for ($i = 1; $i < 11; $i++) {
							echo "<option value='" . $i . "'>" . $i . "</option>";
						} ?>
					</select><br />
					<div id="childrenNames">
						Name #1: <input type="text" name="children[]" />
					</div>
				</p>
			</fieldset>
			
			<fieldset>
				<legend>Program Tutorial</legend>
				<input type="radio" name="tutorial" value="0" /> I am familiar with the program and know how to run it<br />
				<input type="radio" name="tutorial" value="1" /> I would like to have a tutorial about the program
			</fieldset>
			
			<fieldset>
				<legend>Additional Programs</legend>
				<input type="checkbox" name="chavrusaEn" /> The Ach/Achos Sheli Chavrusa program (English)<br />
				<input type="checkbox" name="chavrusaHe" /> The Ach/Achos Sheli Chavrusa program (Hebrew)<br />
				<input type="checkbox" name="library" /> The Yaldei Hashluchim Book Library<br />
				<input type="checkbox" name"birthday" /> The Birthday Zone<br />
				<input type="checkbox" name="mishmor" /> The Online Mishmor Program
			</fieldset>
			
			<fieldset id="shipping">
				<legend>Shipping</legend>
				<p>Shipping Options</p>
				<input type="radio" name="shipping" value="1" /> Option 1: shipping of magazines, medals and rank books<br />
				<input type="radio" name="shipping" value="2" /> Option 2: shipping of medals and rank books, pick up magazines from our office
				<p>Shipping Location</p>
				<input type="radio" name="shipLocation" value="1" /> USA<br />
				<input type="radio" name="shipLocation" value="2" /> Canada<br />
				<input type="radio" name="shipLocation" value="3" /> International
			</fieldset>
			
			<fieldset id='cc'>
				<legend>Credit Card Info</legend>
				<table>
					<tr>
						<td colspan="2">Total being charged: <span class='total'></span></td>
					</tr>
					<tr>
						<td>Credit Card Type</td>
						<td> 
							<select name='cctype'> 
								<option value='mc'>MasterCard</option>
								<option value='visa'>Visa</option>
								<option value='amex'>Amex</option>
								<option value='disc'>Discover</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>Name on Credit Card</td>
						<td><input type='text' name='ccname' id='ccname' /></td>
					</tr>
					<tr>
						<td>Credit Card Number</td>
						<td><input type='text' name='ccnum' id='ccnum' size="40"
							value="<?=isset($_POST['ccnum']) ? $_POST['ccnum'] : ''?>" /></td>
					</tr>
					<tr>
						<td>Expiry</td>
						<td>
							<select name='mm' id='mm'>
								<? for ($i = 1; $i < 13; $i++) {
									$val = (string)$i;
									if (strlen($val) == 1)
										$val = '0' . $val;
									if (isset($_POST['mm']) && $_POST['mm'] == $val) {
										echo "<option value=$val selected='selected'>$val</option>";
									} else {
										echo "<option value=$val>$val</option>";
									}
								} ?>
							</select>
							<select name='yy' id='yy'>
								<? for ($i = 2015; $i < 2021; $i++) {
									if (isset($_POST['yy']) && $_POST['yy'] == $i) {
										echo "<option value=$i selected='selected'>$i</option>";
									} else {
										echo "<option value=$i>$i</option>";
									} 
								} ?>
							</select>
						</td>
					</tr>
					<tr>
						<td>Security Code</td>
						<td><input type='text' name='scode' id="scode" size='3' /></td>
					</tr>
					<tr>
						<td>Billing Zip Code</td>
						<td><input type='text' name='zcode' id='zcode' size='5' /></td>
					</tr>
					<tr>
						<td>Email (for confirmation email)</td>
						<td><input type='email' name='email' id='email' size='30' /></td>
					</tr>
					<tr>
						<td colspan="2">
							<input type='checkbox' name='agree' id='agree' /> 
							I allow Tzivos Hashem to charge my credit card <span class='total'></span>
							<input type='hidden' name='total' id='total' value='0' />
						</td>
					</tr>
				</table>
			</fieldset>	
			
			<input type="submit" id="Continue" value="Create Account" class="button" > 
			
		</form>
		
		</div> 
				
	</body>
	
	<script src="jquery.js" type="text/javascript"></script>
	
	<script>
		// validate input	
		function validation(){
		
			var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
			var address = document.getElementById('admin_email').value;
			
			if (document.getElementById('first').value == '') {
				document.getElementById('first').focus();
				alert("All fields are mandatory.");
				return false;
			}	
			else if (document.getElementById('last').value == '') {
				document.getElementById('last').focus();
				alert("All fields are mandatory.");
				return false;
			}
			else if (document.getElementById('admin_phone_home').value == '') {
				document.getElementById('admin_phone_home').focus();
				alert("All fields are mandatory.");
				return false;
			}
			else if (document.getElementById('admin_email').value == '') {
				document.getElementById('admin_email').focus();
				alert("All fields are mandatory.");
				return false;
			}
			else if  (reg.test(address) != true) {					
				document.getElementById('admin_email').focus();
				alert("Invalid email address.");
				return false;				
			}
			else if (document.getElementById('username').value == '') {
				document.getElementById('username').focus();
				alert("All fields are mandatory.");
				return false;
			}
			else if (!isAlphaNumeric(document.getElementById('username').value)) {
				document.getElementById('username').focus();
				alert("Username can only contain letters and numbers.");
				return false;
			}
			
			// check if username is already used																 
			else if (username_not_duplicate(document.getElementById('username').value) ) {
				document.getElementById('username').focus();
				alert("Duplicate username. Please use another username");
				return false;
			}			
			// check if email address already used																 
			else if (email_not_duplicate(document.getElementById('admin_email').value) ) {
				document.getElementById('username').focus();
				alert("Duplicate email address. If you already have an account, you may login directly at mashpia.com.");
				return false;
			}			
			else if (document.getElementById('password').value != document.getElementById('password2').value) {
				document.getElementById('password').focus();
				alert("Passwords do not match.");
				return false;
			}
			else if (document.getElementById('password').value == "") {
				document.getElementById('password').focus();
				alert("All fields are mandatory.");
				return false;
			} 
			else if (document.getElementById('admin_address1').value == "") {
				document.getElementById('admin_address1').focus();
				alert("All fields are mandatory.");
				return false;
			} 
			else if (document.getElementById('admin_city').value == "") {
				document.getElementById('admin_city').focus();
				alert("All fields are mandatory.");
				return false;
			} 
			else if (document.getElementById('admin_state').value == "") {
				document.getElementById('admin_state').focus();
				alert("All fields are mandatory.");
				return false;
			} 
			else if (document.getElementById('admin_postal').value == "") {
				document.getElementById('admin_postal').focus();
				alert("All fields are mandatory.");
				return false;
			} 
			else
			{
				// document.forms["login"].submit();
			}
		}
						
		function username_not_duplicate(username) {
		   //var function_name = "get_username"; 
		   var function_name = "is_username_duplicate"; 
		   var parameters = [username]; 
		   var url = "camps/includes/get_functions.php?function_name=" + function_name + "&parameters=" + parameters;			   
		   var rslt = false; 
		   $.ajax({ 
				 async: false, 
				 url: url, 
				 dataType: "json", 
				 success: function(data) {					 
				   if (data == true) {					 
					 rslt = true; 
				   }
				}, 
			});
			return rslt; 
		}
		
		function email_not_duplicate(email) {
		   //var function_name = "get_username"; 
		   var function_name = "is_email_duplicate"; 
		   var parameters = [email]; 
		   var url = "camps/includes/get_functions.php?function_name=" + function_name + "&parameters=" + parameters;			   
		   var rslt = false; 
		   $.ajax({ 
				 async: false, 
				 url: url, 
				 dataType: "json", 
				 success: function(data) {					 
				   if (data == true) {					 
					 rslt = true; 
				   }
				}, 
			});
			return rslt; 
		}
		
		
		function isAlphaNumeric(sText)	{
			var ValidChars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890* ";
			var IsAlphabetic=true;
			var Char;			
			for (i = 0; i < sText.length; i++){
				Char = sText.charAt(i);
				if (ValidChars.indexOf(Char) == -1){
				IsAlphabetic = false;
				}
			}
			return IsAlphabetic;
		} 
		
		$( function() {
			$("#shipping").hide();
			
			$(".shliach").click( function() {
				var val = $(this).val();
				if (val == 1) {
					$("#shipping").hide();
				} else if (val == 0) {
					$("#shipping").show();
				}
			});
			
			$("#numChildren").change( function() {
				$("#childrenNames").empty();
				var val = $(this).val();
				var str = "";
				for (var i = 1; i <= val; i++) {
					str += "Name #" + i + ": <input type='text' name='children[]' /><br />";
				}
				$("#childrenNames").html(str);
			});
		});			
	</script>
</html>