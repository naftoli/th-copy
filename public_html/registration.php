<?php
session_start();

$admin_id = isset($_SESSION["admin_id"]) ? $_SESSION["admin_id"] : 0;
$school_id = isset($_SESSION["school_id"]) ? $_SESSION["school_id"] : 0;

include("db.php");
$next_page = "false";

if (isset( $_REQUEST['admin_id'] )) {
	$admin_id = $_REQUEST['admin_id'];
	$_SESSION['admin_id'] = $admin_id;
} 
if (isset( $_REQUEST['school_id'] )) {
	$school_id = $_REQUEST['school_id'];
	$_SESSION['school_id'] = $school_id;
}

if (isset( $_GET['skip'] ) && $_GET['skip'] == 'shimmy') {
	// we will need to skip the cc part at the end
	$_SESSION['skipCC'] = 'yes';
}

if (isset($_POST["action"])) {
	$action = $_POST["action"];
	$message = "";
	
	foreach ($_POST as $k => $v) {
		$_POST[$k] = mysql_real_escape_string(trim($v));
	}

	if ($action == "update") {		
		$sql = "UPDATE admins SET title='" . $_POST['title']  ."', first='" . $_POST['first'] . "', last='" . $_POST['last'] . "', admin_phone_mobile='" . $_POST['admin_phone_mobile'] . "', admin_phone_work='" . $_POST['admin_phone_work'] . "', admin_phone_home='" . $_POST['admin_phone_home'] . "' WHERE admin_id=" . $admin_id;
		$query = mysql_query($sql);
		if (!$query) {
			//echo $sql . mysql_error();
			$message = "<span style='color:red;'>Administrator not updated. Please try again.</span></a>";
		}

		$school_type = $_SESSION['school_type'] = $_POST['school_type'];
		
		// update principal info and school type / chidon info
		$sql = "update schools
				set principal = '" . mysql_real_escape_string($_POST['p_name']) . "',
				principal_number = '" . mysql_real_escape_string($_POST['p_number']) . "',
				principal_email = '" . mysql_real_escape_string($_POST['p_email']) . "'";

		switch ($school_type) {
			case 'chayolei':
				$sql .= ", chayolei = 1, chidon = 1, tanya = 1";
				$sql .= ", chidon_name = '" . mysql_real_escape_string($_POST['chidon_name']) . "',
						chidon_number = '" . mysql_real_escape_string($_POST['chidon_number']) . "',
						chidon_email = '" . mysql_real_escape_string($_POST['chidon_email']) . "'";
				break;
			case 'chidon':
				$sql .= ", chayolei = 0, chidon = 1, tanya = 0";
				$sql .= ",chidon_name = '" . mysql_real_escape_string($_POST['chidon_name']) . "',
						chidon_number = '" . mysql_real_escape_string($_POST['chidon_number']) . "',
						chidon_email = '" . mysql_real_escape_string($_POST['chidon_email']) . "'";
				break;
			case 'tanya':
				$sql .= ", chayolei = 0, chidon = 0, tanya = 1";
				break;
		}

		$sql .= " where school_id = " . $school_id;

		if (!mysql_query( $sql )) {
			$message = "<span style='color:red;'>Principal Info and / or Chidon Info not updated.</span></a>";
		}
	} else {
		if ( $_POST['has_account'] == 'true' ) {
			$sql = 'SELECT admin_id FROM admins WHERE username = "'.$_POST['old_username'].'" AND password = "'. $_POST['old_password'] .'"';
			$have_admin = mysql_query($sql);
			if ( !$have_admin ) {
				$message = "<span style='color:red;'>Administrator not found. Please try again.</span></a>";
			} else {
				$admin_id = mysql_fetch_assoc( $have_admin )['admin_id'];
				$sql = "UPDATE admins SET 
					title='" . 				$_POST['title']  ."', 
					first='" . 				$_POST['first'] . "', 
					last='" . 				$_POST['last'] . "', 
					admin_phone_mobile='" . $_POST['admin_phone_mobile'] . "', 
					admin_phone_work='" . 	$_POST['admin_phone_work'] . "', 
					admin_phone_home='" . 	$_POST['admin_phone_home'] . "'
					WHERE admin_id='$admin_id'";
				$query = mysql_query($sql);
				if ($query) {
					$_SESSION['admin_id'] = $admin_id;
				} else {
					$message = "<span style='color:red;'>Administrator not updated. Please try again.</span></a>";
				}
			}
		} else {
			$sql = "INSERT INTO admins SET 
				username='" . 			$_POST['username'] . "', 
				password='" . 			$_POST['password'] . "', 
				title='" . 				$_POST['title']  ."', 
				first='" . 				$_POST['first'] . "', 
				last='" . 				$_POST['last'] . "', 
				admin_phone_mobile='" . $_POST['admin_phone_mobile'] . "', 
				admin_email='" . 		$_POST['admin_email'] . "', 
				admin_phone_work='" . 	$_POST['admin_phone_work'] . "', 
				admin_phone_home='" . 	$_POST['admin_phone_home'] . "'";
			$query = mysql_query($sql);
			if ($query) {
				$admin_id = mysql_insert_id();
				$_SESSION['admin_id'] = $admin_id;
			} else {
				$message = "<span style='color:red;'>Administrator not added. Please try again.</span></a>";
			}
		}
		// save session info
		$_SESSION['school_id'] = $school_id;
		$_SESSION['school_type']	= $_POST['school_type'];
		
		// save principal and chidon info to session
		$_SESSION['p_name'] 		= $_POST['p_name'];
		$_SESSION['p_number'] 		= $_POST['p_number'];
		$_SESSION['p_email'] 		= $_POST['p_email'];
		$_SESSION['chidon_name'] 	= $_POST['chidon_name'];
		$_SESSION['chidon_number'] 	= $_POST['chidon_number'];
		$_SESSION['chidon_email'] 	= $_POST['chidon_email'];			
	}

	if ($message=="") {
		$next_page = "true";
	}
		
}
// first time through
else {
	if ( $school_id > 0 ) {
		$sql = "SELECT * FROM schools WHERE school_id=" . $school_id;
		$query = mysql_query($sql);
		$school_info = mysql_fetch_assoc($query);
		$school_name = $school_info["school_name"];
		$admin_school_id = $school_id;
	} 
	
	include("classes/admin.php");
	$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$admin = new \classes\admin($row);	
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
		<script>
			var next_page = <?=$next_page;?>;
			
			$(function() {
				$("#nav").height($("#content").height());
                
                $("#username").blur( function() { 
                    var username = $("#username").val();
                    $.post('ajax/checkUsername.php', {user : username}, function(data) {
                        if (data == 1) {
                            alert('This username is already in use.\nPlease choose another one.');
                        }
                    });
                })
			});

			function check_next_page() {
				if (next_page) {		
					location.href = "registration_2.php";
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
			
			function checkTerms() {
				//check if terms was checked off
				var terms = ['responsible', 'designate', 'commited', 'agree'];
				for (t in terms) {
					var checkbox = '#' + terms[t];
					if (!$(checkbox).is(":checked")) {
						alert("You must accept all terms.");
						return false;
					}
				}				
			}
			
			function validate() {
				var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
				
				// make sure school type is checked
				var school_type =  false;
				if ($(".school_type:checked").length) {
					school_type = true;
					var school_type_val = $(".school_type:checked").val();
				}

				if (!school_type) {
					alert('You must indicate what type of school you have.');
					return false;
				}
								
				if (school_type_val == 'chayolei' || school_type_val == 'chidon') {
					// make sure chidon info is entered
					var cname = $("#chidon_name").val().trim();
					var cnumber = $("#chidon_number").val().trim();
					var cemail = $("#chidon_email").val().trim();
					if (cname == '' || cnumber == '' || cemail == '') {
						alert("You must enter your chidon coordinator name, number and email.");
						return false;
					}
					if (cname.length < 3) {
						alert("Coordinator name must be at least 3 characters.");
						return false;
					}
					if (cnumber.length < 9 || isAlphabetic(cnumber)) {
						alert("Coordinator number must be at least 9 digits and cannot contain alphabetic characters.");
						return false;
					}
					if (reg.test(cemail) !== true) {
						alert("Invalid coordinator email address.");
						return false;
					}
				}
				
				// principal info
				var p_name = $("#p_name").val().trim();
				var p_number = $("#p_number").val().trim();
				var p_email = $("#p_email").val().trim();
				if (p_name == '' || p_number == '' || p_email == '') {
					alert("You must enter the principal's info.");
					return false;
				}
				if (p_name.length < 3) {
					alert("Principal name must be at least 3 characters.");
					return false;
				}
				if (p_number.length < 9 || isAlphabetic(p_number)) {
					alert("Principal number must be at least 9 digits and cannot contain alphabetic characters.");
					return false;
				}
				if (reg.test(p_email) !== true) {
					alert("Invalid principal email address.");
					return false;
				}

				if (school_type_val == 'chayolei') return checkTerms();
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
			.new {
				font-size: 14px;
				color: red;
				font-weight: bold;
			}
			.school_type_info {
				font-size: 14px;
			}
			#ckids {
				margin: 3px 3px 0px 5px;
				padding-left: 20px;
			}
		</style>
	</head>

	<body onload="check_next_page();">
		
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
			
				<div class="col_title_bg"></div>
				
				<div class="slider_container">
				
					<div class="slider">
					
						<div class="col_title"></div>
						
						<div class="col_content">
						
							<h1>School Registration</h1>
							
							<? if (isset($message) && $message != "") : ?>
								<h1><?=$message;?></h1>
							<? endif; ?>
											
							<div class="infobox">
								<div class="module_content" style="position:relative; left:50px;">
									  <span class="label">
										<? if ( isset( $school_name ) ) { ?>
										You need to register school <b><?=$school_name;?></b><br />
										<? } ?>
										Please note: You will need a valid credit card to complete registration
									</span>
								</div>
							</div>
	 
							<form method="post" name="registration_form" id="registration_form" action="registration.php" accept-charset="UTF-8"> 
								<? if ($admin_id > 0) : ?>
								<input type="hidden" name="action" value="update">
								<? else : ?>
								<input type="hidden" name="action" value="add">
								<? endif; ?>
								
								<p>* denotes mandatory field</p>

								<h2>School Type</h2>
								<div class="module" id="module-info">
								
									<div class="module_content">
									
										<div class="lists form">
											
											<ul>
												<li>
													<p style="font-size: 16px; font-weight: bold;">*Please choose what type of school you are running:</p>
												</li>
												<li>
													<input type="radio" name="school_type" value="chayolei" class="school_type" /> 
													<span class='school_type_info'>Chayolei (includes Chidon / Tanya)</span><br />
													<input type="radio" name="school_type" value="chidon" class="school_type" /> 
													<span class='school_type_info'>Chidon Only</span><br />
													<input type="radio" name="school_type" value="tanya" class="school_type" /> 
													<span class='school_type_info'>Tanya Only</span><br />
													<!-- <input type="radio" name="school_type" value="ckids" class="school_type" /> 
													<span class='school_type_info'>CKids</span> <span class='new'>NEW</span><br />  -->
													<a href='/registration_ckids.php' id='ckids'>
														CKids <span class='new'>NEW</span>
													</a>
												</li>

												<div id="chidonInfo" style="display: none">
													<li></li>
													<li>
														<p style="font-size: 16px; font-weight: bold;">
															Please provide some information about your chidon coordinator:
														</p>
													</li>
													<li>
														<span class="label"><label for="chidon_name">*Name</label></span>
														<span class="label"><input name="chidon_name" type="text" id="chidon_name" value="<?=isset($school_info['chidon_name'])?$school_info['chidon_name']:''?>" /></span>
													</li>
													<li>
														<span class="label"><label for="chidon_number">*Contact Number</label></span>
														<span class="label"><input name="chidon_number" type="text" id="chidon_number" value="<?=isset($school_info['chidon_number'])?$school_info['chidon_number']:''?>" /></span>
													</li>
													<li>
														<span class="label"><label for="chidon_email">*Email</label></span>
														<span class="label"><input name="chidon_email" type="text" id="chidon_email" value="<?=isset($school_info['chidon_email'])?$school_info['chidon_email']:''?>" /></span>
													</li>
												</div>
											</ul>
											
										</div>
										
									</div>
									
								</div>		

								<div id="otherInfo" style="display: none">

									<h2>Principal's Info</h2>
									<div class="module" id="module-info">
									
										<div class="module_content">
										
											<div class="lists form">
												
												<ul>
													<li>
														<span class="label"><label for="p_name">*Name</label></span>
														<span class="label"><input name="p_name" type="text" id="p_name" value="<?=isset($school_info['principal'])?$school_info['principal']:''?>" required /></span>
													</li>
													<li>
														<span class="label"><label for="p_number">*Contact Number</label></span>
														<span class="label"><input name="p_number" type="text" id="p_number" value="<?=isset($school_info['principal_number'])?$school_info['principal_number']:''?>" required /></span>
													</li>
													<li>
														<span class="label"><label for="p_email">*Email</label></span>
														<span class="label"><input name="p_email" type="email" id="p_email" value="<?=isset($school_info['principal_email'])?$school_info['principal_email']:''?>" required /></span>
													</li>
												</ul>
												
											</div>
											
										</div>
										
									</div>
									
									<h2>Base Commander's Info</h2>

									<div class="module" id="module-info">
									
										<div class="module_content">
										
											<div class="lists form">
												
												<ul>
													<li>
														<span class="label"><label for="title">Title</label></span>
														
														<span class="input">
															<select name="title" class="select">
																<option value="0" disabled="disabled">Please Select</option>
																<? if ($admin->title == "Rabbi") : ?>
																<option value="Rabbi" selected>Rabbi</option>
																<? else : ?>
																<option value="Rabbi">Rabbi</option>
																<? endif; ?>
																
																<? if ($admin->title == "Mr.") : ?>
																<option value="Mr." selected>Mr.</option>
																<? else : ?>
																<option value="Mr.">Mr.</option>
																<? endif; ?>
																
																<? if ($admin->title == "Mrs.") : ?>
																<option value="Mrs." selected>Mrs.</option>
																<? else : ?>
																<option value="Mrs.">Mrs.</option>
																<? endif; ?>
																
																<? if ($admin->title == "Ms.") : ?>															
																<option value="Ms." selected>Ms.</option>
																<? else : ?>
																<option value="Ms.">Ms.</option>
																<? endif; ?>															
															</select>													
														</span>
													</li>
													<li>
														<span class="label"><label for="first">*First Name</label></span>
														<span class="label"><input class="required" name="first" type="text" value="<?=isset($admin->first)?$admin->first:'';?>" required /></span>
													</li>
													<li>
														<span class="label"><label for="last">*Last Name</label></span>
														<span class="label"><input class="required" name="last" type="text" value="<?=isset($admin->last)?$admin->last:'';?>" required /></span>
													</li>
													<li>
														<span class="label"><label for="mobile">*Mobile Phone</label></span>
														<span class="label"><input class="required" name="admin_phone_mobile" type="text" value="<?=isset($admin->admin_phone_mobile)?$admin->admin_phone_mobile:'';?>" required /></span>
													</li>
													<li>
														<span class="label"><label for="work">*Work Phone (+ext)</label></span>
														<span class="label"><input class="required" name="admin_phone_work" type="text" value="<?=isset($admin->admin_phone_work)?$admin->admin_phone_work:'';?>" required /></span>
													</li>
													<li>
														<span class="label"><label for="home">Home Phone</label></span>
														<span class="label"><input name="admin_phone_home" type="text" value="<?=isset($admin->admin_phone_home)?$admin->admin_phone_home:'';?>" /></span>
													</li>
												</ul>
											</div>
										</div>
									</div>
																	
									<? if (!isset($admin_id) || $admin_id == 0) : ?>
									<h2>Login Info</h2> 
									<div class="module" id="module-info">
										<div class="module_content">
											<div class="lists form">
												<ul>
													<li>
														<p style="font-size: 16px; font-weight: bold;">Do you have an existing Tzivos Hashem Account?</p>
													</li>
													<li>
														<input type="radio" name="has_account" value="true" class="has_account" /> 
														<span class='school_type_info'>Yes I do</span><br />
														<input type="radio" name="has_account" value="false" class="has_account" /> 
														<span class='school_type_info'>No I do not</span><br />
													</li>
													<div id="has_account" style="display: none">
														<li></li>
														<li>
															<p style="font-size: 16px; font-weight: bold;">
																Please provide your existing login information:
															</p>
														</li>
														<li>
															<span class="label"><label for="old_username">*Username</label></span>
															<span class="label"><input class="required" name="old_username" id="old_username" type="text" required /></span>
														</li>
														<li>
															<span class="label"><label for="old_password">*Password</label></span>
															<span class="label"><input class="required" name="old_password" id="old_password" type="password" required /></span>
														</li>
													</div>
													<div id="new_account" style="display: none">
														<li>
															<span class="label"><label for="email">*Email Address</label></span>
															<span class="label"><input class="required email" name="admin_email" id="admin_email" type="email" value="<?=isset($admin->admin_email)?$admin->admin_email:'';?>" required /></span>
														</li>
														<li>
															<span class="label"><label for="username">*Username</label></span>
															<span class="label"><input class="required" name="username" id="username" type="text" required /></span>
														</li>
														<li>
															<span class="label"><label for="password">*Password</label></span>
															<span class="label"><input class="required" name="password" id="password" type="password" required /></span>
														</li>
														<li>
															<span class="label"><label for="password2">*Re-enter Password</label></span>
															<span class="label"><input class="required" name="password2" id="password2" type="password" required /></span>
														</li>
													</div>
												</ul>
											</div>
										</div>
									</div>
									<? endif; ?>
									
									<div id="accept_terms" style="display:none">
										<h2>Accept Terms</h2> 
										<div class="module" id="module-info">
											<div class="module_content">
												<div class="lists form">
													<ul>
														<li>
															<span>To help you take full advantage of this program, we ask you to confirm that:</span>
														</li>
														<li>
															<span>
																<input class="required" type="checkbox" name="responsible" id="responsible" value="responsible">
																<label for="tac">
																	I am the base commander responsible for supervising Tzivos Hashem, and I pledge to fully understand the goal and mission of Tzivos Hashem and how it works seamlessly with my school’s curriculum.
																</label>
															</span>
														</li>
														<li>
															<span>
																<input class="required" type="checkbox" name="designate" id="designate" value="designate">
																<label for="tac">
																	I am fully committed to the ongoing growth of Tzivos Hashem on our base (school) and will attend the monthly base commanders meetings.
																</label>
															</span>
														</li>
														<li>
															<span>
																<input class="required" type="checkbox" name="commited" id="commited" value="designate">
																<label for="tac">
																	I will ensure that I will provide all my teachers email addresses and cell phone numbers so we can be in touch with them to provide resources.
																</label>
															</span>
														</li>
														<li>
															<span>
																<input class="required" type="checkbox" name="agree" id="agree" value="designate">
																<label for="tac">
																	I agree for my card to be charged the registration fee for every student that I  register(s) into the Tzivos Hashem program from my school. [Parents who register directly will pay their own registration fee(s).]
																</label>
															</span>
														</li>
														<li>
															<input id="Continue" type="submit" value="Continue" class="button" onclick="return validate()">
														</li>
													</ul>
												</div>
											</div>
										</div>
									</div>
									<div id="nonChayolei">
										<input id="Continue" type="submit" value="Continue" class="button" onclick="return validate()">
									</div>
								</div>
							</form> 
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
	<script>
		$(".school_type").click( function() {
			$("#otherInfo").show();
			var val = $(this).val();
			if (val == 'chayolei') {
				$("#chidonInfo").show();
				$("#accept_terms").show();
				$("#nonChayolei").hide();
			} else if (val == 'chidon') {
				$("#chidonInfo").show();
				$("#accept_terms").hide();
				$("#nonChayolei").show();
			} else {
				$("#chidonInfo").hide();
				$("#accept_terms").hide();
				$("#nonChayolei").show();
			}
		});

		$(".has_account").change( function() {
			if ( JSON.parse( $(this).val() ) ) {
				$("#has_account").show();
				$("#has_account input").attr('required', true);
				$("#new_account").hide();
				$("#new_account input").attr('required', false);
			} else {
				$("#has_account").hide();
				$("#has_account input").attr('required', false);
				$("#new_account").show();
				$("#new_account input").attr('required', true);
			}
		})
		
		<?php
		// when updating existing school automatically choose correct school type
		if ( $school_id > 0 ) {
			if ( $school_info['chayolei'] ) {
			?>
				$(".school_type").eq(0).trigger('click');
				$("#accept_terms").show();
			<?
			}
			else if ( $school_info['chidon'] ) {
			?>
				$(".school_type").eq(1).trigger('click');
			<?
			}
			else if ( $school_info['tanya'] ) {
			?>
				$(".school_type").eq(2).trigger('click');
			<?
			}
			else if ( $school_info['ckids'] ) {
			?>
				$(".school_type").eq(3).trigger('click');
			<?
			}
		}
		?>
	</script>
</html> 
