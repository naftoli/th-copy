<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
	$_SESSION['admin_id'] = $_POST['admin_id'];
}
unset($_SESSION['toEnroll']);
unset($_SESSION['addon']);
define('REGISTERAMOUNT', 40);
include("db.php");
include("check_admin_id.php");

$next_page = "false";

// ***** GET THE ADMIN AND SCHOOL INFO ***** //
include("camps/includes/classes/admin.php");
include("camps/includes/classes/user.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
$admin->get_unregistered_children();
//////////if (count($admin->children) == 0) {
//////////	header("Location: admin.php?p=true");
//////////}

// ***** GET THE ADMIN AND SCHOOL INFO ***** //

$message = "";
$child_registered = "false";

// ----- Get all add_ons for this year ----- //
$s = "select year from school_add_ons group by year desc limit 1";
$r = mysql_query($s);
$y = mysql_fetch_row($r);
$year = $y[0];
$add_ons = array();
$sql = "SELECT * FROM school_add_ons WHERE YEAR=$year ORDER by add_on";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$add_ons[] = $row;
}
// ----- Get all add_ons for this year ----- //

// ----- Get the existing user add on fees ----- //
$enrolledChildren = 0;
for ($cno = 0; $cno < count($admin->children); $cno++) {
	$user_registration_fee = 0;
	for ($aono = 0; $aono < count($add_ons); $aono++) {
		$sql = "SELECT * FROM user_add_ons WHERE user_id=" . $admin->children[$cno]->user_id . " AND school_add_on_id=" . $add_ons[$aono]['school_add_on_id'];
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		if ($row['user_add_on_id'] > 0) {
			$user_registration_fee = $user_registration_fee + $add_ons[$aono]['price'];
		}
		
	}	

	if ($user_registration_fee > 0) {
		$user_registration_fee = $user_registration_fee + REGISTERAMOUNT;
		$enrolledChildren++;
	}
	
	$admin->children[$cno]->user_registration_fee = $user_registration_fee;
}
// ----- Get the existing user add on fees ----- //

// ----- ADMIN SPONSORS ----- //
$sponsor_amount = 0;
$year = date('Y');
$sql = "SELECT * FROM admin_sponsors WHERE admin_id=" . $admin->admin_id . " AND year=" . $year;
$query = mysql_query($sql);	
$row = mysql_fetch_assoc($query);
if ($row['admin_sponsor_id'] > 0) {
	$sponsor_amount = $row['amount'];
}
// ----- ADMIN SPONSORS ----- //

$add_on_size = '';
function check_user_add_ons($children, $userId, $school_add_on_id) {
	global $add_on_size;
	$add_on_size = '';
	$checked = '';
	foreach ($children as $child) {
		if ($child->user_id == $userId) {
			foreach ($child->user_add_ons as $add_on) {
				if ($school_add_on_id == $add_on['school_add_on_id']) {
					if ($add_on['size'] != '') 
						$add_on_size = $add_on['size'];
					$checked = ' CHECKED ';
					break;
				}
			}
		}
	}
	return $checked;
}

