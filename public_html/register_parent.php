<?php
session_start();
if (!isset($_COOKIE['naftoli'])) {
	//header("Location: register_parent_temp.php");
}
if (isset($_POST['admin_id'])) {
	$_SESSION['admin_id'] = $_POST['admin_id'];
	$admin_id = $_SESSION['admin_id'];
} else {
	$admin_id = null;
}

//include("check_admin_id.php");
include("db.php");

$action = isset($_POST['action']) ? $_POST['action'] : ''; 
$submit_form = isset($_POST['submit_form']) ? $_POST['submit_form'] : 0; 

$message = "";
$next_page = "false";

// if form has been submitted, then perform validation, update/adds
if ($submit_form)
{
	//echo "<pre>"; print_r($_POST); echo "</pre>"; 
	foreach ($_POST as $k => $v) {
		$_POST[$k] = mysql_real_escape_string(trim($v));
		if ($k == 'admin_id' || $k == 'admin_address2') continue;
		if (trim($v) == '') {
			echo "all fields are mandatory please go back and try again.";
			exit;
		}
	}	
	
	//check for spammers    
	include 'check_for_spammers.php';
	
	if ($_POST['first'] == 'Acunetix' || $_POST['last'] == 'yrbsopsw') {
		echo "unsuccessful";
		exit;
	}
	
	if (trim($_POST['admin_email']) == 'jamebond129@gmail.com') {
		echo "unsuccessful";
		exit;
	}
	
	// request to add a parent record
	if ($action == 'add') {
		$admin_id = insert_into_admins();
		if ($admin_id > 0) {				
			send_parent_registration_confirmation_email(mysql_real_escape_string(trim($_POST['admin_email'])));		
			$_SESSION["admin_id"] = $admin_id;
			$next_page = "true";
		}
		else {
			$message = "Insert failed. Please try again.";
		}		
	}
	// request to edit a parent record
	elseif ($action == 'update')	{
		update_admins();
		$next_page = "true";
	}
}
	
// on edit - get values	 based on admin_id
if ($admin_id > 0) {
	include("classes/admin.php");
	$sql = "SELECT * FROM admins WHERE admin_id='" . mysql_real_escape_string($admin_id) . "'" ;		
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);	
	$admin = new admin($row);
}

// update admins record
function update_admins() {
	$admin_id = 0;
	$sql = "UPDATE admins  
			SET 
			first = '" . mysql_real_escape_string($_POST['first'])  . "' ,
			last =  '" . mysql_real_escape_string($_POST['last'])  . "' ,
			admin_address1 = '" . mysql_real_escape_string($_POST['admin_address1'])  . "' ,
			admin_address2 = '" . mysql_real_escape_string($_POST['admin_address2'])  . "' ,
			admin_city = '" . mysql_real_escape_string($_POST['admin_city'])  . "' ,
			admin_state = '" . mysql_real_escape_string($_POST['admin_state'])  . "' ,
			admin_postal = '" . mysql_real_escape_string($_POST['admin_postal'])  . "' ,
			admin_phone_home = '" . mysql_real_escape_string($_POST['admin_phone_home'])  . "' ,
			admin_phone_mobile = '" . mysql_real_escape_string($_POST['admin_phone_mobile'])  . "' ,
			admin_email = '" . mysql_real_escape_string($_POST['admin_email'])  . "' 
		WHERE admin_id ='" . mysql_real_escape_string($_POST['admin_id']) ."'" ;		
	$query = mysql_query($sql);	
	if($query){	
	}	
	else{
		include('constant_file.php');
		@mail($programmers_email2, 'Error in program register_parent.php',  "error in SQL update statement: " , mysql_error() );		
	}	
}

