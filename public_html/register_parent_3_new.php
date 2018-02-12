<?php
include("db.php");
include("check_admin_id_2.php");

$next_page = "false";

// ***** GET THE ADMIN AND SCHOOL INFO ***** //
include("camps/includes/classes/admin.php");
include("camps/includes/classes/user.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
$admin->get_unregistered_children();
// ***** GET THE ADMIN AND SCHOOL INFO ***** //

$message = "";
$child_registered = "false";
if (isset($_POST["action"])) {
	$action = $_POST["action"];

	if ($action == "enroll_child") {	

		// ***** Children that are being enrolled ***** //
		$enroll_data = $_POST["enroll_data"];
		$users = explode(":", $enroll_data);
		
		for ($uno = 0; $uno < count($users); $uno++) {
			$user_info = $users[$uno];		
			$enrollments = explode(",", $user_info);		
// var_dump($user_info);
// die();

			if (count($enrollments) > 1) {
				$user_id = $enrollments[0];
				$fee = 36;
				$add_on_one = 0;
				$add_on_two = 0;
				
				 
				for ($ano = 1; $ano < count($enrollments); $ano++) {
 var_dump($enrollments);
 die();
					$info = explode("_", $enrollments[$ano]);

					$add_on_option = $info[2];
					
					if ($add_on_option == 1){
						$add_on_one = 1;
						$fee = $fee + 14;
						$shirt_size = $enrollments[2];						
					}
					
					if ($add_on_option == 2) {
						$add_on_two = 1;
						$fee = $fee + 24;
						$shirt_size = "";
					}
				}
				//$sql = "UPDATE users SET user_registered=NOW(), add_on_one=" . $add_on_one . ", add_on_two=" . $add_on_two . ", user_registration_fee=" . $fee . " WHERE user_id=" . $user_id;
				//$sql = "UPDATE users SET add_on_one=" . $add_on_one . ", add_on_two=" . $add_on_two . ", user_registration_fee=" . $fee . " WHERE user_id=" . $user_id;

				$sql = "UPDATE users SET 	add_on_one=" . $add_on_one . ", 
											add_on_two=" . $add_on_two . ", 
											user_registration_fee=" . $fee . ",
											shirt_size= '" . $shirt_size . "'" . "
											WHERE user_id=" . $user_id;
															
				$query = mysql_query($sql);
				if ($query) {
					$next_page = "true";
					//header("Location: register_parent_4.php");
				}
				else  {
					$message = "<span style='color:red;'>Enrollment failed. Please try again.<span>";
				}

			}

		}
		// ***** Children that are being enrolled ***** //
		
		// ***** Children that are being sponsored ***** //
		$send_mail = false;
		if (isset($_POST["sponsor_reg"])) {
			$sql = "INSERT INTO admin_sponsors SET admin_id=" . $admin_id . ", name='" . $_POST["sponsor_person"] . "', is_regular=1";
			$query = mysql_query($sql);
			$send_mail = true;
		}	
		if (isset($_POST["sponsor_plus"])) {
			$sql = "INSERT INTO admin_sponsors SET admin_id=" . $admin_id . ", name='" . $_POST["sponsor_person"] . "', is_regular=0";
			$query = mysql_query($sql);
			$send_mail = true;
		}	
		
		if ($send_mail == true) {
			$to = $_POST["email"];
			$subject = "Program Director Invitation";
			$message = "You have been invited to be Program Director.";		
			$headers = "From: director@mashpia.com\r\nReply-To: chaniee21@gmail.com";
			$mail_sent = @mail($to, $subject, $message, $headers);
			if ($mail_sent) 
				$message = "Mail sent";
			else
				$message = "Mail failed";			
		}
		// ***** Children that are being sponsored ***** //		
	}
}

// ***** SCHOOL ADD ONS ***** //
include("camps/includes/classes/school_add_on.php");
$school_add_ons = array();
$sql = "SELECT * FROM school_add_ons WHERE school_add_on_id=1";
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$school_add_on = new school_add_on($row);
array_push($school_add_ons, $school_add_on);

$sql = "SELECT * FROM school_add_ons WHERE school_add_on_id=2";
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$school_add_on = new school_add_on($row);
array_push($school_add_ons, $school_add_on);
// ***** SCHOOL ADD ONS ***** //
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" dir="<?=$dir?>">

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<title>Registration Wizard - Tzivos Hashem Management System</title>
		<link rel="alternate" media="print" href="index.php">
		<link href="admin_styles.css" rel="stylesheet" type="text/css" />
		<script src="camps/scripts/jquery.tools.min.js"></script>
		
		<script>
			var next_page = "<?=$next_page;?>";
			var admin_id = "<?=$admin_id;?>";
		
			var child_registered = "<?=$child_registered;?>";
			var no_of_children_enrolled = 0;
			
			var users = new Array();
			<?
				for ($uno = 0; $uno < count($admin->children); $uno++) {
					$user = $admin->children[$uno];
					echo "users[" . $uno . "]=" . $user->user_id . ";\n\t\t\t";
				}
			?>
			
	/*		$(function() {
				$('.checkbox_enroll').not(':checked').parents('ul').find('.add_on').hide();
				
				$('.checkbox_enroll').click(function(){
					$(this).parents('ul').find('.add_on').slideToggle();
				});
				
				$("#nav").height($("#content").height());
			});
	*/		
			function get_submit_values(frm) {
				if (no_of_children_enrolled == 0) {
					alert("You must enroll at least one child in order to continue.");
					return false;				
				}
				else {
				
					if (document.getElementById("support").checked == false) {
						document.getElementById("support").focus();
						alert("Please agree to support your child/ren in completing their missions.");
						return false;
					}
					else if ((document.getElementById("sponsor_reg").checked == true || document.getElementById("sponsor_plus").checked == true) && document.getElementById("sponsor_person").value == "") {
						document.getElementById("sponsor_person").focus();
						alert("You must enter the name of the person that you want to sponsor.");
						return false;
					}
					else {
						var enroll_data = "";
						
						
						
						for (uno = 0; uno < users.length; uno++) {
							var user_id = users[uno];
							enroll_data = enroll_data + user_id;
						
							var x = "#shirt_size_" + user_id ;														
							var shirt_size = $(x + " option:selected").val();
						
							var user_div = $(frm).find("div[name=" + user_id + "]");
							var inputs = $(user_div).find("input");					
							for (ano = 0; ano < inputs.length; ano++) {								
								var add_on = $(inputs).get(ano);								
								if (add_on.checked == true) {
									//enroll_data = enroll_data + "," + $(add_on).attr("id");									
									enroll_data = enroll_data + "," + $(add_on).attr("id") + "," + shirt_size;									
								}
								
							}
							
							enroll_data = enroll_data + ":";
						}	
						

						enroll_data = enroll_data.substr(0, (enroll_data.length - 1));				
						document.getElementById("enroll_data").value = enroll_data;	
						document.getElementById("enroll").value = "enroll";	
						return true;
					}			
				
					
				}
				
				
				
				
				
				
			}
			
			function check_child_registered() {
				if (child_registered == "true") {
					window.location = "register_parent_4.php";
				}
			}
			
			function enroll_child(chcxb) {
				if (chcxb.checked == true)
					no_of_children_enrolled++;
				else
					no_of_children_enrolled--;
			}
			
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

	<body onload="check_next_page(); check_child_registered();">
		<FORM name="parent_registration" method="post" action="https://mashpia.com/register_parent_4_new.php">
			<input type="hidden" name="admin_id" value="">
		</FORM>
	
		<NOSCRIPT>
			<P STYLE="color: red; font-size: larger;">Notice: You have javascript disabled. Some parts of the site will not function without javascript.</P>
		</NOSCRIPT>
		
		<div id="wrapper">
		
			<div id="nav" class="wizard">
				<div class="col_title_bg"></div>
				<div class="col_title">Menu</div>
				<? $curr = 3; ?>
				<? include("register_parent_menu.php"); ?>
			</div>
			
			<div id="content">
			
				<div class="col_title_bg"></div>
				
				<div class="slider_container">
				
					<div class="slider">
					
						<div class="col_title"></div>
						
						<div class="col_content">
							<h1>Child Registration</h1>
	 
							<form action="register_parent_3_new.php" method="post" accept-charset="UTF-8" name="register_parent_3" onsubmit="return get_submit_values(this);"> 								
								<input type="hidden" name="action" id="action" value="enroll_child">							
								<input type="hidden" name="admin_id" id="admin_id" value="<?=$admin_id;?>">
								<input type="hidden" name="enroll_data" id="enroll_data" value="">
								
								<?=$message;?>
								
								<!-- ***** ADMIN CHILDREN ***** -->
								<? for ($uno = 0; $uno < count($admin->children); $uno++) : ?>
									<? $user = $admin->children[$uno]; ?>
										<h2>Enroll</h2> 
										
										<div class="module" id="module-info">
										
											<div class="module_content">
											
												<!-- ***** CHILD ***** -->
												<div class="lists form" id="<?=$user->user_id;?>" name="<?=$user->user_id;?>">
													<ul>
														<li>
															  <span class="photo"><img width="32" height="32" alt="" src="<?=($user->user_photo_id=='')?'images/generic_user_small.png':'/file_view.php?id='.$user->user_photo_id?>"></span>
															  <span class="label large"><?=$user->first;?> <?=$user->last;?></span>
															  <div class="box">
																<div class="role">Grade <?=$user->school_class->class_grade;?> - <?=$user->school_class->class_teacher;?></div>
																<div class="info"><?=$user->school_name;?></div>
															  </div>
															  <? if ($user->user_registered != "") : ?>
															  <span class="label price">Registered</span>
															  <? endif; ?>

														</li>
														
														<? if ($user->user_registered == "") : ?>							
														<li>
														<h4>Membership Includes</h4>
														  <ul class="register_package">
															<li>
																<img src="images/registration/account.png" width="48" height="48" />
																Tzivos Hashem Account
															</li>
															<li>
																<img src="images/registration/missions.png" width="48" height="48" />
																Missions
															</li>
															<li>
																<img src="images/registration/card.png" width="48" height="48" />
																ID Cards
															</li>
															<li>
																<img src="images/registration/rank_book.png" width="48" height="48" />
																Rank Books
															</li>
															<li>
																<img src="images/registration/certificate.png" width="48" height="48" />
																Rank Certificates
															</li>
															<li>
																<img src="images/registration/medals.png" width="48" height="48" />
																Medals
															</li>
															<li>
																<img src="images/registration/hachayol.png" width="48" height="48" />
																Subscription to TH Magazines
															</li>
															<li>
																<img src="images/registration/tehillim.png" width="48" height="48" />
																The Fellig Tehillim
															</li>
															<li>
																<img src="images/registration/binder.png" width="48" height="48" />
																Leather-like Zipper Binder
															</li>
															<li>

																<img src="images/registration/sticker_chart.png" width="48" height="48" />
																Mission Sticker Boards
															</li>
															<li>
																<img src="images/registration/sticker.png" width="48" height="48" />
																Stickers Book
															</li>
															<li>
																<img src="images/registration/achievement.png" width="48" height="48" />
																Barcode Achievement Cards
															</li>
															<li>
																<img src="images/registration/auction.png" width="48" height="48" />
																Tickets for 3 Chinese auctions
															</li>
														  </ul>
													  </li>
													  
													  <!-- ***** ADD ON ONE ***** -->
													  <? if ($user->add_on_one > 0) : ?>
													  <? $school_add_on = $school_add_ons[0]; ?>
													  <li>
														<h4><?=$school_add_on->title;?></h4>
														  <ul class="register_package">
															<li>
																<img src="images/registration/sweatshirt.png" width="48" height="48" />
																<?=$school_add_on->line_1;?>
															</li>
														  </ul>
													  </li>
													  <? endif; ?>
													  <!-- ***** ADD ON ONE ***** -->
													  
													  
													  <!-- ***** ADD ON TWO ***** -->
													  <? if ($user->add_on_two > 0 && $user->school_class->class_grade > 2) : ?>
													  <? $school_add_on = $school_add_ons[1]; ?>
													  <li>
														<h4><?=$school_add_on->title;?></h4>
														  <ul class="register_package">
															<li>
																<img src="images/registration/770.png" width="48" height="48" />
																<?=$school_add_on->line_1;?>
															</li>
															<li>
																<img src="images/registration/770_sticker.png" width="48" height="48" />
																<?=$school_add_on->line_2;?>
															</li>
														  </ul>
													  </li>
													  <? endif; ?>
													  <!-- ***** ADD ON TWO ***** -->
													  
													  <?
													  //find out if add_ons are mandatory
													  $user->get_school();
													  $one = $user->school->add_on_one;
													  $two = $user->school->add_on_two;
													  $fee = null;													
													  if ($one == 2 and $two != 2) $fee = 50;
													  else if ($one != 2 and $two == 2 and $user->school_class->class_grade > 2) $fee = 60;
													  else if ($one == 2 and $two == 2 and $user->school_class->class_grade > 2) $fee = 74;
													  else $fee = 36;
													  
													  switch ($fee) {
														case 50:
														?>
														<li>
															<span class="box">
																<p class="input">
																	<input type="checkbox" name="enroll" id="enroll" value="yes" class="checkbox_enroll" onclick="enroll_child(this);">
																	<input type="hidden" name="addon1" id="add_on_1" value="yes">
																	<input type="hidden" name="fee" id="fee" value="<?=$fee?>">
																	<label for="enroll-12345">I would like to enroll this child in the Tzivos Hashem 5771 program. ($50)</label>
																	<br>Sweatshirt Size
																	<select name='shirt_size' id='shirt_size_<?=$user->user_id;?>'>
																	<option>S</option>
																	<option>M</option>
																	<option>L</option>
																	<option>XL</option>
																	</select>
																</p>
															</span>
														</li>
														<?
															break;
														case 60:
														?>
														<li>
															<span class="box">
																<p class="input">
																	<input type="checkbox" name="enroll" id="enroll" value="yes" class="checkbox_enroll" onclick="enroll_child(this);">
																	<label for="enroll-12345">I would like to enroll this child in the Tzivos Hashem 5771 program. ($60)</label>
																	<input type="hidden" name="addon2" id="add_on_2" value="yes">
																	<input type="hidden" name="fee" id="fee" value="<?=$fee?>">
																</p>
															</span>
														</li>
														<?
															break;
														case 74:
														?>
														<li>
															<span class="box">
																<p class="input">
																	<input type="checkbox" name="enroll" id="enroll" value="yes" class="checkbox_enroll" onclick="enroll_child(this);">
																	<label for="enroll-12345">I would like to enroll this child in the Tzivos Hashem 5771 program. ($74)</label>
																	<input type="hidden" name="addon1" id="add_on_1" value="yes">
																	<br>Sweatshirt Size
																	<select name='shirt_size' id='shirt_size_<?=$user->user_id;?>'>
																	<option>S</option>
																	<option>M</option>
																	<option>L</option>
																	<option>XL</option>
																	</select>
																	<input type="hidden" name="addon2" id="add_on_2" value="yes">
																	<input type="hidden" name="fee" id="fee" value="<?=$fee?>">
																</p>
															</span>
														</li>
														<?
															break;
														default:														
													  ?>
													  
														<li>
															<span class="box">
																<p class="input">
																	<input type="checkbox" name="enroll" id="enroll" value="yes" class="checkbox_enroll" onclick="enroll_child(this);">
																	<label for="enroll-12345">I would like to enroll this child in the Tzivos Hashem 5771 program. ($36)</label>
																</p>
															</span>
														</li>
														
														<? if ($user->add_on_one > 0) : ?>
														<li class="add_on">
															<span class="box">
																<p class="input">
																	<input type="checkbox" name="addon1" id="add_on_1" value="yes">
																	<label for="addon1-12345">I would like to purchase Add-On Package 1 for this child. ($14)</label>
																	<br>Sweatshirt Size
																	<select name='shirt_size' id='shirt_size_<?=$user->user_id;?>'>
																	<option>S</option>
																	<option>M</option>
																	<option>L</option>
																	<option>XL</option>
																	</select>
																</p>
															</span>
														</li>
														<? endif; ?>
														
														<? if ($user->add_on_two > 0 && $user->school_class->class_grade > 2) : ?>
														<li class="add_on">
															<span class="box">
																<p class="input">
																	<input type="checkbox" name="addon2" id="add_on_2" value="yes">
																	<label for="addon2-12345">I would like to purchase Add-On Package 2 for this child. ($24)</label>
																</p>
															</span>
														</li>
														<? endif; ?>
														
														<?
															break;
														}
														?>
														
														<? endif; ?>
														
													</ul>
													
												</div>
												<!-- ***** CHILD ***** -->
												
											</div>
											
										</div>	
								<? endfor; ?>
								<!-- ***** ADMIN CHILDREN ***** -->
								
								<h2>Donate</h2>
								
								<div class="module" id="module-info">
								
									<div class="module_content">
									
										<div class="lists form">
										
											<ul>
											
												<li>
													<span class="box">
														<p class="input">
															<input type="checkbox" name="sponsor_reg" id="sponsor_reg" onclick="document.getElementById('sponsor_plus').checked=false;">
															<label for="sponsor_reg">I would like to donate a child's registration. ($36)</label>
														</p>
													</span>
												</li>
												
												<li>
													<span class="box">
														<p class="input">
															<input type="checkbox" name="sponsor_plus" id="sponsor_plus" onclick="document.getElementById('sponsor_reg').checked=false;">
															<label for="sponsor_plus">I would like to donate a child's registration + options. ($50)</label>
														</p>
													</span>
												</li>
												
												<li>
													<span class="box"><p class="input">
														<label for="sponsor_person">I would like to donate:</label>
														<input class="small" type="text" name="sponsor_person" id="sponsor_person" value="" /></p></span>
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
												  <span class="box"><p class="input"><input type="checkbox" name="support" id="support" value="yes"><label for="support-12345">I will support my child/ren in completing their missions.</label></p></span>
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
