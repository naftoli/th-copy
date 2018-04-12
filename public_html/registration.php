<?php
session_start();
if ( (isset( $_GET['type'] ) && $_GET['type'] == 'hschool') || (isset( $_POST['type'] ) && $_POST['type'] == 'hschool') ) {
    $_SESSION['hschool'] = 1;
} else {
    $_SESSION['hschool'] = 0;
}

include("db.php");
$next_page = "false";

$item_no = 0;
if (isset($_POST["action"])) {
	$action = $_POST["action"];
    $message = "";
	
	foreach ($_POST as $k => $v) {
		$_POST[$k] = mysql_real_escape_string(trim($v));
	}

	if ($action == "update") {
		$admin_id = $_POST["admin_id"];		
		$school_id = $_POST["school_id"];		
		
		$sql = "UPDATE admins SET title='" . $_POST['title']  ."', first='" . $_POST['first'] . "', last='" . $_POST['last'] . "', admin_phone_mobile='" . $_POST['admin_phone_mobile'] . "', admin_email='" . $_POST['admin_email'] . "', admin_phone_work='" . $_POST['admin_phone_work'] . "', admin_phone_home='" . $_POST['admin_phone_home'] . "' WHERE admin_id=" . $admin_id;
		$query = mysql_query($sql);
		if (!$query) {
			//echo $sql . mysql_error();
			$message = "<span style='color:red;'>Administrator not updated. Please try again.</span></a>";
		}
		
		// update principal info and chidon info
		$sql = "update schools
				set principal = '" . mysql_real_escape_string($_POST['p_name']) . "',
				principal_number = '" . mysql_real_escape_string($_POST['p_number']) . "',
				principal_email = '" . mysql_real_escape_string($_POST['p_email']) . "'";
		if (intval($_POST['chidon']) == 1) {
			$sql .= ", chidon = 1,
					chidon_name = '" . mysql_real_escape_string($_POST['chidon_name']) . "',
					chidon_number = '" . mysql_real_escape_string($_POST['chidon_number']) . "',
					chidon_email = '" . mysql_real_escape_string($_POST['chidon_email']) . "'";
		} else {
			$sql .= ", chidon = 0";
		}
		$sql .= " where school_id = " . $school_id;
		if (!mysql_query($sql)) {
			$message = "<span style='color:red;'>Principal Info and / or Chidon Info not updated.</span></a>";
		}
	}
	else {
		$school_id = 0;
		
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
			
			// save principal and chidon info to session
			$_SESSION['p_name'] 		= $_POST['p_name'];
			$_SESSION['p_number'] 		= $_POST['p_number'];
			$_SESSION['p_email'] 		= $_POST['p_email'];
			$_SESSION['chidon'] 		= $_POST['chidon'];
			$_SESSION['chidon_name'] 	= $_POST['chidon_name'];
			$_SESSION['chidon_number'] 	= $_POST['chidon_number'];
			$_SESSION['chidon_email'] 	= $_POST['chidon_email'];
		} else {
			//echo $sql . mysql_error();
			$message = "<span style='color:red;'>Administrator not added. Please try again.</span></a>";
		    //echo mysql_error();
		}			
	}

	if ($message=="") {
		$next_page = "true";
	}
		
}
// first time through
else {
	if (isset($_SESSION["admin_id"])) 		// mmc
		$admin_id = $_SESSION["admin_id"];  // mmc
		
	if ( isset( $admin_id ) && $admin_id > 0) {
		if ( isset( $_POST['school_id'] ) ) 
            $school_id = $_POST['school_id'];
		if ( isset( $_GET['school_id'] ) ) {
		    $school_id = $_GET['school_id'];
		}
		
		if ( isset( $school_id ) ) {
    		$sql = "SELECT * FROM schools WHERE school_id=" . $school_id;
    		$query = mysql_query($sql);
    		$school_info = mysql_fetch_assoc($query);
    		$school_name = $school_info["school_name"];
    		$admin_school_id = $school_id;
    		
    		//find out if school is hebrew school
    		if ( $row['inst_id'] == 4 ) {
    		    $_SESSION['hschool'] = 1;
    		}
        } else {
            $school_id = 0;
            $admin_school_id = 0;
        }
		
		include("classes/admin.php");
		$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$admin = new admin($row);	
	}
	else {
		$admin_id = 0;
		$school_id = 0;
	}
}
$h_school = $_SESSION['hschool'];
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
			var admin_id = <?=$admin_id;?>;
			var school_id = <?=$school_id;?>;
			var hschool = "<?=($h_school)?1:0?>";
			
			$(function() {
				$("#nav").height($("#content").height());
                
                $("#username").blur( function() { 
                    var username = $("#username").val();
                    $.post('ajax/checkUsername.php', {user : username}, function(data) {
                        if (data == 1) {
                            alert('This username is already in use.\nPlease choose another one.');
                            $("#username").focus().select();
                        }
                    });
                })
			});

			function check_next_page() {
				if (next_page) {		
					var registration_form_two = document.forms["registration_form_two"];
					registration_form_two.elements["admin_id"].value = admin_id;
                    registration_form_two.elements["school_id"].value = school_id;
                    registration_form_two.elements["hschool"].value = hschool;					
					registration_form_two.submit();
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
				
				var chidon = false;
				$(".chidon").each( function() {
					if ($(this).is(":checked")) {
						chidon = $(this).val();
					}
				});
				if (chidon === false) {
					alert("You must indicate whether you are a chidon school or not.");
					return false;				
				} else if (chidon == 1) {
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
				return checkTerms();
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
		
		<FORM name="registration_form_two" method="post" action="https://mashpia.com/registration_2.php">
			<input type="hidden" name="admin_id" value="">
			<input type="hidden" name="school_id" value="">
			<input type="hidden" name="hschool" value="" />
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
								<? if (isset($admin_id) && $admin_id > 0) : ?>
								<input type="hidden" name="action" value="update">
								<? else : ?>
								<input type="hidden" name="action" value="add">
								<? endif; ?>
								<input type="hidden" name="school_id" value="<?=$admin_school_id;?>">
								<input type="hidden" name="admin_id" value="<?=$admin_id;?>">
								
								<? if ( $h_school ) { ?>
								<input type="hidden" name="type" value="hschool" />
								<? } ?>
								
								<p>* denotes mandatory field</p>
								<h2>Principal's Info</h2>
								<div class="module" id="module-info">
								
									<div class="module_content">
									
										<div class="lists form">
											
											<ul>
												<li>
													<span class="label"><label for="p_name">*Name</label></span>
													<span class="label"><input name="p_name" type="text" id="p_name" value="<?=isset($school_info['principal'])?$school_info['principal']:''?>" /></span>
												</li>
												<li>
													<span class="label"><label for="p_number">*Contact Number</label></span>
													<span class="label"><input name="p_number" type="text" id="p_number" value="<?=isset($school_info['principal_number'])?$school_info['principal_number']:''?>" /></span>
												</li>
												<li>
													<span class="label"><label for="p_email">*Email</label></span>
													<span class="label"><input name="p_email" type="text" id="p_email" value="<?=isset($school_info['principal_email'])?$school_info['principal_email']:''?>" /></span>
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
													<span class="label"><label for="title">Title</label>
													</span>
													
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
													<span class="label"><label for="email">*Email Address</label></span>
													<span class="label"><input class="required email" name="admin_email" id="admin_email" type="email" value="<?=isset($admin->admin_email)?$admin->admin_email:'';?>" required /></span>
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
								
								<h2>Chidon Info</h2>
								<div class="module" id="module-info">
								
									<div class="module_content">
									
										<div class="lists form">
											
											<ul>
												<li>
													<p style="font-size: 16px; font-weight: bold;">*Is your school a chidon base?</p>
												</li>
												<li>
													<input type="radio" name="chidon" value="0" class="chidon" /> No<br />
													<input type="radio" name="chidon" value="1" class="chidon" /> Yes
												</li>
												<li></li>
												<div id="chidonInfo" style="display: none">
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
								
								<? if (!isset($admin_id) || $admin_id == 0) : ?>
								<h2>Login Info</h2> 
								<div class="module" id="module-info">
									<div class="module_content">
										<div class="lists form">
											<ul>
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
												<!--
												<li>
													<span class="label"><label for="lang">*Language</label></span>
													<span class="input">
														<select name="lang" class="select">
															<option value="0" disabled="disabled">Please Select</option>
															<? if ($admin->lang == "en") : ?>
															<option value="en" selected>English</option>
															<? else : ?>
															<option value="en">English</option>													  
															<? endif; ?>
															<? if ($admin->lang == "he") : ?>
															<option value="he" selected>עברית</option>
															<? else : ?>
															<option value="he">עברית</option>
															<? endif; ?>
															<? if ($admin->lang == "yi") : ?>
															<option value="yi" selected>יידיש</option>
															<? else : ?>
															<option value="yi">יידיש</option>
															<? endif; ?>													  
														</select>
													</span>
												</li>
												-->
											</ul>
										</div>
									</div>
								</div>
								<? endif; ?>
								
								<? if ( !$h_school ) { ?>
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
                                                            I will ensure that I will provide all my teachers email addresses so we can be in touch with them to provide resources.
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
								<? } else { ?>
								    <input id="Continue" type="submit" value="Continue" class="button">
								<? } ?>							
							</form> 
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
	<script>
		$(".chidon").click( function() {
			if ($(this).val() == 1) {
				$("#chidonInfo").show();
			} else {
				$("#chidonInfo").hide();
			}
		});
		
		<?php
		if (isset($school_info['chidon'])) {
			if (intval($school_info['chidon']) == 1) {
				?>
				$(".chidon").eq(1).trigger('click');
				<?
			} else {
				?>
				$(".chidon").eq(0).trigger('click');
				<?
			}
		}
		?>
	</script>
</html> 
