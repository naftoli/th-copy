<?php
session_start();
$admin_id = 0;
include("db.php");

$action = isset($_POST['action']) ? $_POST['action'] : ''; 
$submit_form = isset($_POST['submit_form']) ? $_POST['submit_form'] : 0; 

$message = "";
$next_page = "false";

$hebYear = 5775;

// if form has been submitted, then perform validation, update/add
if ($submit_form)
{
	// request to add a parent record
	if ($action == 'add') {
		$admin_id = insert_into_admins();
		if ($admin_id > 0) {
			$email = trim($_POST['admin_email']);
			//if (!empty($email))				
				//send_parent_registration_confirmation_email(mysql_real_escape_string($email));		
			$_SESSION["admin_id"] = $admin_id;
			$next_page = "true";												
			// header("Location: http://www.mashpia.com/register_parent_2.php");			
		}
		else {
			$message = "Insert failed. Please try again.";
		}		
	}
	// request to edit a parent record
	elseif ($action == 'update')	{
		update_admins();
		$next_page = "true";
		//header("Location: register_parent_2.php");
	}
}
	
// on edit - get values	 based on admin_id
if ($admin_id > 0) {
	include("camps/includes/classes/admin.php");
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
			admin_email = '" . mysql_real_escape_string($_POST['admin_email'])  . "' 
		WHERE admin_id ='" . mysql_real_escape_string($_POST['admin_id']) ."'" ;	
	$query = mysql_query($sql);	
	if (!$query) {
		include('constant_file.php');
		@mail($programmers_email2, 'Error in program register_student.php',  "error in SQL update statement: " , mysql_error() );		
	}	
}

// insert admins record
function insert_into_admins() {
	global $hebYear;
	$sql = "INSERT INTO admins  
		(first,
		last, 
		admin_address1,
		admin_address2,
		admin_city,
		admin_state,
		admin_postal,
		admin_email,		
		username, 
		password, 
		is_parent) 
		VALUES( '" . mysql_real_escape_string(trim($_POST['first']))  . "' ,
				'" . mysql_real_escape_string(trim($_POST['last']))  . "'  ,
				'" . mysql_real_escape_string(trim($_POST['admin_address1']))  . "' ,
				'" . mysql_real_escape_string(trim($_POST['admin_address2']))  . "' ,
				'" . mysql_real_escape_string(trim($_POST['admin_city']))  . "' ,
				'" . mysql_real_escape_string(trim($_POST['admin_state']))  . "' ,
				'" . mysql_real_escape_string(trim($_POST['admin_postal']))  . "' ,
				'" . mysql_real_escape_string(trim($_POST['admin_email']))  . "' ,
				'" . mysql_real_escape_string(trim($_POST['username']))  . "' ,
				'" . mysql_real_escape_string(trim($_POST['password']))  . "' , 1)";
	$query = mysql_query($sql);
	
	if ($query) {
		  $admin_id = mysql_insert_id();
		
          //create student account with same info and link to admin account
          $user_code = get_user_code();
          $user_serial = get_user_serial(); 
                  
          $sql = "insert into users set ";
          $sql .= "lang='en', ";
          $sql .= "user_code=" . $user_code . ", ";
          $sql .= "first='" . mysql_real_escape_string(trim($_POST['first'])) . "', ";
          $sql .= "last='" . mysql_real_escape_string(trim($_POST['last'])) . "', ";
          $sql .= "class_id = " . mysql_real_escape_string($_POST['grade']) . ", ";
          $sql .= "school_id=1, ";                   
          $sql .= "gender='F', ";
          $sql .= "school_type_id = 1, ";
          $sql .= "user_start_date='" . unixtojd() . "', ";                         
          $sql .= "user_serial=" . $user_serial . ", ";
          $sql .= "user_registered = now(), ";
          $sql .= "heb_year = " . $hebYear;
          //echo $sql;
          mysql_query( $sql ) or die( mysql_error() );
          $user_id = mysql_insert_id();
          
          $sqlJoin = "insert into admin_auths values($admin_id, 'user', $user_id, 1)";
          mysql_query($sqlJoin) or die(mysql_error());
          
          //put student on year/ladder/subject
          $sql = "insert into user_tracks values($user_id,1,1,1,1)";
          mysql_query($sql) or die(mysql_error());
          
          //update user's medal to White
          //require_once 'classes/medal_updater.php';
          //$m = new medal_updater;
          //$m->update_medal_two($user_id);
	} 	
	else {
		include('constant_file.php');
		@mail($programmers_email2, 'Error in program register_student.php',  "error in SQL insert statement: " , mysql_error() );		
	}	
	return $admin_id;	
}