// insert admins record
function insert_into_admins() {
	$sql = "INSERT INTO admins  
		(first,
		last, 
		admin_address1,
		admin_address2,
		admin_city,
		admin_state,
		admin_postal,
		admin_phone_home,
		admin_phone_mobile,
		admin_email,		
		username, 
		password, 
		reminders,
		is_parent) 
		VALUES( '" . mysql_real_escape_string(trim($_POST['first']))  . "' ,
				'" . mysql_real_escape_string(trim($_POST['last']))  . "'  ,
				'" . mysql_real_escape_string(trim($_POST['admin_address1']))  . "' ,
				'" . mysql_real_escape_string(trim($_POST['admin_address2']))  . "' ,
				'" . mysql_real_escape_string(trim($_POST['admin_city']))  . "' ,
				'" . mysql_real_escape_string(trim($_POST['admin_state']))  . "' ,
				'" . mysql_real_escape_string(trim($_POST['admin_postal']))  . "' ,
				'" . mysql_real_escape_string(trim($_POST['admin_phone_home']))  . "' ,
				'" . mysql_real_escape_string(trim($_POST['admin_phone_mobile']))  . "' ,
				'" . mysql_real_escape_string(trim($_POST['admin_email']))  . "' ,
				'" . mysql_real_escape_string(trim($_POST['username']))  . "' ,
				'" . mysql_real_escape_string(trim($_POST['password']))  . "' ,
				'" . mysql_real_escape_string(trim($_POST['reminders']))  . "', 1)" ;

	$query = mysql_query($sql);
	
	if($query){
		$admin_id = mysql_insert_id();
	}	
	else{
		include('constant_file.php');
		@mail($programmers_email2, 'Error in program register_parent.php',  "error in SQL insert statement: " , mysql_error() );		
	}	
	return $admin_id;	
}