function checkAddOnSize($addOnSize) {
	global $add_on_size;
	if ($add_on_size == $addOnSize)
		echo ' selected="selected" ';
}
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
			var REGISTERAMOUNT = <?=REGISTERAMOUNT;?>;
			
			var admin_id = <?=$admin->admin_id;?>;
			
			var add_ons = new Array();
			<? foreach ($add_ons as $add_on) : ?>
			add_ons[<?=$add_on['school_add_on_id'];?>] = <?=$add_on['price'];?>;
			<? endforeach; ?>
			
			var children = new Array();
			<? foreach ($admin->children as $child) : ?>
				children.push(<?=$child->user_id;?>);
			<? endforeach; ?>
			
			var noOfChildrenEnrolled = 0;
			
			// ************************************************** READY ************************************************** //
			$(document).ready(function() {
			
				$('#Continue').click(function() { 
					var enrolledChildren = document.getElementById('enrolledChildren').value;
					
					var supported = checkSupported();
					
					if (supported) {
						var enrolled = true;
						
						for (cno = 0; cno < children.length; cno++) {
							var user_id = children[cno];							
							var user_registration_fee = parseFloat($('#user_registration_fee_' + user_id).val());
							var sponsor_amount = $("#sponsor_amount").val();
																					
							if (user_registration_fee > 0) {
								enrolled = checkEnrollment(user_id);
								
								if (enrolled) {									
									//var url = "https://www.mashpia.com/update_user_registration_fee.php?user_id=" + user_id + "&user_registration_fee=" +  user_registration_fee;
									var url = "https://www.mashpia.com/update_user_session.php?user_id=" + user_id + "&fee=" + user_registration_fee + "&sponsor_amount=" + sponsor_amount;
									
									$.getJSON(url, function(success) { 
										noOfChildrenEnrolled++;

										if (noOfChildrenEnrolled == enrolledChildren) {
											window.location = "https://www.mashpia.com/register_parent_4.php";
										}										
									});				
								}
							}
						}
					}
				});

				$('.checkbox_enroll').click(function() {
					var user_id = $(this).attr('id');
					user_id = user_id.replace('enroll_', '');

					// ----- If the child gets enrolled then the fee is set to the registration fee ----- //
					if ($(this).attr("checked") == true) {
						document.getElementById('enrolledChildren').value = parseInt(document.getElementById('enrolledChildren').value) + 1;
						$('#user_registration_fee_' + user_id).val(REGISTERAMOUNT);
					}
					// ----- If the child gets un-enrolled then the fee is set to 0 and all the add ons get unchecked  ----- //
					else {
						document.getElementById('enrolledChildren').value = parseInt(document.getElementById('enrolledChildren').value) - 1;
						unenrollUser(user_id);
						$('#user_registration_fee_' + user_id).val(0);
					}					
				});
				
				// ---------- ADD ONS ---------- //
				$('.add_on_checkbox').click(function() {
					var user_id = $(this).parents('li').attr('id');
					user_id = user_id.replace('list_item_', '');
						
					var enrolled = checkEnrollment(user_id);
					
					if (enrolled) {
					
						var school_add_on_id = $(this).attr('id');
						school_add_on_id = school_add_on_id.replace('school_add_on_id_', '');

						var price = add_ons[school_add_on_id];
						
						if ($(this).attr("checked") == true) {
							//var url = "https://www.mashpia.com/add_user_add_on.php?user_id=" + user_id + "&school_add_on_id=" +  school_add_on_id;
							var url = "https://www.mashpia.com/update_session_addon.php?user_id=" + user_id + "&school_add_on_id=" +  school_add_on_id;
								
							if ($(this).attr('value') == 'yes') {
								var select = $(this).parent('span').find('select');
								url = url + "&size=" + $(select).val();
							}
							
							$('#user_registration_fee_' + user_id).val( parseFloat($('#user_registration_fee_' + user_id).val()) + price );
							
							
						}
						else {
							//var url = "https://www.mashpia.com/delete_user_add_on.php?user_id=" + user_id + "&school_add_on_id=" +  school_add_on_id;
							var url = "https://www.mashpia.com/delete_session_addon.php?user_id=" + user_id + "&school_add_on_id=" +  school_add_on_id;
							$('#user_registration_fee_' + user_id).val( parseFloat($('#user_registration_fee_' + user_id).val()) - price );
						}
						//alert(url);	
						$.getJSON(url, function(success) { if (success == 0) alert('UPDATE NOT SUCCESSFULL');});				
						
					}
					else {
						if ($(this).attr("checked") == true) {
							document.getElementById($(this).attr('id')).checked = false;
						}
						else {
							document.getElementById($(this).attr('id')).checked = true;
						}
					}
					
				});
				// ---------- ADD ONS ---------- //

				// ---------- UPDATE THE SIZE IF IT IS ALREADY CHECKED ---------- //
				$('.sizeSelect').change(function() {
				
					if ($(this).parent('span').find('input').attr("checked") == true) {
						var supported = checkSupported();
						
						if (supported) {
							var user_id = $(this).parents('li').attr('id');
							user_id = user_id.replace('list_item_', '');
							var size = $(this).val();
							var school_add_on_id = $(this).parent('span').find('input').attr('id');
							school_add_on_id = school_add_on_id.replace('school_add_on_id_', '');
						
							var url = "https://www.mashpia.com/update_user_add_on.php?user_id=" + user_id + "&school_add_on_id=" +  school_add_on_id + "&size=" + size;
							
							$.getJSON(url, function(success) {
								if (success == 0)
									alert('UPDATE NOT SUCCESSFULL');						
							});					
						}
						else {
						}
					}
					
				});
				// ---------- UPDATE THE SIZE IF IT IS ALREADY CHECKED ---------- //
				
				$('#sponsor_reg').click(function() {
					if (document.getElementById('sponsor_reg').checked == true) {
						$('#sponsor_amount').attr('disabled', false);
						if (document.getElementById('sponsor_amount').value == '')
							var url = "https://www.mashpia.com/sponsor_amount.php?admin_id=" + admin_id + "&amount=" +  REGISTERAMOUNT;
						else 
							var url = "https://www.mashpia.com/sponsor_amount.php?admin_id=" + admin_id + "&amount=" +  document.getElementById('sponsor_amount').value;					
					}
					else {
						$('#sponsor_amount').attr('disabled', true);
						$('#sponsor_amount').val('');
						var url = "https://www.mashpia.com/delete_sponsor_amount.php?admin_id=" + admin_id;
					}
				
					$.getJSON(url, function(success) { if (success == 0) alert('UPDATE NOT SUCCESSFULL');});				
				});
				
				$('#sponsor_amount').blur(function() {
					var url = "https://www.mashpia.com/sponsor_amount.php?admin_id=" + admin_id + "&amount=" +  $(this).val();
					$.getJSON(url, function(success) { if (success == 0) alert('UPDATE NOT SUCCESSFULL');});
				});
				
			});			
			// ************************************************** READY ************************************************** //
			
			function unenrollUser(user_id) {
				var list_item = '#list_item_' + user_id;
				var checkboxes = $(list_item).find('input');
				$.each(checkboxes, function() { 
					$(this).attr('checked', '');
					var school_add_on_id = $(this).attr('id');
					school_add_on_id = school_add_on_id.replace('school_add_on_id_', '');
					var url = "https://www.mashpia.com/delete_user_add_on.php?user_id=" + user_id + "&school_add_on_id=" +  school_add_on_id;
					$.getJSON(url, function(success) { if (success == 0) alert('UPDATE NOT SUCCESSFULL');});				
				});
				
				var url2 = "https://www.mashpia.com/update_user_registration_fee.php?user_id=" + user_id + "&user_registration_fee=0";
				$.getJSON(url2, function(success) { if (success == 0) { alert('UPDATE NOT SUCCESSFULL'); } });				
			}
			
			function checkEnrollment(user_id) {
				var checkBoxId = 'enroll_' + user_id;
				if ( document.getElementById(checkBoxId).checked == false ) {
					alert("You have to enroll the child in the Tzivos Hashem 5774 program first.");
					return false
				}
				else {
					return true;
				}
			}
			
			function checkSupported() {
				if (document.getElementById("support").checked == false) {
					document.getElementById("support").focus();
					alert("Please agree to the Terms.");
					return false;
				}	
				else {
					return true;
				}
			}
			
			<?
			
				for ($uno = 0; $uno < count($admin->children); $uno++) {
					$user = $admin->children[$uno];
					$users[$uno] = $user->user_id;
					echo "users[" . $uno . "]=" . $user->user_id . ";\n\t\t\t";
				}
			?>
		</script>
		<!--Copyright Ariel Shkedi 2007-2010-->
	</head>

	<body onload="check_next_page(); check_child_registered();">
	
		<input type="hidden" id="enrolledChildren" value="<?=$enrolledChildren;?>">
		
		<NOSCRIPT>
			<P STYLE="color: red; font-size: larger;">Notice: You have javascript disabled. Some parts of the site will not function without javascript.</P>
		</NOSCRIPT>
		
		<div id="wrapper">
		
			<div id="nav" class="wizard">
				<div class="col_title_bg"></div>
				<div class="col_title">Menu</div>
				<? $curr = 3; ?>
				<? if ( isset( $_POST['register'] ) && $_POST['register'] == 'children' ) {
					include 'register_children_menu.php';
				} else {
					include 'register_parent_menu.php'; 
				}?>
			</div>
			
			<div id="content">
			
				<div class="col_title_bg"></div>
				
				<div class="slider_container">
				
					<div class="slider">
					
						<div class="col_title"></div>
						
						<div class="col_content">
							<h1>Step 3 - Child Registration</h1>
							
								<div class="module" id="module-info">
										
									<div class="module_content">
											
										<div class="lists form">
											<ul>
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
															<img src="images/registration/siddur.png" width="48" height="48" />
															Siddur with Biur Tefilla
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
														<li>
															<img src="images/registration/charge_cards.png" width="48" height="48" />
															Scratch Off Charge Cards
														</li>
													  </ul>
												  </li>
												  
												  <li>
													<h4>Additional Store Prizes</h4>
													<p>Purchase store prizes at 50% off, for your children to buy with their miles in the online store.</p>
												  </li>
												  
												  <li>
													  <ul class="register_package">
													  <? foreach ($add_ons as $add_on) : ?>
														<li>
															<img src="images/registration/<?=strtolower($add_on['title']);?>.png" width="48" height="48" />
															<?=$add_on['title'];?>
														</li>
													  <? endforeach; ?>
													  </ul>
												  </li>
											</ul>
										</div>
									</div>
								</div>
													  
													  
	 								
								<!-- ***** ADMIN CHILDREN ***** -->
								<? for ($uno = 0; $uno < count($admin->children); $uno++) : ?>
									<? $user = $admin->children[$uno]; ?>
									
										<h2>Enroll</h2> 
										
										<input type="hidden" id="user_registration_fee_<?=$user->user_id;?>" name="user_registration_fee_<?=$user->user_id;?>" value="<?=$user->user_registration_fee;?>">
										
										<div class="module" id="module-info">
										
											<div class="module_content">
											
												<!-- ***** CHILD ***** -->
												<div class="lists form" id="<?=$user->user_id;?>" name="<?=$user->user_id;?>">
													<ul>
														<li>
															  <span class="photo"><img width="32" height="32" alt="" src="<?=($user->user_photo_id=='')?'images/generic_user_small.png':'/file_view.php?id='.$user->user_photo_id?>"></span>
															  <span class="label large"><?=$user->first;?> <?=$user->last;?></span>
															  <div class="box">
																<div class="role">Grade 
																	<? 
																	if (isset($user->school_class->class_grade)) {
																		echo $user->school_class->class_grade;?> - <?=$user->school_class->class_teacher;
																	}?></div>
																<div class="info"><?=$user->school_name;?></div>
															  </div>
															  <? if ($user->user_registered != "") : ?>
															  <span class="label price">Registered</span>
															  <? endif; ?>

														</li>
														
														<? if ($user->user_registered == "") : ?>									  
													  
														<li>
															<span class="box">
																<p class="input">
																	<input id="enroll_<?=$user->user_id;?>" data="<?=$user->user_id;?>" type="checkbox" name="enroll" value="yes" class="checkbox_enroll" <? if ($user->user_registration_fee > 0) echo ' checked="checked"'; ?>">
																	<label for="enroll-12345">I would like to enroll this child in the Tzivos Hashem 5774 program. ($<?=REGISTERAMOUNT;?>)</label>
																</p>
															</span>
														</li>
														
														<script type="text/javascript">
														function popup(link) {
															window.open(link, 'sizes', 'width=200,height=200');
															return false;
														}
														</script>
														
														<li name="add_ons" class='add_on' id="list_item_<?=$user->user_id;?>">
															<span class='box'>
																<p class='input'>
																	I would also like to purchase the following items: <a href="suggested_sizes.php" onClick="return popup(this)">(view suggested sizes)</a><br />
																	
																	<? foreach ($add_ons as $add_on) : $i = 1; ?>																														
																		
																		<span>
																		
																		<? $checked = check_user_add_ons($admin->children, $user->user_id, $add_on['school_add_on_id']); ?>
																		
																		<input class="add_on_checkbox" <?=$checked;?> id="school_add_on_id_<?=$add_on['school_add_on_id'];?>" type='checkbox' <? if ($add_on['needs_size'] == 1)  echo 'value="yes"'; else echo 'value="no"';?>>
																		
																		<?=$add_on['title'] . " <strike>$" . $add_on['value'] . "</strike> $" . $add_on['price'];?>
																		
																		<? if ($add_on['needs_size'] == 1) {
																		
																		$size = $add_on['title'] . "_size";
																		
																		//echo 'ADD ON SIZE:' . $add_on_size . '<br>';
																		
																		switch ($add_on['title']) {
																			case 'Sweatshirt':
																		?>
																				<select class="sizeSelect">
																					<option <? if ($add_on_size != '') checkAddOnSize('s'); ?> value='s'>S</option>
																					<option <? if ($add_on_size != '') checkAddOnSize('m'); ?> value='m' selected >M</option>
																					<option <? if ($add_on_size != '') checkAddOnSize('l'); ?> value='l'>L</option>
																					<option <? if ($add_on_size != '') checkAddOnSize('xl'); ?> value='xl'>XL</option>
																				</select>
																		<?
																				break;
																			case 'Cap':
																		?>
																				<select class="sizeSelect">
																					<option <? if ($add_on_size != '') checkAddOnSize('s'); ?> value='s'>S</option>
																					<option <? if ($add_on_size != '') checkAddOnSize('l'); ?> value='l'>L</option>
																				</select>
																		<?
																				break;
																			case 'Yarmulka':
																		?>
																				<select class="sizeSelect">
																					<option <? if ($add_on_size != '') checkAddOnSize('4'); ?> value='4'>4</option>
																					<option <? if ($add_on_size != '') checkAddOnSize('5'); ?> value='5'>5</option>
																				</select>
																		<?
																				break;
																			}																	
																		}
																		
																		$i++;
																		?>
																		
																		</span>

																		<br />
																		
																	<? endforeach; ?>
																	
																</p>
															</span>
														</li>
														<!--
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
														
														<? if ($user->add_on_two > 0) : ?>
														<li class="add_on">
															<span class="box">
																<p class="input">
																	<input type="checkbox" name="addon2" id="add_on_2" value="yes">
																	<label for="addon2-12345">I would like to purchase Add-On Package 2 for this child. ($24)</label>
																</p>
															</span>
														</li>
														<? endif; ?>
														<? endif; ?>
														-->
													</ul>
													
												</div>
												<!-- ***** CHILD ***** -->
												
											</div>
											
										</div>	
								<? endfor; ?>
								<!-- ***** ADMIN CHILDREN ***** -->
								
								<h2>Terms</h2>
								<div class="module" id="module-info">
									<div class="module_content">
										<div class="lists form">
											<ul>
												<li>
												  <span class="box"><p class="input"><input type="checkbox" name="support" id="support" value="yes"><label for="support-12345">I will support my child/ren in completing their missions.</label></p></span>
											  </li>
											</ul>
										</div>
									</div>
								</div>
								
								<h2>Donate</h2>
								
								<div class="module" id="module-info">
								
									<div class="module_content">
									
										<div class="lists form">
										
											<ul>
											
												<li>
													<span class="box">
														<p class="input">
															<input name="admin_sponsor" type="checkbox" name="sponsor_reg" id="sponsor_reg" <? if ($sponsor_amount != 0) echo ' checked="checked" '; ?>>
															<label for="sponsor_reg">I would like to donate a child's registration. ($<?=REGISTERAMOUNT;?>)</label>
														</p>
													</span>
												</li>
												
												<li>
													<span class="box"><p class="input">
														<label>I would like to donate another amount:</label>
														<input id="sponsor_amount" class="small" type="text" name="sponsor_amount" <?if ($sponsor_amount == 0)  echo ' disabled="true" ';?> value="<?if ($sponsor_amount > 0 && $sponsor_amount != REGISTERAMOUNT) echo $sponsor_amount; ?>" /></p></span>
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
													<input id="Continue" type="submit" value="Continue" class="button"> 
												</li>
											</ul>
										</div>
										
										
		<div id="info">
		</div>
		
										
										
										
									</div>
								</div>
							
						</div>
						
					</div>
					
				</div>
				
			</div>
			
		</div>

	</body>
	
</html>
