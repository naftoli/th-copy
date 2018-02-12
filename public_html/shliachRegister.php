<?
session_start();

$str = substr($_SERVER['SCRIPT_URI'], 4, 1);
if ($str != 's') {
	header("Location: https://mashpia.com/shliachRegister.php");
}

if (isset($_GET['load']) && $_GET['load']) {
	$load = true;
} else {
	$load = false;
}
if ($load && isset($_GET['error'])) {
	$_POST = $_SESSION['post'];
}
require 'db.php';
$grades = array();
$sql = "select * from classes where class_era = 0 and school_id = 61";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$grades[$row['class_id']] = $row['class_grade'];
}
$year = 5777;
$fee = 20;
?>
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
				width: auto;
				margin: auto;
				font-family: "sans-serif";
				font-size: 14px;
			}
			.star {
				color: red;
				padding: 2px;
			}
			.error {
				color: red;
				font-weight: bold;
			}
			small {
				font-size: 12px;
				color: grey;
				font-style: italic;
			}
			.small {
				font-size: 12px;
				font-style: italic;
				color: grey;
			}
		</style>
	</head>
	
	<body>
	
	<div id="info">
		<!--
		<p>
			<img src="https://myshliachcom.clhosting.org/media/images/570/DKoj5703940.jpg" 
				alt="" width="650" height="200" />
		</p>
		-->
		<h1 id="header">New Parent Account</h1>
		 
		<?
		if (isset($_GET['error'])) {
			echo "<div class='error'>" . urldecode($_GET['error']) . "</div><br />";
		} else if (isset($_GET['msg'])) {
			echo "<div class='error'>" . urldecode($_GET['msg']) . "</div><br />";
		}
		?>
		
		<? if (!isset($_GET['load'])) { ?>
			<p id="existing">Already have a Tzivos Hashem account? Click <a href="#" id="existingUser">here</a>.</p>
		<? } ?>

		<form method="post" enctype="multipart/form-data" action="https://www.mashpia.com/shliachRegisterPost.php" accept-charset="UTF-8" name="form" id='form' onsubmit="return checkChildren();"> 								
			
			<fieldset id="login">
				<? if (isset($_SESSION['type']) && $_SESSION['type'] == 'update') { ?>
					<input type='hidden' id='admin_id' name='admin_id' value='<?=$_POST['admin_id']?>' />
					<legend>Login</legend>
					<table>
						<tr>
							<td class="small" colspan="2">
								Forgot your login info? Please email <a href="mailto:chanie@myshliach.com">chanie@myshliach.com</a>.
							</td>
						</tr>
						<tr>
							<td>Username</td>
							<td><input name="username" type="text"  id="username" required="required" 
								value="<?=isset($_POST['username']) ? $_POST['username'] : ''?>" />
								<span class='star'>*</span></td>
						</tr>
						<tr>
							<td>Password</td>
							<td><input name="password" type="password" id="password" required="required" 
								value="<?=isset($_POST['password']) ? $_POST['password'] : ''?>" />
								<span class='star'>*</span></td>
						</tr>
						<? if (!$load) { ?>
						<tr>
							<td>
								<input id='btnLogin' type='button' value='Login' />
							</td>
						</tr>
						<? } ?>
					</table>
				<? } else { ?>
					<legend>Create a Username</legend>
					<table>
						<tr>
							<td>Username</td>
							<td><input name="username" type="text" id="username" required="required" 
								value="<?=isset($_POST['username']) ? $_POST['username'] : ''?>" />
								<span class='star'>*</span></td>
						</tr>
						<tr>
							<td>Password</td>
							<td><input name="password" type="password" id="password" required="required" 
								value="<?=isset($_POST['password']) ? $_POST['password'] : ''?>" />
								<span class='star'>*</span></td>
						</tr>
						<tr>
							<td>Confirm Password</td>
							<td><input name="password2" type="password"  id="password2" required="required" 
								value="<?=isset($_POST['password2']) ? $_POST['password2'] : ''?>" />
								<span class='star'>*</span></td>
						</tr>
					</table>
				<? } ?>
			</fieldset>
			
			<fieldset>
				<legend>Personal Info</legend>
				<table>
					<tr>
						<td>First Name</td>
						<td><input name="first" type="text" id='first' required="required" 
							value="<?=isset($_POST['first']) ? $_POST['first'] : ''?>"
							/><span class='star'>*</span></td>
					</tr>
					<tr>
						<td>Last Name</td>
						<td><input name="last" type="text" id='last' required="required" 
							value="<?=isset($_POST['last']) ? $_POST['last'] : ''?>"
							/><span class='star'>*</span></td>
					</tr>
					<tr>
						<td>Address</td>
						<td><input name="admin_address1" type="text" id='address' required="required" 
							value="<?=isset($_POST['admin_address1']) ? $_POST['admin_address1'] : ''?>"
							/><span class='star'>*</span></td>
					</tr>
					<tr>
						<td>Address 2</td>
						<td><input name="admin_address2" type="text" id='address2' 
							value="<?=isset($_POST['admin_address2']) ? $_POST['admin_address2'] : ''?>"
							/></td>
					</tr>
					<tr>
						<td>City</td>
						<td><input name="admin_city" type="text" id='city' required="required" 
							value="<?=isset($_POST['admin_city']) ? $_POST['admin_city'] : ''?>"
							/><span class='star'>*</span></td>
					</tr>
					<tr>
						<td>State</td>
						<td><input name="admin_state" type="text" id='state' required="required" 
							value="<?=isset($_POST['admin_state']) ? $_POST['admin_state'] : ''?>"
							/><span class='star'>*</span></td>
					</tr>
					<tr>
						<td>Zip</td>
						<td><input name="admin_postal" type="text" id='zip' required="required" 
							value="<?=isset($_POST['admin_postal']) ? $_POST['admin_postal'] : ''?>"
							/><span class='star'>*</span></td>
					</tr>
					<tr>
						<td>Country</td>
						<td><input name="admin_country" type="text" id='country' required="required" 
							value="<?=isset($_POST['admin_country']) ? $_POST['admin_country'] : ''?>"
							/><span class='star'>*</span></td>
					</tr>
					<tr>
						<td>Home Phone</td>
						<td><input name="admin_phone_home" type="text" id='hphone' required="required" 
							value="<?=isset($_POST['admin_phone_home']) ? $_POST['admin_phone_home'] : ''?>"
							/><span class='star'>*</span></td>
					</tr>
					<tr>
						<td>Cell Phone</td>
						<td><input name="admin_phone_mobile" type="text" id='cphone' required="required" 
							value="<?=isset($_POST['admin_phone_mobile']) ? $_POST['admin_phone_mobile'] : ''?>"
							/><span class='star'>*</span></td>
					</tr>
					<tr>
						<td>Email Address</td>
						<td><input name="admin_email" type="email" id='email' required="required" 
							value="<?=isset($_POST['admin_email']) ? $_POST['admin_email'] : ''?>"
							/><span class='star'>*</span></td>
					</tr>
					<!--
					<tr>
						<td>Photo</td>
						<td>
							<? 
							if (isset($_POST['photo'])) 
								echo $_POST['photo'];
							else { 
								?><input type="file" name="photo" />
							<? } ?>
						</td>
					</tr>
					-->
				</table>
			</fieldset>
			
			<fieldset>
				<legend>Children</legend>
				<p id="children">
					Number of children registering: 
					<select name="addChildren" id="numChildren">
						<option value="0">0</option>
						<? for ($i = 1; $i < 11; $i++) {
							echo "<option value='" . $i . "'>" . $i . "</option>";
						} ?>
					</select><br />
					<div id="childrenNames"></div>
				</p>
			</fieldset>
			
			<fieldset>
				<legend>Program Tutorial</legend>
				<input type="radio" name="tutorial" value="0" checked="checked" /> I am familiar with the program and know how to run it<br />
				<input type="radio" name="tutorial" value="1" /> I would like to have a tutorial about the program
			</fieldset>
			
			<fieldset>
				<legend>TH WhatsApp</legend>
				<p>Tzivos Hashem Whatsapp broadcast list:</p>
				<input type="radio" name="whatsapp" value="1" /> Please add me to the Tzivos Hashem Whatsapp broadcast list to receive updates and reminders<br />
				<input type="radio" name="whatsapp" value="0" checked="checked" /> No thank you, I would not like to be added to the Tzivos Hashem Whatsapp broadcast list
			</fieldset>
			
			<fieldset>
				<legend>Additional Programs</legend>
				<p>I would like additional information about:</p>
				<input type="checkbox" name="chavrusaEn" /> The Ach/Achos Sheli Chavrusa program (English)<br />
				<input type="checkbox" name="chavrusaHe" /> The Ach/Achos Sheli Chavrusa program (Hebrew)<br />
				<input type="checkbox" name="library" /> The Yaldei Hashluchim Book Library<br />
				<input type="checkbox" name="birthday" /> The Birthday Zone<br />
				<input type="checkbox" name="mishmor" /> The International Chidon Sefer Hamitzvos
			</fieldset>
			
			<fieldset id='cc'>
				<legend>Credit Card Info</legend>
				<table>
					<tr>
						<td colspan="2">
							Total being charged: <b><span class='total'></span></b>
						</td>
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
						<td><input type='text' name='ccname' id='ccname' required="required"  /><span class='star'>*</span></td>
					</tr>
					<tr>
						<td>Credit Card Number</td>
						<td><input type='text' name='ccnum' id='ccnum' size="40"
							value="<?=isset($_POST['ccnum']) ? $_POST['ccnum'] : ''?>" required="required" /><span class='star'>*</span></td>
					</tr>
					<tr>
						<td>Expiry</td>
						<td>
							<select name='ccmm' id='mm'>
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
							<select name='ccyy' id='yy'>
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
						<td><input type='text' name='scode' id="scode" size='3' required="required" /><span class='star'>*</span></td>
					</tr>
					<tr>
						<td>Billing Zip Code</td>
						<td><input type='text' name='zcode' id='zcode' size='5' required="required" /><span class='star'>*</span></td>
					</tr>
					<tr>
						<td>Promo Code</td>
						<td>
							<input type='text' name='promo' id='promo' size='10' /> 
							<input type='button' name='applyPromo' id='applyPromo' value='apply' />
						</td>
					<tr>
						<td colspan="2">
							<input type='checkbox' name='agree' id='agree' /> 
							I allow Tzivos Hashem to charge my credit card <span class='total'></span>
							<input type='hidden' name='total' id='total' value='0' />
						</td>
					</tr>
				</table>
			</fieldset>	
			
			<input type="submit" id="continue" 
			<? if (isset($_SESSION['type']) && $_SESSION['type'] == 'update') { ?>
				value="Register"
			<? } else { ?>
				value="Create Account" 
			<? } ?>	
				class="button" > 
			
		</form>
		
		</div> 
				
	</body>
	
	<script src="https://www.mashpia.com/jquery-1.8.1.min.js" type="text/javascript"></script>
	
	<script>
		$( function() {
			<? 
			if (isset($_GET['error'])) {
				echo "alert('" . urldecode($_GET['error']) . "')";
			} 
			if (isset($_GET['msg'])) {
				echo "alert('" . urldecode($_GET['msg']) . "')";
			} 
			?>
		});
						
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
		
		var months = {
			1 : 'Jan', 
			2 : 'Feb', 
			3 : 'Mar',
			4 : 'Apr',
			5 : 'May',
			6 : 'Jun',
			7 : 'Jul',
			8 : 'Aug',
			9 : 'Sep',
			10 : 'Oct',
			11 : 'Nov',
			12 : 'Dec'
		};
		
		var fee = <?=$fee?>;
		$(document).on('change', '#numChildren', function() {
			$("#childrenNames").empty();
			calculateTotal();
			var val = $(this).val();
			var str = "";
			for (var i = 1; i <= val; i++) {
				str += "<fieldset class='child'><legend>Child #" + i + "</legend>";
				str += "<table><tr><td style='vertical-align: top'>Gender</td><td><input type='radio' name='gender[" + i + "]' value='m' class='gender' /> Boy<br />";
				str += "<input type='radio' name='gender[" + i + "]' value='f' class='gender' /> Girl</td></tr>";
				str += "<tr><td>First Name</td><td><input type='text' name='fname[]' class='fname' /></td></tr>";
				str += "<tr><td>Last Name</td><td><input type='text' name='lname[]' class='lname' /></td></tr>";
				str += "<tr><td>First Name in HEBREW LETTERS</td><td><input type='text' name='fnameh[]' class='fnameh keyboardInput' /></td></tr>";
				str += "<tr><td>Last Name in HEBREW LETTERS</td><td><input type='text' name='lnameh[]' class='lnameh keyboardInput' /></td></tr>";
				str += "<tr><td>DOB</td><td><select name='mm[]'>";
				for (var m in months) {
					str += "<option value='" + m + "'>" + months[m] + "</option>";
				}
				str += "</select><select name='dd[]'>";
				for (var k = 1; k < 32; k++) {
					str += "<option value='" + k + "'>" + k + "</option>";
				}
				str += "</select><select name='yy[]'>";
				var d = new Date();
				var y = d.getFullYear();
				var v = y - 14;
				for (; v <= y; v++) {
					str += "<option value='" + v + "'>" + v + "</option>";
				}
				str += "</select></td></tr>";
				str += "<tr><td>Grade</td><td><select name='grade[]'>";
				<? foreach ($grades as $gid => $grade) { ?>
					str += "<option value='<?=$gid?>'><?=$grade?></option>";
				<? } ?>
				str += "</select></td></tr>";
				str += "<tr><td>Photo</td><td><input type='file' name='photo[]' /></td></tr>";
				str += "<tr><td>Add Personalized Pushka</td><td><input type='radio' name='pp[" + i + "]' class='pp' value='1' /> (Additional $30)</td></tr>";
				str += "<tr><td>Add Plain Pushka</td><td><input type='radio' name='pp[" + i + "]' class='plainP' value='2' /> (Additional $25)</td></tr>";
				str += "</table>";
				//str += "<p>Child Supplies: <input type='checkbox' name='sup" + i + "' class='sup' />";
				//str += "Please ship the sticker book to my home ($5-USA only)</p></fieldset></div>";
				str += "</fieldset></div>";
			}
			$("#childrenNames").html(str);
		});
		
		$(document).on('click', '#existingUser', function() {
			var str = "<legend>Login</legend>";
			str += "<table><tr><td class='small' colspan='2'>Forgot your login info? Please email <a href='mailto:chanie@myshliach.com'>chanie@myshliach.com</a>.</td></tr>";
			str += "<td>Username</td><td><input name='username' type='text' id='username' required='required' />";
			str += "<span class='star'>*</span></td></tr><tr><td>Password</td><td>";
			str += "<input name='password' type='password' id='password' required='required' />";
			str += "<span class='star'>*</span></td></tr><tr><td><input id='btnLogin' type='button' value='Login' /></td></tr></table>";
			$("#login").empty();
			$("#login").append(str);
			$("#continue").val('Register');
			$("#existing").remove();
			$("#header").text('Existing Parent Account');
		});
		
		$(document).on('click', '#btnLogin', function() {
			var username = $("#username").val();
			var password = $("#password").val();
			$.post('ajax/login.php', {username : username, password : password}, function(success) {
				if (success == 0) {
					alert('There is no such username / password combination.');
				} else {
					var data = $.parseJSON(success);
					$("#first").val(data.first);
					$("#last").val(data.last);
					$("#address").val(data.admin_address1);
					$("#address2").val(data.admin_address2);
					$("#city").val(data.admin_city);
					$("#state").val(data.admin_state);
					$("#zip").val(data.admin_postal);
					$("#country").val(data.admin_country);
					$("#hphone").val(data.admin_phone_home);
					$("#cphone").val(data.admin_phone_mobile);
					$("#email").val(data.admin_email);
					
					var id = data.admin_id;
					var year = <?=$year?>;
					$.post('ajax/getChildren.php', {admin : id, year : year}, function(success) {
						if (success) {
							var children = $.parseJSON(success);
							$("#children").empty();
							var str = "<p>Check off the children that you are registering.</p>";
							for (var c in children) {
								if (c == 'registered') {
									for (d in children[c]) {
										str += "<del>" + children[c][d] + "</del> <i>[already registered]</i><br />";
									}
								}
								else if (c == 'not-registered') {
									for (d in children[c]) {
										str += "<input type='checkbox' name='children[]' class='regPay' value='" + d + "' /> " + children[c][d] + "<br />";
										str += "<blockquote><input type='radio' name='pp[" + d + "]' class='pp' value='1' /> Add Personalized Pushka (Additional $30)<br />";
										str += "<input type='radio' name='pp[" + d + "]' class='plainP' value='2' /> Add Plain Pushka (Additional $25)</blockquote><br />";
									}
								}
							}
							str += "<input type='hidden' id='admin_id' name='admin_id' value='" + id + "' />";
							str += "<p>Number of children adding: <select name='addChildren' class='addChildren'>";
							for (var k = 0; k < 10; k++) {
								str += "<option value='" + k + "'>" + k + "</option>";
							}
							str += "</select></p>";
							$("#children").append(str);
						}
					});
				}
			});
		});
		
		$(document).on('change', '.addChildren', function() {
			calculateTotal();
			$("#childrenAdded").empty();
			var num = $(this).val();
			if (num > 0) {
				var str = "<div id='childrenAdded'>";
				for (var i = 1; i <= num; i++) {
					str += "<fieldset class='child'><legend>Child #" + i + "</legend>";
					str += "<table><tr><td style='vertical-align: top'>Gender</td><td><input type='radio' name='gender[" + i + "]' class='gender' value='m' /> Boy<br />";
					str += "<input type='radio' name='gender[" + i + "]' value='f' class='gender' /> Girl</td></tr>";
					str += "<tr><td>First Name</td><td><input type='text' name='fname[]' class='fname' /></td></tr>";
					str += "<tr><td>Last Name</td><td><input type='text' name='lname[]' class='lname' /></td></tr>";
					str += "<tr><td>First Name in HEBREW LETTERS</td><td><input type='text' name='fnameh[]' class='fnameh keyboardInput' /></td></tr>";
					str += "<tr><td>Last Name in HEBREW LETTERS</td><td><input type='text' name='lnameh[]' class='lnameh keyboardInput' /></td></tr>";
					str += "<tr><td>DOB</td><td><select name='mm[]'>";
					for (var m in months) {
						str += "<option value='" + m + "'>" + months[m] + "</option>";
					}
					str += "</select><select name='dd[]'>";
					for (var k = 1; k < 32; k++) {
						str += "<option value='" + k + "'>" + k + "</option>";
					}
					str += "</select><select name='yy[]'>";
					var d = new Date();
					var y = d.getFullYear();
					var v = y - 14;
					for (; v <= y; v++) {
						str += "<option value='" + v + "'>" + v + "</option>";
					}
					str += "</select></td></tr>";
					str += "<tr><td>Grade</td><td><select name='grade[]'>";
					<? foreach ($grades as $gid => $grade) { ?>
						str += "<option value='<?=$gid?>'><?=$grade?></option>";
					<? } ?>
					str += "</select></td></tr>";
					str += "<tr><td>Photo</td><td><input type='file' name='photo[]' /></td></tr>";
					str += "<tr><td>Add Personalized Pushka</td><td><input type='radio' name='pp[" + i + "]' class='pp' value='1' /> (Additional $30)</td></tr>";
					str += "<tr><td>Add Plain Pushka</td><td><input type='radio' name='pp[" + i + "]' class='plainP' value='2' /> (Additional $25)</td></tr>";
					str += "</table>";
					//str += "<p>Child Supplies: <input type='checkbox' name='sup" + i + "' class='sup' />";
					//str += "Please ship the sticker book to my home ($5-USA only)</p></fieldset></div>";
					str += "</fieldset></div>";
				}
				$(".addChildren").after(str);
			}
		});
		
		<? if (isset($_POST['admin_id'])) { ?> 
			var admin = <?=$_POST['admin_id']?>;
			$.post('ajax/getChildren.php', {admin : admin}, function(success) {
				if (success) {
					var children = $.parseJSON(success);
					$("#children").empty();
					var str = "<p>Check off the children that you are registering.</p>";
					for (var c in children) {
						if (c == 'registered') {
							for (d in children[c]) {
								str += "<del>" + children[c][d] + "</del> <i>[already registered]</i><br />";
							}
						}
						else if (c == 'not-registered') {
							for (d in children[c]) {
								str += "<input type='checkbox' name='children[]' class='regPay' value='" + d + "' /> " + children[c][d] + "<br />";
							}
						}
					}
					str += "<input type='hidden' id='admin_id' name='admin_id' value='" + admin + "' />";
					str += "<p>Number of children adding: <select name='addChildren' class='addChildren'>";
					for (var k = 0; k < 10; k++) {
						str += "<option value='" + k + "'>" + k + "</option>";
					}
					str += "</select></p>";
					$("#children").append(str);
				}
			}); 
		<? } ?>
		
		$(document).on('click', '.regPay', function() {
			calculateTotal();
		});
		
		$(document).on('blur', '#username', function() {
			if ($("#continue").val() != 'Register') {
				var function_name = "is_username_duplicate"; 
			    var parameters = [$(this).val()]; 
			    var url = "camps/includes/get_functions.php?function_name=" + function_name + "&parameters=" + parameters;			   
			    $.ajax({ 
					 async: false, 
					 url: url, 
					 dataType: "json", 
					 success: function(data) {					 
					   if (data == true) {			 
						 	alert('This username has already been taken.');
						 	//$("#username").focus();
					   }
					}, 
				});
			}
		});
		
		$(document).on('blur', '#password2', function() {
			var pass = $("#password").val();
			var pass2 = $("#password2").val();
			if (pass != pass2) {
				alert('You passwords do not match.');
				//$("#password2").focus();
			}
		});
		
		$(document).on('click', '.sup', function() {
			calculateTotal();
		});
		
		$(document).on('click', '#applyPromo', function() {
			calculateTotal();
		});
		
		$(document).on('click', '.pp', function() {
			calculateTotal();
		});
		
		$(document).on('click', '.plainP', function() {
			calculateTotal();
		});
		
		function calculateTotal() {
			var total = 0;
			if ($(".regPay").length) {
				$(".regPay").each( function() {
					if ($(this).is(":checked")) {
						total++;
					}
				});
			}
			if ($(".addChildren").length)
				total += parseInt($(".addChildren").val());
			else 
				total += parseInt($("#numChildren").val());
			total *= fee;
			
			var numChecked = 0;
			$(".sup").each( function() {
				if ($(this).is(":checked")) numChecked++;
			});
			total += (numChecked * 5);
			
			var pp = 0;
			$(".pp").each( function() {
				if ($(this).is(":checked")) pp++;
			});
			total += (pp * 30);
			
			var plainP = 0;
			$(".plainP").each( function() {
				if ($(this).is(":checked")) plainP++;
			});
			total += (plainP * 25);
			
			var promo = $("#promo").val().trim().toLowerCase();
			if (promo == 'tzivos15') {
				total *= .85;
			} else if (promo == 'tzivos3') {
				if ($(".addChildren").length)
					var num = parseInt($(".addChildren").val());
				else 
					var num = parseInt($("#numChildren").val());
				if (num > 2)
					total -= fee;
			}
			
			if (total % 1 == 0) {
				$(".total").text('$' + total + '.00');
			} else {
				$(".total").text('$' + total);
			}
			$("#total").val(total);
		}
		
		function checkChildren() {
			var good = true;
			$(".child").each( function() {
				var gender = $(this).find('.gender');
				var checked = false;
				var m = $(gender).eq(0);
				var f = $(gender).eq(1);
				if ($(m).is(":checked") || $(f).is(":checked")) {
					checked = true;
				}
				var first = $(this).find(".fname").val();
				var last = $(this).find(".lname").val();
				var firsth = $(this).find(".fnameh").val();
				var lasth  = $(this).find(".lnameh").val();
				if (!checked || first == '' || last == '' || firsth == '' || lasth == '') {
					alert('All children being added need to have ALL their information filled out.');
					good = false;
				} 
			});
			
			if (good) {
				var username = $("#username").val();
				if (!isAlphaNumeric(username)) {
					alert('Username can only contain letters and numbers.');
					return false;
				}

				if (!$("#admin_id").val()) {
					if (username_not_duplicate(username)) {
						alert('This username has already been taken.');
						return false;
					}
					var pass = $("#password").val();
					var pass2 = $("#password2").val();
					if (pass != pass2) {
						alert('Your passwords do not match.');
						return false;
					}
				} 				
				
				if (!$("#agree").is(":checked")) {
					alert('You have not indicated that you agree to the charge.');
					return false;
				}
				
				if ($("#total").val() == 0) {
					alert('You have not chosen to register any children!');
					return false;
				}
			} 
			return good;
		}
	</script>
</html>