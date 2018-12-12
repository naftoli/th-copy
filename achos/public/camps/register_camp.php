<?php
$message = 0;

if (isset($_POST["action"])) {
	include ("includes/db.php");
	require_once('includes/file_save.php');	

	$camp_logo_id = "NULL";
	if (isset($_FILES['camp_photo'])) 
		$camp_logo_id = addFile($_FILES['camp_photo'], $user_photo_id);

	$camp_name = $_POST['camp_name'];
	$camp_name_he = $_POST['camp_name_he'];
	$camp_gender = $_POST['camp_gender'];
	$camp_address1 = $_POST['camp_address1'];
	$camp_address2 = $_POST['camp_address2'];
	$camp_city = $_POST['camp_city'];
	$camp_state = $_POST['camp_state'];
	$camp_postal = $_POST['camp_postal'];
	$camp_country = $_POST['camp_country'];
	$camp_phone = $_POST['camp_phone'];
	$title = $_POST['title'];
	$first = $_POST['first'];
	$last = $_POST['last'];
	$date_array =explode(("/", $_POST['start_date']);
	$start_date = gregoriantojd($date_array[0], $date_array[1], $date_array[2]);    
	$date_array =explode(("/", $_POST['end_date']);
	$end_date = gregoriantojd($date_array[0], $date_array[1], $date_array[2]);    

	$admin_phone_mobile = $_POST['admin_phone_mobile'];
	$admin_email = $_POST['admin_email'];
	$admin_phone_work = $_POST['admin_phone_work'];
	$admin_phone_home = $_POST['admin_phone_home'];
	
	$username = $_POST['username'];
	$password = $_POST['password'];
	
	$camp_number = mysql_result(mq("(SELECT IFNULL(MAX(camp_number), 0)+1 FROM camps camps_max)"), 0);
	
	$inst_id = 8;
	
	$sql = "INSERT INTO camps (camp_name, camp_name_he, camp_gender, camp_address1, camp_address2, camp_city, camp_state, camp_country, camp_postal, camp_phone, camp_era, camp_logo_id, camp_number, start_date, end_date) VALUES (";
	$sql = $sql . "'" . mysql_real_escape_string($camp_name) . "', '";
	$sql = $sql . mysql_real_escape_string($camp_name_he) . "', '";
	$sql = $sql . mysql_real_escape_string($camp_gender) . "', '";
	$sql = $sql . mysql_real_escape_string($camp_address1) . "', '";
	$sql = $sql . mysql_real_escape_string($camp_address2) . "', '";
	$sql = $sql . mysql_real_escape_string($camp_city) . "', '";
	$sql = $sql . mysql_real_escape_string($camp_state) . "', '";
	$sql = $sql . mysql_real_escape_string($camp_country) . "', '"; 
	$sql = $sql . mysql_real_escape_string($camp_postal) . "', '";
	$sql = $sql . mysql_real_escape_string($camp_phone) . "', 1, ";
	$sql = $sql . $camp_logo_id . ", ";
	$sql = $sql . $camp_number . ", ";
	$sql = $sql . $start_date . ", ";
	$sql = $sql . $end_date . ")";	
	$query = mysql_query($sql);
	if ($query) {
		$camp_id = mysql_insert_id();
			
		$sql = "INSERT INTO admins ";
		$sql = $sql . "(username, camp_id, auth, password, title, first, last, admin_email, admin_phone_mobile, admin_phone_work, admin_phone_home) VALUES ";
		$sql = $sql . "('" . mysql_real_escape_string($username) . "', " . $camp_id . ", 'inactive', '" . mysql_real_escape_string($password) . "', '" . mysql_real_escape_string($title) . "', '" . mysql_real_escape_string($first) . "', '" . mysql_real_escape_string($last) . "', '" . mysql_real_escape_string($admin_email) . "', '" . mysql_real_escape_string($admin_phone_mobile) . "', '" . mysql_real_escape_string($admin_phone_work) . "', '" . mysql_real_escape_string($admin_phone_home) . "')";
		$query = mysql_query($sql);
		if ($query)
			$message = mysql_insert_id();		

		$sql = "INSERT INTO admin_auths (admin_id, auth, id, role_id) VALUES (" . $admin_id . ", 'camp', " . $camp_id . ", 35)";
		mysql_query($sql);

		$sql = "SELECT * FROM default_group_types";
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			if ($row['logo_id'] > 0) 
				$insert = "INSERT INTO group_types SET camp_id=" . $camp_id . ", group_type_name='" . mysql_real_escape_string($row['group_type_name']) . "', logo_id=" . $row['logo_id'] . ", has_divisions=1";
			else
				$insert = "INSERT INTO group_types SET camp_id=" . $camp_id . ", group_type_name='" . mysql_real_escape_string($row['group_type_name']) . "', has_divisions=1";
			mysql_query($insert);			
			$group_type_id = mysql_insert_id();
				
			$sql2 = "SELECT * FROM default_divisions";
			$query2 = mysql_query($sql2);
			while ($row2 = mysql_fetch_assoc($query2)) {
				$insert2 = "INSERT INTO divisions SET group_type_id=" . $group_type_id . ", division_name='" . mysql_real_escape_string($row2['division_name']) . "'";
				mysql_query($insert2);
			}
				
		}		
	}
	else {
		$message = "Camp not registered. Please review your data and try again.";
	}
	
	echo $message;
}
?>

<? if ($admin_id == 0) : ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
	<head>

		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		
		<title>Hachayal Kiosk - Admin</title>
		
		<link rel="alternate" media="print" href="index.php">
		<link href="styles/reset.css" rel="stylesheet" type="text/css" />
		<link href="styles/styles.css" rel="stylesheet" type="text/css" />
		<link href="styles/print.css" rel="stylesheet" type="text/css" media="print" />		
		
		<script src="scripts/jquery.tools.min.js"></script>
		<script src="scripts/jquery.color.min.js"></script>
		<script src="scripts/jquery.styleselect.js"></script>
		<script src="scripts/jquery.form.js"></script>
		<script src="scripts/jquery.jeditable.min.js"></script>
		<script src="scripts/jquery.editableText.js"></script>
		<script src="scripts/scripts.js"></script>	
		<script src="scripts/jquery.blockui.js"></script>
		<script>	
			$(document).ready(function() {	
				$.blockUI.defaults.css = {};
				$.blockUI.defaults.overlayCSS = {}; 
			
				var currentTime = new Date();
				var month = currentTime.getMonth() + 1;
				var day = 1; //currentTime.getDate();
				var year = currentTime.getFullYear();		
				var date = (month + "/" + day + "/" + year);				
				document.register_camp_form.start_date.value = date;
				var date = ((month + 3) + "/" + day + "/" + year);
				document.register_camp_form.end_date.value = date;
			
				$('#register_camp_form').ajaxForm({						
					beforeSubmit: function() {
						if ($('#camp_photo').value != null) {
							alert($('#camp_photo').value);
							$.blockUI({ message: '<h1><img src="busy.gif" /> Uploading camper photo...</h1>' });
						}						
					},
					success: function(admin_id){
						if (isNaN(admin_id) == true) {
							alert(admin_id);
						}
						else {
							var expiry_date = new Date();
							expiry_date.setDate(expiry_date.getDate() + 1);
							document.cookie = "admin_id=" + escape(admin_id) + ";expires=" + expiry_date.toUTCString();						
							window.location = "http://www.mashpia.com/camps/index.php";
						}
					}
				});
			
				$('form a.submit').click( function(e) {
					e.preventDefault();
					
					if (document.getElementById('password').value != document.getElementById('password2').value) { 
						alert("Passwords don't match."); 
						document.getElementById('password').focus(); 
					}
					else {
						var camp_name =  document.getElementById("camp_name").value;
						var function_name = "check_camp_name";
						var parameters = [camp_name];						
						var url = "includes/get_functions.php?function_name=" + function_name + "&parameters=" + parameters;
						$.getJSON(url, function(no_of_usernames) {
							if (no_of_usernames == 0) {							
								var username = document.getElementById("username").value;
								var function_name = "check_admin_username";
								parameters = [username];
								var url = "includes/get_functions.php?function_name=" + function_name + "&parameters=" + parameters;
								$.getJSON(url, function(no_of_usernames) {
									if (no_of_usernames == 0) {
										$('form').submit();
									}
									else {
										$('#username').focus();
										alert("User name already taken. Please eneter a new one.");
									}
								});							
							}
							else {
								$('#camp_name').focus();
								alert("Camp name already taken. Please eneter a new one.");
							}
						});						
					}					
				});
				
			});
		</script>
	</head>

	<body>
	
		<div class="slider">
		
			<div id="wrapper">
			
				<div id="nav">
					<div class="col_title_bg"></div>
					<div class="col_title">Menu</div>
					<ul class="list_first">
						<li class="list_parent"><a href="#dashboard"><img src="images/icon_dashboard.png" width="22" height="22" alt="Camp Registration" /> Camp Registration</a></li>
							<ul class="list_second">
								<li><a href="#"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /> Registration</a></li>
								<li><a href="#"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /> Account Setup</a></li>
								<li><a href="#"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /> Shipping</a></li>
								<li><a href="#"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /> Package</a></li>
								<li><a href="#"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /> Billing</a></li>
								<li><a href="content.php?output=gettingstarted"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /> Getting Started</a></li>
							</ul>
					</ul>
				</div>
				<div id="content">
					<div class="col_title_bg"></div>
					<div class="slider_container">
						<link href="styles/dateinput.css" rel="stylesheet" type="text/css" />
						<div class="slider">
							<div class="col_title"><span>Camp Registration</span></div>
							<div class="col_content form">
								<div class="module" id="module-info">
									<h1>Just Think</h1>
									<div class="module_content">
										<p>As an official Tzivos Hashem base, your school will access over $1,200,000<a href="#thanks">*</a> of programming, school curricula and technology during the coming year.</p>
										<p>To help you take full advantage of this program, we ask you to confirm that:</p>
										<ul>
											<li>You are the school principal responsible for supervising Tzivos Hashem.</li>
											<li>You are familiar with the basic format of this program, and how it works seamlessly with your school's curricula. (Full support is available at all times.)</li>
											<li>You have designated or will designate a program director who is fully committed to the ongoing growth of Tzivos Hashem on your base.</li>
										</ul>
									</div>
								</div>
								<h1>Tell us about your camp</h1> 

								<!-- ****************************** REGISTRATION FORM ****************************** -->
								<form name="register_camp_form" id="register_camp_form" action="register_camp.php" method="post" accept-charset="UTF-8" >
									<input type="hidden" name="action" value="register">
									
									<div class="module" id="module-info">
										<h1>Camp Info</h1>
										<div class="module_content list">
											<ul>
												<li>
													<span class="label">Camp Name</span>
													<span class="input">
														<input type="text" name="camp_name" id="camp_name"/>
													</span>
													<div class="clear"></div>
												</li>
												
												<li>
													<span class="label">Camp Name in Hebrew</span>
													<span class="input">
														<input type="text" name="camp_name_he" />
													</span>
													<span class="tip">(This is how it will appear on the camp banner)<br />Don't have Hebrew? <a href="http://www.mikledet.com" target="_blank">www.mikledet.com</a></span>
													<div class="clear"></div>
												</li>
												
												
												<li>
													<span class="label">Camp Photo</span>
													<span class="input"><input name="camp_photo" type="file"></span>
													<span class="tip">Maximum file size: 2MB. Minimum size: 180x225 (Larger is OK, the desired aspect ratio is: 1.25 times as high, as it is wide)</span>
													<div class="clear"></div>
												</li>
												
												<li>																						
													<span class="label">Camp Start Date</span>
													<span class="input">
														<input type="text" name="start_date" id="start_date" value="" class="date" />
													</span>
													<span class="tip">(When the camp season begins)</span>
													<div class="clear"></div>
												</li>
												
												<li>
													<span class="label">Camp End Date</span>
													<span class="input">
														<input type="text" name="end_date" id="end_date" value="" class="date" />
													</span>
													<span class="tip">(When the camp season ends)</span>
													<div class="clear"></div>											
												</li>
												
												
												<li>
													<span class="label">Gender</span>
													<span class="input">
														<input type="radio" name="camp_gender" value="M" >Boys
														<input type="radio" name="camp_gender" value="F" />Girls 
														<input type="radio" name="camp_gender" value="B" />Both </span>
													<div class="clear"></div>
												</li>
											</ul>
										</div>
									</div> <!-- <div class="module" id="module-info"> -->
									
									<div class="module" id="module-info">
										<h1>Camp Address</h1>
										<div class="module_content list">
											<ul>
												<li>
													<span class="label">Address</span>
													<span class="input">
														<input type="text" name="camp_address1" />
														<input type="text" name="camp_address2" />
													</span>
													<div class="clear"></div>
												</li>
												<li>
													<span class="label">City</span>
													<span class="input">
														<input type="text" name="camp_city" />
													</span>
													<div class="clear"></div>
												</li>
												<li>
													<span class="label">State</span>
													<span class="input">
														<input type="text" name="camp_state" />
													</span>
													<div class="clear"></div>
												</li>
												<li>
													<span class="label">Postal Code</span>
													<span class="input">
														<input type="text" name="camp_postal" />
													</span>
													<div class="clear"></div>
												</li>
												<li>
													<span class="label">Country</span>
													<span class="input">
														<input type="text" name="camp_country" />
													</span>
													<div class="clear"></div>
												</li>
												<li>
													<span class="label">Phone</span>
													<span class="input">
														<input type="text" name="camp_phone" />
													</span>
													<div class="clear"></div>
												</li>
											</ul>
										</div>
									</div> <!-- <div class="module" id="module-info"> -->
									
									<h1>Camp Director</h1> 
									
									<div class="module" id="module-info">
										<h1>Director Info</h1>
										<div class="module_content list">
											<ul>
												<li>
													<span class="label">Title</span>
													<span class="input">
														<select name="title">
															<option>Rabbi</option>
															<option>Mr.</option>
															<option>Mrs.</option>
															<option>Ms.</option>
														</select>
													</span>
													<div class="clear"></div>
												</li>
												
												<li>
													<span class="label">First Name</span>
													<span class="input">
														<input type="text" name="first" />
													</span>
													<div class="clear"></div>
												</li>
												
												<li>
													<span class="label">Last Name</span>
													<span class="input">
														<input type="text" name="last" />
													</span>
													<div class="clear"></div>
												</li>
												
												<li>
													<span class="label">Mobile Phone</span>
													<span class="input">
														<input type="text" name="admin_phone_mobile" />
													</span>
													<div class="clear"></div>
												</li>											
											
												<li>
													<span class="label">Work Phone (+ext)</span>
													<span class="input">
														<input type="text" name="admin_phone_work" />
													</span>
													<div class="clear"></div>
												</li>
												
												<li>
													<span class="label">Home Phone</span>
													<span class="input">
														<input type="text" name="admin_phone_home" />
													</span>
													<div class="clear"></div>
												</li>
												<li>
													<span class="label">Email Address</span>
													<span class="input">
														<input type="text" name="admin_email" />
													</span>
													<div class="clear"></div>
												</li>
		
											</ul>
										</div>
									</div>
								
									<div class="module" id="module-info">
										<h1>Director Login</h1>
										<div class="module_content list">
											<ul>
												<li>
													<span class="label">Username</span>
													<span class="input">
														<input type="text" name="username" id="username" />
													</span>
													<div class="clear"></div>
												</li>
												
												<li>
													<span class="label">Password</span>
													<span class="input">
														<input type="password" name="password" id="password" />
													</span>
													<div class="clear"></div>
												</li>
												
												<li>
													<span class="label">Re-enter Password</span>
													<span class="input">
														<input type="password" name="password2" id="password2" />
													</span>
													<div class="clear"></div>
												</li>
												
											</ul>
										</div>
									</div>
									
									<div class="bot_nav">
										<a href="#" title="Save" class="button submit">Register And Continue</a>
										<!--<input type="submit" class="button register_camp" value="Register &amp; Continue"/>-->
									</div>
								</form>
								<!-- ****************************** REGISTRATION FORM ****************************** -->
							
							</div>
							
						</div>
						
					</div>
					
				</div>
				
			</div>
			
			<div id="overlay" class="narrow">
				<div class="content">
				</div>
			</div>
			
			</div>
			
		</body>
	
</html>

<? endif; ?>