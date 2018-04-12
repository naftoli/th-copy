<?php 
session_start();
ini_set('display_errors',1);

if ( !isset( $_SESSION['hschool'] ) ) 
    header( "Location: admin.php" );
$h_school = $_SESSION['hschool'];

include("db.php");
include("check_admin_id.php");

// ***** GET THE ADMIN AND SCHOOL INFO ***** //
include("classes/admin.php");
include("classes/user.php");
include("camps/includes/classes/school_class.php");

// get admin info
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);

$admin = new admin($row);
$admin->get_school_id();
$school_id = $admin->school_id;
/*
// get school info
$sql2 = "SELECT * FROM schools WHERE school_id=" . $school_id;
$query2 = mysql_query($sql2);
$row2 = mysql_fetch_assoc($query2);
$inst_id = $row2['inst_id'];

if ( $inst_id != 4 ) {
    $reg_fee = 600;
} else {
    $reg_fee = 40;
}
 * 
 */

$reg_fee = 770;
$total = $reg_fee;
 
$schools = array(
	84  => 410.31,
	185 => 1052.9,
	37  => 300,
	194 => 183.54,
	80  => 770,
	264 => 770,
	328 => 85.59
);
 
if (in_array($school_id, array_keys($schools))) {
	$total += $schools[$school_id];
}

// ***** GET THE ADMIN AND SCHOOL INFO ***** //
/*
//get scanners ordered by school
$scanners = 0;
$sql = "select scanners from school_accessories where school_id = " . $school_id . " and year = 5777";
$result = mysql_query( $sql );
$row = mysql_fetch_assoc( $result );
$scanners = $row['scanners'];

$total = $reg_fee + ($scanners * 50);

// ***** GET THE KIOSKS ORDERED BY THE SCHOOL ***** //
include("classes/school_kiosk.php");
include("classes/kiosk_type.php");
$school_kiosks = array();
$sql = "SELECT * FROM school_kiosks WHERE school_id=" . $admin->school_id;
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$school_kiosk = new school_kiosk($row);
	$school_kiosk->get_kiosk_type();
	array_push($school_kiosks, $school_kiosk);
}
// ***** GET THE KIOSKS ORDERED BY THE SCHOOL ***** //
*/
// ***** SEND THE CONFIRMATION EMAIL ***** //
$confirmation = "";
$message = "";

$sql = "SELECT * FROM schools WHERE school_id = " . $school_id;
$result2 = mysql_query($sql);
$row2 = mysql_fetch_assoc($result2);
echo "<pre>"; print_r($row2); echo "</pre>";

// make sure there's an authorize profile created
if (empty($row2['authorize_customer_profile_id']) || empty($row2['authorize_payment_profile_id'])) {
    header("Location: registration_7.php");
    exit;
}

require_once 'class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();
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
				var schools = <?=json_encode($schools)?>;
				
				for (var s in schools) {
					if (s == school_id) {
						alert("Your base has a previous outstanding balance of $" + schools[s] + ". This amount will be added to your registration fee. If you have any questions please contact accounting@tzivoshashem.org.");
					}
				}
				// hide return button
				$("#return_to_main_menu_button").hide();
				
				// on click of submit button
				$("#submit_button").click(function(){ 				
					// perform some validation
					if(check_checkboxes())				{
						submit_transaction_to_creditcard_processing();
					}
				}); 			
				
				// on click of return button
				$("#return_to_main_menu_button").click(function(){ 
					<? if ( $h_school ) { ?>	
						var str = "admin_setup_guide_hschool.php?school_id=" + school_id;
					<? } else { ?>
						var str = "admin_setup_guide.php?school_id=" + school_id;
					<? } ?>
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
			
			// display message
			// function display_message() {
				// if (confirmation != "") {
					// alert(confirmation);
						// window.location = "http://mashpia.com/admin.php";
				// }
			// }
				
			function submit_transaction_to_creditcard_processing() {
				// cast the json into a post params list
				var dataToSend = $.param({
					school_id: <?=$row2['school_id'];?>,
					description: "school registration for: " + '<?=$row2['school_name'];?>',
					amount: $('#trans_total').val(),
					customer_profile_id: <?=$row2['authorize_customer_profile_id'];?>,
					payment_profile_id: <?=$row2['authorize_payment_profile_id'];?>
				});
				
				$.post(
					"/ajax/authorize/charge_card.php",
					dataToSend,
					function(data) { // on a sucessfull hit this function is called
						data = JSON.parse(data); // parse the result to json
						// add || sid == 82 to allow the test account access
						if(data.success) { // if the charge was sucessfull{
							console.log(data.response); // log out the transaction response
							// generate the response and show it in the correct box
							var response  = data.response.transactionResponse.messages[0]; // load the correct section of the response
							var message = response.description + " (" + response.code + ")"; // format the text
							$('#cc_response').html(message + "<br/>") ; // show the user the response
							$("#return_to_main_menu_button").show();
							$("#submit_button").hide();

							var url = "camps/includes/edit_functions.php?function_name=set_school_era&parameters=" + school_id;								
							//alert(url);
							$.get(url, function(success) {
								//alert(success);
							});
							
							//update school to be enrolled in all appropriate campaigns
							$.post('ajax/enrollIntoCampaigns.php', {type : 'school', id : school_id});
							
							// send email to office
							$.post('ajax/sendEmail.php', { school : school_id });
							
						} else { // alert the user that the charge has failed.
							$('#cc_response').html("Credit Card Error: " + data.response + "<br/>Please Try Again.") ; // show the user the error					
						}
					}
				);
				return false;
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
								<input type="hidden" name="school_id" value="<?=$school_id;?>">
								<input type="hidden" name="admin_id" value="<?=$admin_id;?>">								

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
														<h4>Base Membership for <?if ( $h_school ) echo "Hebrew School "; ?><?=$year?> - $<?=$reg_fee?></h4>
													</div>
												</li>
												
												<?php if (in_array($school_id, array_keys($schools))) : ?>
												<li>
													<div class="box">
														<h4>Total outstanding balance - $<?=$schools[$school_id]?></h4>
													</div>
												</li>
												<? endif; ?>
												
												<? if ( $scanners ) { ?>
												<li>
                                                    <div class="box">
                                                        <h4><?=$scanners?> Scanners ($50 / ea.) - $<?=$scanners*50?></h4>
                                                    </div>
                                                </li>    
												<? } ?>
													
												<!-- ***** KIOSKS ORDERED BY THE SCHOOL ***** -->
												<? //for ($skno = 0; $skno < count($school_kiosks); $skno++) :?>
													<!--<li>
														<div class="box">
															<? //$total = $total + ($school_kiosks[$skno]->quantity * $school_kiosks[$skno]->price); ?>															
															<h4>
																<?//=$school_kiosks[$skno]->quantity;?> <?//=$school_kiosks[$skno]->kiosk_type->kiosk_name;?> (<?//=number_format($school_kiosks[$skno]->price, 2, '.', '');?>) Kiosk(s) - $<?//=number_format($school_kiosks[$skno]->quantity * $school_kiosks[$skno]->price, 2, '.', '');?>
															</h4>
														</div>
													</li>-->
												<? //endfor; ?>
												<!-- ***** KIOSKS ORDERED BY THE SCHOOL ***** -->
												
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
														Please charge my card $<?=$total?>.00
														<? if ( $h_school ) echo "(Hebrew School " . $year . " Total Registration)"; ?></h4>
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
