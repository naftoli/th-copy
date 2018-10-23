<?php 
session_start();
if ( !isset( $_SESSION['school_id'] ) ) 
	header( "Location: registration.php" );
	
$admin_id = $_SESSION['admin_id'];
$school_id = $_SESSION['school_id'];

include("db.php");
$next_page = "false";

include("classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
	
$message = "";	
if (isset($_POST["action"])) {
	
	foreach ($_POST as $k => $v) {
		$_POST[$k] = mysql_real_escape_string(trim($v));
	}

	if ($_POST["action"] == "update") {
		$sql = "UPDATE schools
				SET shipping_first='" . $_POST['shipping_first'] . "',
				shipping_last='" . $_POST['shipping_last'] . "',
				shipping_address1='" . $_POST['shipping_address1'] . "',
				shipping_phone='" . $_POST['shipping_phone'] . "',
				shipping_address2='" . $_POST['shipping_address2'] . "',
				shipping_city='" . $_POST['shipping_city'] . "',
				shipping_state='" . $_POST['shipping_state'] . "',
				shipping_postal='" . $_POST['shipping_postal'] . "',
				shipping_country='" . $_POST['shipping_country'] . "',
				shipping_requests = '" . $_POST['requests'] . "', 
				shipping_method = '" . $_POST['shipping_method'] . "' 
				WHERE school_id=" . $school_id;
		$query = mysql_query($sql);
		
		if (!$query) {
			$message = "<span style='color:red;'>Update failed, Please try again.<span>";	
		}
		else {
			$next_page = "true";
		}
	}
	
}

include("classes/school.php");
$sql = "SELECT * FROM schools WHERE school_id=" . $school_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$school = new \classes\school($row);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" dir="<?=$dir?>">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<title>School Registration</title>
		<link rel="alternate" media="print" href="index.php">
		<link href="admin_styles.css" rel="stylesheet" type="text/css" />
		
		<style>
		.form h4{
			text-align:left;			
		}
		.box{
			padding-left:40px;
		}
		select#shipping_method {
			border: 1px solid #BBBBBB;
			-moz-border-radius: 5px 5px 5px 5px;
			-webkit-border-radius: 5px;
			width: 350px;
			/* min-width: 350px; */
			font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
			font-size: 16px;
			font-weight: bold;
			/* border: none; */
			background: #fff;
			min-height: 26px;
			margin: 1px 0;
			padding: 5px;
			margin-right: 5px;
		}
		</style>
		
		<script src="camps/scripts/jquery.tools.min.js"></script>
		<script src="scripts/jquery.placeholder.js"></script>
		
		<script>
			var next_page = "<?=$next_page;?>";
			var admin_id = <?=$admin_id;?>;
			var school_id = <?=$school_id;?>;
		
			$(function() {
				$("#nav").height($("#content").height());
				$('input').placeholder();				
				
				// toggle 1
				$('.toggle').hide();								
				$('input[type="radio"][value="deliver"]:checked').parents('ul').find('.toggle2').show();
				$('input[type="radio"]').change(function(){
						$(this).parents('ul').find('.toggle2').slideUp('slow');
						$(this).filter('[value="deliver"]:checked').parents('ul').find('.toggle2').slideDown('fast');
						if ( $(this).filter('[value="deliver"]:checked').val() ) {
						    $(".shipping").show();
						} else {
						    $(".shipping").hide();
						}
				});
				
				// toggle 2
				$('.toggle2').hide();				
				$('input[type="radio"][value="pickup"]:checked').parents('ul').find('.toggle').show();
				$('input[type="radio"]').change(function(){
						$(this).parents('ul').find('.toggle').slideUp('fast');
						$(this).filter('[value="pickup"]:checked').parents('ul').find('.toggle').slideDown('slow');
				});
				
			});

			function check_radio_buttons() {
				return checkForm();
			}
			
			function check_next_page() {
				if (next_page == "true") {
					location.href = "/registration_7.php";
				}
			}

			function checkForm() {
				var a = document.login.shipping_first.value;
				var b = document.login.shipping_last.value;
				var c = document.login.shipping_phone.value;
				var d = document.login.shipping_address1.value;
				//var e = document.login.shipping_address2.value;
				var f = document.login.shipping_city.value;
				var g = document.login.shipping_state.value;
				var h = document.login.shipping_postal.value;
				var i = document.login.shipping_country.value;
				
				//alert( a + b + c + d + e + f + g + h + i );
				if (a == "" || b == "" || c == "" || d == "" || f == "" || g == "" || h == "" || i == "") {
					alert("You must fill in ALL shipping info.");
					return false;
				} else
					return true;
			}
		</script>
	</head>

	<body onload="check_next_page();">
	
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
							<h1>School Registration</h1>
	 
							<h1><?=$message;?></h1>
							
							<form action="registration_6.php" method="post" accept-charset="UTF-8" name="login" onsubmit="return check_radio_buttons();"> 
								<input type="hidden" name="action" value="update">
								<input type="hidden" name="school_id" value="<?=$school_id;?>">
								<input type="hidden" name="admin_id" value="<?=$admin_id;?>">
							    <!--
								<div class="module" id="module-info">
									<div class="module_content">
										<div class="lists form">
											<ul>
												<li>
													<div class="box">
														<h4><input name="shipping_method" id="deliver" type="radio" value="deliver" />Deliver any material to the address below.</h4>
													</div>
												</li>
												<li class="toggle2">
													<div class="box">
														<p>PLEASE NOTE: Standard shipping rates apply on all items shipped.</p>
													</div>    
												</li>

												<li>
													<div class="box">
														<h4><input name="shipping_method" id="pickup" type="radio" value="pickup"  />I will pickup any material at TH Headquarters, 792 Eastern Parkway, Brooklyn, NY 11213.</h4>
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
								-->
								<div class="shipping">
    								<h2>Shipping Details</h2>
    								<p><i>All fields are mandatory</i></p>
    							</div>
    								
								<div class="module" id="module-info">
									<div class="module_content">
										<div class="lists form">
											<ul>
											    <div class="shipping">
    												<li>
    													<span class="label"><label for="first">First Name</label></span>
    													<span class="input"><input name="shipping_first" type="text" 
    													    value="<?=empty($school->shipping_first)?$admin->first:$school->shipping_first;?>" /></span>
    												</li>
    												
    												<li>
    													<span class="label"><label for="last">Last Name</label></span>
    													<span class="input"><input name="shipping_last" type="text" 
    													    value="<?=empty($school->shipping_last)?$admin->last:$school->shipping_last;?>" /></span>
    												</li>
    												
    												<li>
    													<span class="label"><label for="phone">Phone</label></span>
    													<span class="input"><input name="shipping_phone" 
    													    value="<?=empty($school->shipping_phone)?$admin->admin_phone_work:$school->shipping_phone;?>" type="text" /></span>
    												</li>
    												
    												<li>
    													<span class="label"><label for="address">Address</label></span>
    													<span class="input"><input name="shipping_address1" 
    													    value="<?=empty($school->shipping_address1)?$school->school_address1:$school->shipping_address1;?>" type="text" /></span>
    													<div class="clear"></div>
    													<span class="label"></span>
    													<span class="input"><input name="shipping_address2" 
    													    value="<?=empty($school->shipping_address2)?$school->school_address2:$school->shipping_address2;?>" type="text" /></span>
    													<div class="clear"></div>
    													<span class="label"></span>
    													<span class="input city"><input name="shipping_city" 
    													    value="<?=empty($school->shipping_city)?$school->school_city:$school->shipping_city;?>" type="text" /></span>
    													<span class="input state"><input name="shipping_state" 
    													    value="<?=empty($school->shipping_state)?$school->school_state:$school->shipping_state;?>" type="text" /></span>
    													<span class="input zip"><input name="shipping_postal" 
    													    value="<?=empty($school->shipping_postal)?$school->school_postal:$school->shipping_postal;?>" type="text" /></span>    													
    												</li>
    												
    												<li>
    													<span class="label"><label for="country">Country</label></span>
    													<span class="input"><input name="shipping_country" 
    													    value="<?=empty($school->shipping_country)?$school->school_country:$school->shipping_country;?>" type="text" /></span>
													</li>
													<li>
    													<span class="label"><label for="shipping_method">Shipping Method</label></span>
    													<span class="input">
															<select name="shipping_method" id='shipping_method'>
																<option value='pickup'>Pickup</option>
																<option value='deliver' <?= $school->shipping_method =='deliver' ? 'selected' : ''?>>
																	Delivery
																</option>
															</select>
														</span>
    												</li>
													<li>
														<span class="label"><label for="requests">Shipment Requests</label></span>
														<span class="input">
															<textarea name="requests" rows="5" cols="50" placeholder='Any notes or requests, such as “Please call us before sending” etc.'></textarea>
														</span>
													</li>
    								            </div>
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
