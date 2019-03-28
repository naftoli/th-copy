<?php 
session_start();
if ( !isset( $_SESSION['school_id'] ) ) 
	header( "Location: registration.php" );
	
$admin_id = $_SESSION['admin_id'];
$school_id = $_SESSION['school_id'];

include("db.php");
require_once( __DIR__ . '/api/header/db.php' ); // import ActiveRecord and PDO

require 'class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();
// get the registration info for the school
$school = School::find([ $school_id ]);
$total = $school->chayolei_fee + $school->balance;

// ***** SEND THE CONFIRMATION EMAIL ***** //
$confirmation = "";
$message = "";

$sql = "SELECT * FROM schools WHERE school_id = " . $school_id;
$result2 = mysql_query($sql);
$row2 = mysql_fetch_assoc($result2);

// make sure there's an authorize profile created
if (!isset( $_SESSION['skipCC'] ) || $_SESSION['skipCC'] !== 'yes') {
	if (empty($school->authorize_customer_profile_id) || empty($school->authorize_payment_profile_id)) {
		header("Location: registration_7.php");
		exit;
	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<title>School Registration</title>
		<link rel="alternate" media="print" href="index.php">
		<link href="admin_styles.css" rel="stylesheet" type="text/css" />
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
		<!--<script type="text/javascript" src="http://jzaefferer.github.com/jquery-validation/jquery.validate.js"></script>-->
		<script src="camps/scripts/jquery.tools.min.js"></script>			
		<script>
			var school_id = <?=$school_id;?>;

			// jquery
			$(document).ready( function() {
				// pop up for outstanding balance
                <? if ( $school->balance > 0 ) { ?>
                    alert("Your base has a previous outstanding balance of $<?=$school->balance?>. This amount will be added to your registration fee. If you have any questions please contact accounting@tzivoshashem.org.");
                <? } ?>

				// hide return button
				$("#return_to_main_menu_button").hide();
				
				// on click of submit button
				$("#submit_button").click(function(){ 				
					// perform some validation
					if(check_checkboxes()) {
						submit_transaction_to_creditcard_processing();
					}
				}); 			
				
				// on click of return button
				$("#return_to_main_menu_button").click(function(){ 
					var str = "admin_setup_guide.php?school_id=" + school_id;
					window.location = str;
				});

				// randomize amount for testing
				$("#point").click(function(){ 
					$('#trans_total').val(.01 * Math.ceil((Math.random() *10))) ;
					alert($('#trans_total').val());
				}); 
				
			});   // end of document.ready
			
			var confirmation = "<?=$confirmation;?>";
			
			$(function() {
				$("#nav").height($("#content").height());
			});
			
			// check them checkboxes
			function check_checkboxes() {
				if (document.getElementById("ccaccept").checked == false) {
					document.getElementById("ccaccept").focus();
					alert("You must agree to accept the charges.");
					return false;
				}
				else if (document.getElementById("ccaccept2").checked == false) {
					document.getElementById("ccaccept2").focus();
					alert("You must agree to accept the charges for any future purchases and student registrations.");
					return false;
				}
				else {
					return true;
				}				
			}
				
			function submit_transaction_to_creditcard_processing() {
				// check if we are suppose to skip cc processing
				<?php if (isset( $_SESSION['skipCC'] ) && $_SESSION['skipCC'] == 'yes') : ?>
					var url = "camps/includes/edit_functions.php?function_name=set_school_era&parameters=" + school_id;								
					$.get(url, function(success) {
						//alert(success);
						$("#return_to_main_menu_button").show();
						$("#submit_button").hide();
					}); 
					return false;
				<?php else : ?>

					// cast the json into a post params list
					var dataToSend = $.param({
						school_id: <?=$school->school_id;?>,
						description: "school registration for: " + '<?=$school->school_name;?>',
						amount: $('#trans_total').val(),
						customer_profile_id: <?=$school->authorize_customer_profile_id;?>,
						payment_profile_id: <?=$school->authorize_payment_profile_id;?>
                    });
                    
					$.post(
						'/api/registration/school_registration.php?action=register',
						dataToSend,
						function(response) { // on a sucessfull hit this function is called
							// add || sid == 82 to allow the test account access
							if(response.success) { // if the charge was sucessfull
                                var message = 'Registration Compleate';
								// generate the response and show it in the correct box
                                if ( response.data.payment ) {
                                    var response  = response.data.payment.transactionResponse.messages[0]; // load the correct section of the response
								    var message = response.description + " (" + response.code + ")"; // format the text
                                }
								$('#cc_response').html(message + "<br/>") ; // show the user the response
								$("#return_to_main_menu_button").show();
								$("#submit_button").hide();
								
								//update school to be enrolled in all appropriate campaigns
								$.post('ajax/enrollIntoCampaigns.php', {type : 'school', id : school_id});
								
							} else { // alert the user that the charge has failed.
								$('#cc_response').html("Credit Card Error: " + response.error + "<br/>Please Go back and update your card information.") ; // show the user the error					
							}
						}
					);
					return false;
				<?php endif; ?>
			} // end of: submit_transaction_to_creditcard_processing
		</script>
	</head>

	<body>
		<NOSCRIPT>
			<P STYLE="color: red; font-size: larger;">
				Notice: You have javascript disabled. Some parts of the site will not function without javascript.
			</P>
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
							<form action="#" id="submit_form" method="post" accept-charset="UTF-8" name="login"> 
								<input type="hidden" name="action" value="submit">
								<input type="hidden" name="action" value="registration_confirmation">								

								<? if ($message != "") : ?>
									<h1><?=$message;?></h1>
								<? endif; ?>
								
								<h2>Summary</h2>
																
								<div class="module" id="module-info">								
									<div class="module_content">	
										<div class="lists form">
											<ul>
												<li>
													<div class="box">
														<h4>Base Membership for <?=$year?> - $<?=$schoolInfo->fee?></h4>
													</div>
												</li>
												
												<?php if ($schoolInfo->balance > 0 ){ ?>
												<li>
													<div class="box">
														<h4>Total outstanding balance - $<?=$schoolInfo->balance?></h4>
													</div>
												</li>
                                                <? } ?>
												
												<li class="right">
													<div class="box">
														<!--<h4>Total $<?//=number_format($total + 600, 2, '.', '');?></h4>-->
														<h4>Total $<?=number_format($total, 2)?></h4>
													</div>
												</li>												
											</ul>											
										</div>																			
									</div>									
								</div>
								<div class="module" id="module-info">
									<div class="module_content">
										<div class="lists form">
											<ul>
												<li>
													<div class="box">
														<h4><input type="checkbox" name="ccaccept" id="ccaccept" />
														Please charge my card $<?=$total?></h4>
													</div>
												</li>
												<li>
													<div class="box">
														<h4><input type="checkbox" name="ccaccept2" id="ccaccept2" />
														Please charge my card for any future purchases and student registration.</h4>
													</div>
												</li>
												<li>
													<input type="button" id='submit_button' value="Submit" class="button"> 
												</li>
											</ul>
										</div>
									</div>
								</div>
								<div class="module" id="module-info">
									<div class="module_content">
										<div class="lists form">
											<ul>
												<li>          
													<div id='cc_response'>&nbsp;</div>
													<input type="button" id='return_to_main_menu_button' value="Proceed to Setup Guide" class="button"> 
												</li>
											</ul>
										</div>										
									</div>									
								</div>								
								<!--<input type="hidden" id='trans_total' value="<?//=$total;?>">-->
								<input type="hidden" id='trans_total' value="<?=$total?>">
							</form> 							
						<a ref="#" id='point'>&nbsp;</a>
						</div>						
					</div>					
				</div>				
			</div>			
		</div>
	</body>	
</html>