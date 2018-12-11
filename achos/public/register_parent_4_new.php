<?php
include("check_admin_id_2.php");

$next_page = "false";

include("db.php");
include("camps/includes/classes/user.php");
include("camps/includes/classes/school_class.php");

if (isset($_POST["action"])) {
	$action = $_POST["action"];
			
	if ($action == "submit_payment") {
		$next_page = "true";
		//header("Location: register_parent_5.php");
	}
}

$users = array();
$sql1 = "SELECT id FROM admin_auths WHERE admin_id=" . $admin_id . " AND auth='user'";
$query1 = mysql_query($sql1);
while ($row1 = mysql_fetch_assoc($query1)) {
	$sql2 = "SELECT * FROM users WHERE user_id=" . $row1["id"] . " AND user_registered IS NULL";
	$query2 = mysql_query($sql2);
	if (mysql_num_rows($query2) > 0) {
		$row2 = mysql_fetch_assoc($query2);
		$user = new user($row2);
		$user->get_school_class();
		$user->get_school_info();
		array_push($users, $user);
	}
}

$total_fee = 0;
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
		
		<script>
			var next_page = "<?=$next_page;?>";
			var admin_id = "<?=$admin_id;?>";
		
			$(window).load(function(){
				$("#nav").height($("#content").height());
			});
			
			function check_next_page() {
				if (next_page == "true") {
					var parent_registration = document.forms["parent_registration"];
					parent_registration.elements["admin_id"].value = admin_id;
					parent_registration.submit();
				}
			}									
		</script>
		<!--Copyright Ariel Shkedi 2007-2010-->
	</head>

	<body onload="check_next_page();">
		<FORM name="parent_registration" method="post" action="register_parent_5_new.php">
			<input type="hidden" name="admin_id" value="">
		</FORM>
	
		<NOSCRIPT>
			<P STYLE="color: red; font-size: larger;">Notice: You have javascript disabled. Some parts of the site will not function without javascript.</P>
		</NOSCRIPT>

		<script>
		$(document).ready(function(){ 
			// hide submit button
			//$('#continue_button').hide();
			$('#box_cc_auth').hide();
			
			// on click of submit button
			$("#submit_button").click(function(){ 
				if(perform_validation()){
					// perform credit card validation
					perform_credit_card_validation();
					return false;
				}
				else{				
					return false;
				}
			}); 

			$("#point").click(function(){ 
				test_data();
			}); 
			
		}); 
		
		// perform validation
		function perform_credit_card_validation() {
			var user_ids = "";
			
			var ul = document.getElementById("register_children_ul");
			var lis = $(ul).find("li[name=user]");
			for (lino = 0; lino < $(lis).size(); lino++) {
				var li = $(lis).get(lino);
				var user_id = li.id;
				user_ids = user_ids + user_id + ":";
			}
			user_ids = user_ids.substr(0, user_ids.length - 1);
		
			var dataToSend =  			
				"cc_first_name=" +  $('#cc_first_name').val() +
				"&cc_amount=" 	 +  $('#cc_amount').val() +
				"&cc_last_name=" +  $('#cc_last_name').val() +
				"&cc_address="   +  $('#cc_address').val() +
				"&cc_state="  	 +  $('#cc_state').val() +
				"&cc_zip="  	 +  $('#cc_zip').val() +
				"&cc_description="	+  "child registration->" + user_ids + 			
				"&ccnum="  		 +  $('#ccnum').val() +
				"&ccexp="  		 +  $('#ccexp').val() +
				"&cccvv="  		 +  $('#cccvv').val() ;			
					
			$.ajax({
			  url: 'register_authorize_net.php',
			  type: "GET",
			  data: dataToSend,
			  success: function(data) {									 
					var info = data.split("\n");
					var success = info[0];					
					if (success == "1") {
						update_users_registered(data, user_ids);
					}
					else {
						//alert(info[3]);
						alert(info[0]);
					}
			  }
			});					
		} 
			
		function update_users_registered(message, user_ids) {
			/*var user_ids = "";
			
			var ul = document.getElementById("register_children_ul");
			var lis = $(ul).find("li[name=user]");
			for (lino = 0; lino < $(lis).size(); lino++) {
				var li = $(lis).get(lino);
				var user_id = li.id;
				user_ids = user_ids + user_id + ":";
			}
			user_ids = user_ids.substr(0, user_ids.length - 1);*/
			
			var function_name = "update_user_registered";
			var parameters = [user_ids];
			var url = "camps/includes/edit_functions.php?function_name=" + function_name + "&parameters=" + parameters;
			
			$.getJSON(url, function(success) {
				if (success == 0) {
					alert("User registered update not successful.");
				}
				else {
					//alert(user_ids);
					alert(message);
					document.getElementById("message").value = message;
					document.getElementById("user_ids").value = user_ids;															
					document.forms["register_children_form"].submit();					
				}				
			});
			
		}
		
		// perform validation
		function perform_validation()
		{			
			if ($("#cc_first_name").val() == "") {
				document.getElementById("cc_first_name").focus();
				alert("You must enter First Name as it appears on credit card");
				return false;					
				}
			else if ($("#cc_last_name").val() == "") {
				document.getElementById("cc_last_name").focus();
				alert("You must enter Last Name as it appears on credit card");
				return false;					
				}
			else if ($("#cc_address").val() == "") {
				document.getElementById("cc_address").focus();
				alert("You must enter Address.");
				return false;					
				}
			else if ($("#cc_state").val() == "") {
				document.getElementById("cc_state").focus();
				alert("You must enter State/Province code.");
				return false;					
				}
			else if ($("#cc_zip").val() == "") {
				document.getElementById("cc_zip").focus();
				alert("You must enter Zip/Postal Code.");
				return false;					
				}
			else if ($("#ccnum").val() == "") {
				document.getElementById("ccnum").focus();
				alert("You must enter Valid Credit Card Number.");
				return false;					
				}
			else if ($("#ccexp").val() == "") {
				document.getElementById("ccexp").focus();
				alert("You must enter Valid Credit Card Expiry Date.");
				return false;					
				}			
			return true;
		}		
		
		function test_data() {			
			$('#cc_first_name').val("Moshe");			
			$('#cc_amount').val(.01 * Math.ceil((Math.random() *10)));
			$('#cc_last_name').val("Marty Test");
			$('#cc_address').val("12345 Park Avenue");
			$('#cc_state').val("NY");
			$('#cc_zip').val("11213");
			$('#ccnum').val("4520016005057052");
			// $('#ccexp').val("0912");
			// $('#cccvv').val("640");
		}		
		</script>		
		<div id="wrapper">		
			<div id="nav" class="wizard">
				<div class="col_title_bg"></div>
				<div class="col_title">Menu</div>
				<? $curr = 4; ?>			
				<? include("register_parent_menu.php"); ?>				
			</div>			
			<div id="content">
				<div class="col_title_bg"></div>
				<div class="slider_container">
					<div class="slider">		
						<div class="col_title"></div>	
						<div class="col_content">
							<h1>Checkout</h1>	 
							<form action="register_parent_5.php" method="post" accept-charset="UTF-8" name="register_children_form"> 							
								<input type="hidden" name="action" value="submit_payment">															
								<input type="hidden" id="cc_description" value="parent payment">								
								<input type="hidden" id="message" name="message" value="">
								<input type="hidden" id="admin_id" name="admin_id" value="<?=$admin_id?>">
								<input type="hidden" id="user_ids" name="user_ids" value="">
								
								<h2>Checkout</h2> 
								
								<div class="module" id="module-info">								
								
									<div class="module_content">									
									
										<div class="lists form">										
										
											<ul id="register_children_ul" name="register_children_ul">
											
											<? for ($uno = 0; $uno < count($users); $uno++) : ?>
												<? $user = $users[$uno]; ?>
												<? $total_fee = $total_fee + $user->user_registration_fee; ?>
												
												<li id="<?=$user->user_id;?>" name="user">
													<span class="photo"><img src="images/generic_user_small.png" width="32" height="32" /></span>
													<span class="label large"><?=$user->first;?> <?=$user->last;?></span>
													<div class="box">
														<div class="role">Grade <?=$user->school_class->class_grade;?> - <?=$user->school_class->class_teacher;?></div>
														<div class="info"><?=$user->school_name;?></div>
													</div>
													<span class="label price"><?=$user->user_registration_fee;?></span>
												</li>
																								

											<? endfor; ?>											
											
												<!--<li>
												  <span class="label large">Sponsor a child's registration</span>
												  <span class="label price sponsor">$36</span>
												</li>-->
												
												<li class="right">
													<div class="box">
														<h4>Total: $<?=$total_fee;?></h4>
													</div>
												</li>
											
											</ul>
											
										</div>
										
									</div>
									
								</div>
								
								<div id="box_cc_info">
								<h2>Credit Card Details</h2>
								<div class="module" id="module-info">
									<div class="module_content">
										<div class="lists form">
											<ul>
												<li>
													<input type="hidden" id="cc_amount" value="<?=$total_fee;?>"> 
													<span class="label"><label for="cc_first_name">First Name on Credit Card</label></span>
													<span class="input"><input id="cc_first_name"  name="cc_first_name" type="text" /></span>
												</li>
												<li>
													<span class="label"><label for="cc_last_name">Last Name on Credit Card</label></span>
													<span class="input"><input id="cc_last_name"  name="cc_last_name" type="text" /></span>
												</li>
												<li>
													<span class="label"><label for="cc_address">Address</label></span>
													<span class="input"><input id="cc_address"  name="cc_address" type="text" /></span>
												</li>
												<li>
													<span class="label"><label for="cc_state">State/Province Code</label></span>
													<span class="input"><input id="cc_state"  name="cc_state" type="text" /></span>
												</li>
												<li>
													<span class="label"><label for="cc_zip">Zip</label></span>
													<span class="input"><input id="cc_zip"  name="cc_zip" type="text" /></span>
												</li>
												<li>
													<span class="label"><label for="ccnum">Credit Card Number</label></span>
													<span class="input"><input id="ccnum"  name="ccnum" type="text" /></span>
												</li>
												<li>
													<span class="label"><label for="ccexp">Expiry Date<br>(format MMYY)</label></span>
													<span class="input"><input id="ccexp"  name="ccexp" type="text" /></span>
												</li>
												<li>
													<span class="label"><label for="cccvv">CVV<br>on back of card</label></span>
													<span class="input"><input id="cccvv"  name="cccvv" type="text" /></span>
												</li>
												<li>
													<input type="submit" value="Continue" id='submit_button' class="button"> 													
												</li>
											</ul>
											</div>
										</div>
									</div>
									</div>
									</form> 						
								<div id="box_cc_auth">
								<h2>Credit Card Authorization Results</h2>
								<div class="module" id="module-info">
									<div class="module_content">
										<div class="lists form">
											<ul>
												<li>
													<div id='authorize_net_result'></div>
												</li>
												<li>
													<form action="register_parent_5.php" method="post" accept-charset="UTF-8" name="login"> 
													<input type="button" value="Continue" id='continue_button' class="button"> 													
												</li>
											</ul>
											
										</div>
									</div>
								</div>	
								</div>
								<a ref="#" id='point'>&nbsp;</a>
						</div>						
					</div>					
				</div>				
			</div>			
		</div>
	</body>	
</html>
