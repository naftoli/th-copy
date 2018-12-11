<?php
include("db.php");
include("check_admin_id.php");

$next_page = "false";

include("camps/includes/classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
$admin->get_school_id();
$school_id = $admin->school_id;
	
$message = "";	
if (isset($_POST["action"])) {

	if ($action == "update") {
		$sql = "UPDATE schools SET shipping_method='" . $_POST['shipping_method'] . "', shipping_first='" . $_POST['shipping_first'] . "', shipping_last='" . $_POST['shipping_last'] . "', shipping_address1='" . $_POST['shipping_address1'] . "', shipping_phone='" . $_POST['shipping_phone'] . "', shipping_address2='" . $_POST['shipping_address2'] . "', shipping_city='" . $_POST['shipping_city'] . "', shipping_state='" . $_POST['shipping_state'] . "', shipping_postal='" . $_POST['shipping_postal'] . "', shipping_country='" . $_POST['shipping_country'] . "' WHERE school_id=" . $school_id;
		$query = mysql_query($sql);
		
		if (!$query) {
			$message = "<span style='color:red;'>Update failed, Please try again.<span>";	
		}
		else {
			$next_page = "true";
			//header("Location: http://www.mashpia.com/registration_7.php");
		}
	}
	
}
else {
	header("https://www.mashpia.com/registration.php");
}

include("camps/includes/classes/school.php");
$sql = "SELECT * FROM schools WHERE school_id=" . $school_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$school = new school($row);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" dir="<?=$dir?>">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<title>Registration Wizard Tzivos Hashem Management System</title>
		<link rel="alternate" media="print" href="index.php">
		<link href="admin_styles.css" rel="stylesheet" type="text/css" />
		<script src="camps/scripts/jquery.tools.min.js"></script>
		<script src="scripts/jquery.placeholder.js"></script>
		
		<script>
			var next_page = "<?=$next_page;?>";
			var admin_id = <?=$admin_id;?>;
			var school_id = <?=$school_id;?>;
		
			$(function() {
				$("#nav").height($("#content").height());
				$('input').placeholder();
				
				$('.toggle').hide();
				$('input[type="radio"][value="pickup"]:checked').parents('ul').find('.toggle').show();
				$('input[type="radio"]').change(function(){
						$(this).parents('ul').find('.toggle').slideUp('fast');
						$(this).filter('[value="pickup"]:checked').parents('ul').find('.toggle').slideDown('fast');
				});
			});

			function check_radio_buttons() {
				var deliver = document.getElementById("deliver");
				var pickup = document.getElementById("pickup");
				
				if (deliver.checked == false && pickup.checked == false) {
					alert("You must choose a delivery type.");
					return false;
				}				
				else {
					return true;
				}
			}
			
			function check_next_page() {
				if (next_page == "true") {
					var registration_form_seven = document.forms["registration_form_seven"];
					registration_form_seven.elements["admin_id"].value = admin_id;
					registration_form_seven.elements["school_id"].value = school_id;
					registration_form_seven.submit();
				}
			}									
		</script>
		<!--Copyright Ariel Shkedi 2007-2010-->
	</head>

	<body onload="check_next_page();">
		<FORM name="registration_form_seven" method="post" action="https://www.mashpia.com/registration_7.php">
			<input type="hidden" name="admin_id" value="">
			<input type="hidden" name="school_id" value="">
		</FORM>
	
		<NOSCRIPT><P STYLE="color: red; font-size: larger;">Notice: You have javascript disabled. Some parts of the site will not function without javascript.</P></NOSCRIPT>
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
							<h1>Registration Wizard - Setup Shipping</h1>
	 
							<h1><?=$message;?></h1>
							
							<form action="registration_7.php" method="post" accept-charset="UTF-8" name="login" onsubmit="return check_radio_buttons();"> 
								<input type="hidden" name="action" value="update">
								<input type="hidden" name="school_id" value="<?=$school_id;?>">
								<input type="hidden" name="admin_id" value="<?=$admin_id;?>">
							
								<div class="module" id="module-info">
									<div class="module_content">
										<div class="lists form">
											<ul>
												<li>
													<div class="box">
														<h4><input name="shipping_method" id="deliver" type="radio" value="deliver" />Deliver any material to the address below.</h4>
													</div>
												</li>
												<li>
													<div class="box">
														<h4><input name="shipping_method" id="pickup" type="radio" value="pickup" onclick="alert('Please note: You will be charged a standard shipping rate for any item shipped to you.');" />I will pickup any material at TH Headquarters, 792 Eastern Parkway, Brooklyn, NY 11213.</h4>
													</div>
												</li>
												<li class="toggle">
													<div class="box">
														<p>PLEASE NOTE: We will notify you as soon as your materials are ready for pickup. Material not collected 7 days after notification will be shipped to the address below and subject to standard shipping rates.</p>
													</div>    
												</li>
											</ul>
										</div>
									</div>
								</div>
								
								<h2>Shipping Details</h2>
								<div class="module" id="module-info">
									<div class="module_content">
										<div class="lists form">
											<ul>
												<li>
													<span class="label"><label for="first">First Name</label></span>
													<span class="input"><input name="shipping_first" type="text" value="<?=$school->shipping_first;?>" /></span>
												</li>
												
												<li>
													<span class="label"><label for="last">Last Name</label></span>
													<span class="input"><input name="shipping_last" type="text" value="<?=$school->shipping_last;?>" /></span>
												</li>
												
												<li>
													<span class="label"><label for="phone">Phone</label></span>
													<span class="input"><input name="shipping_phone" value="<?=$school->shipping_phone;?>" type="text" /></span>
												</li>
												
												<li>
													<span class="label"><label for="address">Address</label></span>
													<span class="input"><input name="shipping_address1" value="<?=$school->shipping_address1;?>" type="text" /></span>
													<div class="clear"></div>
													<span class="label"></span>
													<span class="input"><input name="shipping_address2" value="<?=$school->shipping_address2;?>" type="text" /></span>
													<div class="clear"></div>
													<span class="label"></span>
													
													<? if ($school->shipping_city != "") : ?>
													<span class="input city"><input name="shipping_city" value="<?=$school->shipping_city;?>" type="text" /></span>
													<? else : ?>
													<span class="input city"><input name="shipping_city" type="text" placeholder="City" /></span>
													<? endif; ?>
													
													<? if ($school->shipping_state != "") : ?>
													<span class="input state"><input name="shipping_state" value="<?=$school->shipping_state;?>" type="text" /></span>
													<? else : ?>
													<span class="input state"><input name="shipping_state" type="text" placeholder="State" /></span>													
													<? endif; ?>
													
													<? if ($school->shipping_postal != "") : ?>
													<span class="input zip"><input name="shipping_postal" value="<?=$school->shipping_postal;?>" type="text" /></span>
													<? else : ?>
													<span class="input zip"><input name="shipping_postal" type="text" placeholder="Zip" /></span>
													<? endif; ?>
													
												</li>
												
												<li>
													<span class="label"><label for="country">Country</label></span>
													<span class="input"><input name="shipping_country" value="<?=$school->shipping_country;?>" type="text" /></span>
												</li>
												<li>
													<input type="submit" value="Continue" name="submit" id="submit" class="button"> 
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
