<?php
session_start();
include("db.php");
include("check_admin_id.php");

$next_page = "false";

$message = "";
if (isset($_POST["action"])) {
	$director_type = $_POST["director_type"];

	$sql = "SELECT school_name FROM schools WHERE school_id=" . $_POST['school_id'];
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$school_name = $row["school_name"];
	
	if ($director_type == "director") {
		$sql = "UPDATE admin_auths SET role_id=16 WHERE admin_id=" . $admin_id;
		$query = mysql_query($sql);
		
		if (!$query) {
			$message = "Update failed, Please try again.";
		}
		else {
			createInviteTwo($_POST["email"], $_POST['school_id'], 'school', 18, $school_name, $admin_id);	
		}
			
		//$to = $_POST["email"];
		//$subject = "Program Director Invitation";
		//$email_message = "You have been invited to be Program Director.";		
		//$headers = "From: gcalder@dropzone.com\r\nReply-To: gcalder@dropzone.com";
		//$mail_sent = @mail($to, $subject, $email_message, $headers);
	}
	else {
		$sql = "UPDATE admin_auths SET role_id=18 WHERE admin_id=" . $admin_id;
		$query = mysql_query($sql);
		
		if (!$query)
			$message = "Update failed, Please try again.";		
	}
	
	if ($message == "") {
		$next_page = "true";
		//header("Location: http://www.mashpia.com/registration_4.php");
	}
}

include("camps/includes/classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
$admin->get_school_id();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" dir="<?=$dir?>">

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<title>School Registration</title>
		<link rel="alternate" media="print" href="index.php">
		<link href="admin_styles.css" rel="stylesheet" type="text/css" />
		<script src="camps/scripts/jquery.tools.min.js"></script>
		<script src="scripts/jquery.placeholder.js"></script>
		
		<script>
			var next_page = "<?=$next_page;?>";
			var admin_id = <?=$admin_id;?>;
			var school_id = <?=$admin->school_id;?>;
		
			$(function() {
				$("#nav").height($("#content").height());
				$('input').placeholder();
				
				$('.toggle').hide();
				$('input[type="radio"][value="director"]:checked').parents('ul').find('.toggle').show();
				$('input[type="radio"]').change(function(){
						$(this).parents('ul').find('.toggle').slideUp('fast');
						$(this).filter('[value="director"]:checked').parents('ul').find('.toggle').slideDown('fast');
				});
			});


			function validate_email_address() {
				var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
				var address = document.forms["form_3"].elements["email"].value;
				if (reg.test(address) == false) {
				  return false;
			   }
			}			
			
			function check_director_type() {
				var director = document.getElementById("director");
			
				if (director.checked)
					var valid_email = validate_email_address();
				else
					var valid_email = true;
					
				if (valid_email == false) {
					alert("Invalid email address");
					return false;
				}
				else {	
					var principal = document.getElementById("principal");
					
					if (director.checked == false && principal.checked == false) {
						alert("You must choose a director type.");
						return false;
					}
					else {
						return true;
					}				
				}
			}
			
			function check_next_page() {
				if (next_page == "true") {
					var registration_form_four = document.forms["registration_form_four"];
					registration_form_four.elements["admin_id"].value = admin_id;
					registration_form_four.elements["school_id"].value = school_id;
					registration_form_four.submit();
				}
			}			
		</script>
		<!--Copyright Ariel Shkedi 2007-2010-->
	</head>

	<body onload="check_next_page();">
	
		<FORM name="registration_form_four" method="post" action="https://mashpia.com/registration_4.php">
			<input type="hidden" name="admin_id" value="">
			<input type="hidden" name="school_id" value="">
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
						<div class="col_content left">
						<h1>School Registration</h1>
	 
							<form id="form_3" action="registration_3.php" method="post" accept-charset="UTF-8" onsubmit="return check_director_type();"> 
								<input type="hidden" name="action" value="update">
								<input type="hidden" name="school_id" value="<?=$admin->school_id;?>">
								<input type="hidden" name="admin_id" value="<?=$admin_id;?>">
	
								<? if ($message != "") : ?>
									<?=$message;?>
								<? endif; ?>
	
								<div class="infobox">
									<div class="module_content">
										  <span class="label">For the program to work properly in your school you need a designated Program Director to run the program in your school.</span>
									</div>
								</div>
								
								<div class="module" id="module-info">
									<div class="module_content">
										<div class="lists form">
											<ul>
												<li>
													<span class="input">
														<input name="director_type" id="principal" type="radio" value="principal" />I (the Principal) am taking the role of Program Director (Not Recommended!).
													</span>
												</li>
												
												<li>
													<span class="input">
														<input name="director_type" id="director" type="radio" value="director" />I have dedicated another staff member to be our Program Director, please invite him/her to create his/her own account.
													</span>
												</li>
												
												<li class="toggle">
													<span class="label"><label for="email">Email Address</label></span>
													<span class="input"><input name="email" id="email" type="text" /></span>
												</li>
												
												<li>
													<input type="submit" value="Continue" class="button"> 
												</li>
											</ul>
										</div>
									</div>
								</div>
							</form> 
							
						</div>
					</div>
				</div>
			</div>
		</div>


	</body>
	
</html>