function send_parent_registration_confirmation_email($parent_email) {	
	require_once("classes/send_mail.php");
	
	$mail_parms = array();
	$mail_parms['to'] = $parent_email;
	$mail_parms['subject'] = "Login Confirmation";
	$mail_parms['message'] = "Your login to achoshatemimim.com has been confirmed. Your username is " . $_POST['username'] . " and your password is " . $_POST['password'] . ". Thank you." ;	$mail_parms['headers'] = "From: info@mashpia.com\r\nReply-To: info@mashpia.com";
	$mail_parms['headers'] = "From: info@achoshatemimim.com\r\nReply-To: info@achoshatemimim.com";

	$myMailClass = new MailClass();
	$success = $myMailClass->send_mail($mail_parms);
}

function get_user_serial() {
    $user_serial = 0;
    $sql = "SELECT IFNULL(MAX(user_serial), 0)+1 AS user_serial FROM users";
    $query = mysql_query($sql);
    $row = mysql_fetch_assoc($query);
    $user_serial = $row["user_serial"];
    return $user_serial;
    
}

function get_user_code() {
    $user_code = 0;
    if (mysql_result(mq("SELECT GET_LOCK('users', 30)"),0) != 1) 
        trigger_error('could not get lock', E_USER_ERROR);
    $count = 0;
    do {
        if ($count++ > 100000) 
            trigger_error('could not get ID', E_USER_ERROR);
        $user_code = mysql_result(mq('SELECT FLOOR(RAND() * 9223372036854775807)'),0);
    } while (mysql_result(mq("SELECT COUNT(*) FROM users WHERE user_code = $user_code"),0) != 0);
    return $user_code;
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
		<script src="scripts/jquery.styleselect.js"></script>				
		
		<script>
			var next_page = "<?=$next_page;?>";
			var admin_id = "<?=$admin_id;?>";

			$(function(){
					$("#nav").height($("#content").height());
					$('input').placeholder();
					$(".sSelect").sSelect();
				});

			// validate input	
			function validation(){
		
				var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
				var address = document.getElementById('admin_email').value;
		
				if (document.getElementById('first').value == '') {
					document.getElementById('first').focus();
					alert("First Name is mandatory.");
					return false;
				}	
				else if (document.getElementById('last').value == '') {
					document.getElementById('last').focus();
					alert("Last Name is mandatory.");
					return false;
				}
				/*
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
				*/
				else if (document.getElementById('username').value == '') {
					document.getElementById('username').focus();
					alert("Username is mandatory.");
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
				else if (address != '' && email_not_duplicate(address) ) {
					document.getElementById('username').focus();
					alert("Duplicate email address. If you already have an account, you may login directly at achoshatemim.com.");
					return false;
				}		
				else if (document.getElementById('password').value != document.getElementById('password2').value) {
					document.getElementById('password').focus();
					alert("Passwords do not match.");
					return false;
				}
				else if (document.getElementById('password').value == "") {
					document.getElementById('password').focus();
					alert("Password is mandatory.");
					return false;
				}
				/* 
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
				*/
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
			
			function check_next_page() {
				if (next_page == "true") {
					var parent_registration = document.forms["parent_registration"];
					parent_registration.elements["admin_id"].value = admin_id;
					parent_registration.submit();
				}
			}			
		</script>
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
				<div class="top">
					<img src="images/logo-achos-hatemimim.png" width="180" />
				</div>
				<!--<div class="col_title_bg"></div>
				<div class="col_title">Menu</div>-->
				<? $curr = 1; ?>
				<? include("register_student_menu.php"); ?>
			</div>
			
			<div id="content">
			
				<div class="col_title_bg"></div>
				
				<div class="slider_container">
				
					<div class="slider">
					
						<div class="col_title"></div>
						
						<div class="col_content">
							<h1>New Achos Member</h1>
	 
							<h1><?=$message;?></h1>
							
							<!--<p>Before you register your children please update your profile.</p>-->
							
							<form method="post" action="register_student.php" accept-charset="UTF-8" name="submit"  onsubmit="return validation();"> 								
								<input type="hidden" name="submit_form" value="true">
								<input type="hidden" name="admin_id" value="<?=$admin_id;?>">
								
								<? if ($admin_id == 0) : ?>
								<input type="hidden" name="action" value="add">
								<? else : ?>
								<input type="hidden" name="action" value="update">
								<? endif; ?>
								
								<h2>Personal Info</h2> 
								<div class="module" id="module-info">
									<div class="module_content">
										<div class="lists form">
										  <ul>
										  	
										  	  <li>
										  	  	<span class="label"><label for="school">Choose School</label></span>
										  	  	<span class="input">
										  	  		<select name="school" class="sSelect">
										  	  			<option value='1'>Beis Rivka CH</option>
										  	  		</select>
										  	  	</span>
										  	  </li>
											  <li>
												  <span class="label"><label for="first">First Name</label></span>
												  <span class="input"><input name="first" type="text" id='first' value='<?= empty($admin_id) ? '' : $admin->first; ?>' /></span>
											  </li>
											  <li>
												  <span class="label"><label for="last">Last Name</label></span>
												  <span class="input"><input name="last" type="text" id='last' value='<?= empty($admin_id) ? '' : $admin->last?>' /></span>
											  </li>
											  
											  <li>
                                                  <span class="label"><label for="grade">Grade</label></span>
                                                  <span class="input"><select name="grade" class="sSelect">
                                                      <?
                                                      $sql = "select * from classes";
                                                      $result = mysql_query($sql);
                                                      while ($row = mysql_fetch_assoc($result)) {
                                                          echo "<option value=" . $row['class_id'] . ">" . $row['class_grade'] . '-' . $row['class_sub'] . "</option>";
                                                      }
                                                      ?>
                                                  </select></span>
                                              </li>
											  
												<li>
													<span class="label"><label for="admin_address1">Address</label></span>
													<span class="input"><input name="admin_address1" type="text"  value='<?= empty($admin_id) ? '' : $admin->admin_address1?>' /></span>
													<div class="clear"></div>
													
													<span class="label"></span>
													<span class="input"><input name="admin_address2" type="text"   value='<?= empty($admin_id) ? '' : $admin->admin_address2?>' /></span>
													<div class="clear"></div>
													
													<span class="label"><label >City  / State / Zip </label></span>
													<span class="input city"><input name="admin_city" type="text"    value='<?= empty($admin_id) ? '' : $admin->admin_city?>' /></span>
													
													<span class="input state"><input name="admin_state" type="text"    value='<?= empty($admin_id) ? '' : $admin->admin_state?>' /></span>
													
													<span class="input zip"><input name="admin_postal" type="text"    value='<?= empty($admin_id) ? '' : $admin->admin_postal?>'  /></span>
												</li>
												
											  <li>
												  <span class="label"><label for="email">Email Address</label></span>
												  <span class="input"><input name="admin_email" id="admin_email" type="text"  value='<?= empty($admin_id) ? '' : $admin->admin_email?>' /></span>
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