function send_parent_registration_confirmation_email($parent_email) {	
	require_once("classes/send_mail.php");
	
	$mail_parms = array();
	$mail_parms['to'] = $parent_email;
	$mail_parms['subject'] = "Login Confirmation";
	$mail_parms['message'] = "Your login to mashpia.com has been confirmed. Your username is " . $_POST['username'] . " and your password is " . $_POST['password'] . ". Thank you." ;	$mail_parms['headers'] = "From: info@mashpia.com\r\nReply-To: info@mashpia.com";
	$mail_parms['headers'] = "From: info@mashpia.com\r\nReply-To: info@mashpia.com";

	$myMailClass = new MailClass();
	$success = $myMailClass->send_mail($mail_parms);
}
?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" dir="<?=$dir?>">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<title>Registration Wizard - Tzivos Hashem Management System</title>
		<link rel="alternate" media="print" href="index.php">
		<link href="admin_styles.css" rel="stylesheet" type="text/css" />
		<script src="jquery.js" type="text/javascript"></script>
		<script src="camps/scripts/jquery.tools.min.js"></script>
		<script src="scripts/jquery.placeholder.js"></script>				
		
		<script>
			var next_page = "<?=$next_page;?>";
			var admin_id = "<?=$admin_id;?>";
			
			$(document).ready(function() {
			});

			$(function(){
					$("#nav").height($("#content").height());
					$('input').placeholder();
				}); 

			// validate input	
			function validation(){
			
				var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
				var email = document.getElementById('admin_email').value;
				
				if (document.getElementById('first').value == '' || document.getElementById('first').value.length < 3) {
					document.getElementById('first').focus();
					alert("First Name must be at least 3 characters.");
					return false;
				}	
				else if (document.getElementById('last').value == '' || document.getElementById('last').value.length < 3) {
					document.getElementById('last').focus();
					alert("Last Name must be at least 3 characters.");
					return false;
				}
				else if (document.getElementById('admin_phone_home').value == '' || document.getElementById('admin_phone_home').value.length < 9 ||
						 isAlphabetic(document.getElementById('admin_phone_home').value)) {
					document.getElementById('admin_phone_home').focus();
					alert("Home Phone must be at least 9 digits and cannot contain alphabetic characters.");
					return false;
				}
				else if (document.getElementById('admin_phone_mobile').value == '' || document.getElementById('admin_phone_mobile').value.length < 9 ||
						 isAlphabetic(document.getElementById('admin_phone_mobile').value)) {
					document.getElementById('admin_phone_mobile').focus();
					alert("Cell Phone must be at least 9 digits and cannot contain alphabetic characters.");
					return false;
				}
				else if (document.getElementById('admin_email').value == '') {
					document.getElementById('admin_email').focus();
					alert("All fields are mandatory.");
					return false;
				}
				else if  (reg.test(email) != true) {					
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
				else if (document.getElementById('admin_address1').value == "" || document.getElementById('admin_address1').value.length < 5) {
					document.getElementById('admin_address1').focus();
					alert("Address must be at least 5 characters.");
					return false;
				} 
				else if (document.getElementById('admin_city').value == "" || document.getElementById('admin_city').value.length < 3) {
					document.getElementById('admin_city').focus();
					alert("City must be at least 3 characters.");
					return false;
				} 
				else if (document.getElementById('admin_state').value == "" || document.getElementById('admin_state').value.length < 2) {
					document.getElementById('admin_state').focus();
					alert("State must be at least 2 characters.");
					return false;
				} 
				else if (document.getElementById('admin_postal').value == "" || document.getElementById('admin_postal').value.length < 5) {
					document.getElementById('admin_postal').focus();
					alert("Zip code must be at least 5 characters.");
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
			
			function check_next_page() {
				if (next_page == "true") {
					alert("Your information was successfully updated / save. You will now be sent to the login page.");
					var parent_registration = document.forms["parent_registration"];
					parent_registration.elements["admin_id"].value = admin_id;
					parent_registration.submit();
				}
			}			
		</script>
		<!--Copyright Ariel Shkedi 2007-2010-->
	</head>

	<body onload="check_next_page();">
	
		<FORM name="parent_registration" method="post" action="admin.php">
			<input type="hidden" name="admin_id" value="">
		</FORM>
	
	
		<NOSCRIPT>
			<P STYLE="color: red; font-size: larger;">Notice: You have javascript disabled. Some parts of the site will not function without javascript.</P>
		</NOSCRIPT>
		
		<div id="wrapper">
		
			<div id="nav" class="wizard">
				<div class="col_title_bg"></div>
				<div class="col_title">Menu</div>
				<? $curr = 1; ?>
				<ul class="list_first">
					<li class="list_parent<?=($curr==1)?' current':''?>"><a href="#"><img src="images/icon_pr_user.png" width="28" height="28" alt="Login" />New Parent Account</a></li>
					<!--
					<li class="list_parent<?=($curr==2)?' current':''?>"><a href="#"><img src="images/icon_pr_children.png" width="28" height="28" alt="Login" />Add Children</a></li>
					<li class="list_parent<?=($curr==3)?' current':''?>"><a href="#"><img src="images/icon_pr_register.png" width="28" height="28" alt="Login" />Child Registration</a></li>
					<li class="list_parent<?=($curr==4)?' current':''?>"><a href="#"><img src="images/icon_pr_checkout.png" width="28" height="28" alt="Login" />Checkout</a></li>
					<li class="list_parent<?=($curr==5)?' current':''?>"><a href="#"><img src="images/icon_pr_summary.png" width="28" height="28" alt="Login" />Summary</a></li>
					-->
				</ul>
			</div>
			
			<div id="content">
			
				<div class="col_title_bg"></div>
				
				<div class="slider_container">
				
					<div class="slider">
					
						<div class="col_title"></div>
						
						<div class="col_content">
							<h1>New Parent Account</h1>
	 
							<h1><?=$message;?></h1>
							
							<!--<p>Before you register your children please update your profile.</p>-->
							
							<form method="post" action="register_parent.php" accept-charset="UTF-8" name="submit"  onsubmit="return validation();"> 								
								<input type="hidden" name="submit_form" value="true">
								<input type="hidden" name="admin_id" value="<?=$admin_id;?>">
								
								<? if ($admin_id == 0) : ?>
								<input type="hidden" name="action" value="add">
								<? else : ?>
								<input type="hidden" name="action" value="update">
								<? endif; ?>
								
								<h2>Personal Info</h2> 
								<p><i>All fields are mandatory</i></p>
								<div class="module" id="module-info">
									<div class="module_content">
										<div class="lists form">
										  <ul>
											  <li>
												  <span class="label"><label for="first">First Name</label></span>
												  
												  <span class="input"><input name="first" type="text" id='first' value='<?= is_null($admin_id) ? '' : $admin->first; ?>' required /></span>
											  </li>
											  <li>
												  <span class="label"><label for="last">Last Name</label></span>
												  <span class="input"><input name="last" type="text" id='last' value='<?= is_null($admin_id) ? '' : $admin->last?>' required /></span>
											  </li>
												<li>
													<span class="label"><label for="admin_address1">Address</label></span>
													<span class="input"><input name="admin_address1" type="text"  value='<?= is_null($admin_id) ? '' : $admin->admin_address1?>' required /></span>
													<div class="clear"></div>
													
													<span class="label"></span>
													<span class="input"><input name="admin_address2" type="text"   value='<?= is_null($admin_id) ? '' : $admin->admin_address2?>' /></span>
													<div class="clear"></div>
													
													<span class="label"><label >City  / State / Zip </label></span>
													<span class="input city"><input name="admin_city" type="text"    value='<?= is_null($admin_id) ? '' : $admin->admin_city?>' required /></span>
													
													<span class="input state"><input name="admin_state" type="text"    value='<?= is_null($admin_id) ? '' : $admin->admin_state?>' required /></span>
													
													<span class="input zip"><input name="admin_postal" type="text"    value='<?= is_null($admin_id) ? '' : $admin->admin_postal?>' required /></span>
												</li>
											  <li>
												  <span class="label"><label for="home">Home Phone</label></span>
												  <span class="input"><input name="admin_phone_home" id="admin_phone_home" type="text" value='<?= is_null($admin_id) ? '' : $admin->admin_phone_home?>' required /></span>
											  </li>
											  <li>
												  <span class="label"><label for="mobile">Cell Phone</label></span>
												  <span class="input"><input name="admin_phone_mobile" id="admin_phone_mobile" type="text" value='<?= is_null($admin_id) ? '' : $admin->admin_phone_mobile?>' required /></span>
											  </li>
											  <li>
												  <span class="label"><label for="email">Email Address</label></span>
												  <span class="input"><input name="admin_email" id="admin_email" type="text" value='<?= is_null($admin_id) ? '' : $admin->admin_email?>' required /></span>
											  </li>
											</ul>
										</div>
									</div>
								</div>
								
								<? if ($admin_id == 0) : ?>
								<h2>Create a Username</h2> 
								<div class="module" id="module-info">
									<div class="module_content">
										<div class="lists form">
											<ul>
												<li>
													<span class="label"><label for="username">Username</label></span>
													<span class="input"><input name="username" type="text"  id="username" /></span>
												</li>
												
												<li>
													<span class="label"><label for="password">Password</label></span>
													<span class="input"><input name="password" type="password" id="password" /></span>
												</li>
												
												<li>
													<span class="label"><label for="password2">Confirm Password</label></span>
													<span class="input"><input name="password2" type="password"  id="password2" /></span>
												</li>
												
												<!--
												<li>
													<span class="label"><label for="lang">Language</label></span>
													<span class="input">
														<select name="lang" class="select">
															<option value="0" disabled="disabled">Please Select</option>														  
															<option <? if ($admin->lang == "en") echo "selected"; ?> value="en">English</option>
															<option <? if ($admin->lang == "he") echo "selected"; ?> value="he">?????</option>
															<option <? if ($admin->lang == "yi") echo "selected"; ?> value="yi">?????</option>
														</select>
													</span>
												</li>
												
												<li>
													<p class="input"><input type="checkbox" name="reminders" value="1"><label for="remind">I would like to receive reminders whenever new missions are posted.</label></p>
												</li>
												
												<li>
													<input type="submit" value="Continue" class="button"> 
												</li>
												-->
											</ul>
										</div>
									</div>
								</div>
								<? endif; ?>
								
								<input type="submit" id="Continue" value="Create Account" class="button" > 
								
							</form> 
							
						</div>
					</div>
				</div>
			</div>
		</div>

	</body>
</html>
