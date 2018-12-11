<?php
session_start();

$admin_auth = array('user'); 
$ui_type = 'child';

require('header.php');

include('objects/admin.php');
include('objects/user.php');
include('objects/school.php');
$admin = new admin(NULL, $_SESSION['admin_id']);
$admin->get_children();

// ----- Get all add_ons for this year ----- //
$addOns = array();
$s = "select year from school_add_ons group by year desc limit 1";
$r = mysql_query($s);
$y = mysql_fetch_row($r);
$year = $y[0];
$sql = "SELECT * FROM school_add_ons WHERE year=" . $year . " ORDER by add_on";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	array_push($addOns, $row);
}
//print_r($addOns);
// ----- Get all add_ons for this year ----- //

function getSelect($title) {

	switch ($title) {
		case 'Sweatshirt':
			echo '<select class="sizeSelect">
				<option value="s">S</option>
				<option value="m" selected >M</option>
				<option value="l">L</option>
				<option value="xl">XL</option>
			</select>';
		break;
		
		case 'Cap':
			echo '<select class="sizeSelect">
				<option value="s">S</option>
				<option value="l">L</option>
			</select>';
		break;
		
		case 'Yarmulka':
			echo '<select class="sizeSelect">
				<option value="4">4</option>
				<option value="5">5</option>
			</select>';
		break;
	}																	

}

function checkUserAddOn($user_id, $school_add_on_id) {
	global $admin;
	$disabled = '';
	foreach ($admin->children as $child) {
		if ($child->user_id == $user_id) {
			foreach ($child->user_add_ons as $user_add_on) {
				if ($user_add_on['school_add_on_id'] == $school_add_on_id) {
					$disabled = ' checked="checked" disabled="disabled" ';
					break;
				}
			}
		}
	}
	return $disabled;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/DIV/html4/sDIVict.dtd">


<HTML>

	<HEAD>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Children Add Ons</title>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
	</HEAD>
	
	<BODY>
		<? include("admin_header.php"); ?>

		<div id="info">
		</div>
		
		<DIV class="body">
			<H1><?php echo $admin->first . ' ' . $admin->last;?> Children Add Ons</H1>
			
			<DIV class="content">
						
				<script>
					var users = new Array();
					<? foreach ($admin->children as $child) : ?>
					users.push(<?=$child->user_id;?>);
					<? endforeach; ?>
					
					function formatCurrency(num) {
						num = num.toString().replace(/\$|\,/g,'');
						
						if	(isNaN(num))
							num = "0";
							
						sign = (num == (num = Math.abs(num)));
						num = Math.floor(num*100+0.50000000001);
						cents = num%100;
						num = Math.floor(num/100).toString();
						
						if (cents < 10)
							cents = "0" + cents;
							
						for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
							num = num.substring(0,num.length-(4*i+3))+','+
							
						num.substring(num.length-(4*i+3));
						
						return (((sign)?'':'-') + num + '.' + cents);
					}

					$(document).ready(function() {
						
						$('.addOnCheckbox').click(function() {

							var price = $(this).attr('data');
							var info = $(this).attr('id').split('_');
							var user_id = info[0];
							var school_add_on_id = info[1];

							if ($(this).attr('checked') == true) 
								var newTotal = parseFloat($('#total_' + user_id).html()) + parseFloat(price);
							else
								var newTotal = parseFloat($('#total_' + user_id).html()) - parseFloat(price);
							
							$('#total_' + user_id).html(formatCurrency(newTotal));
							
							var grand_total = parseFloat($('#grand_total_span').html());
							if ($(this).attr('checked') == true) 
								grand_total = grand_total + parseFloat(price);
							else
								grand_total = grand_total - parseFloat(price);
							$('#grand_total_span').html(grand_total);
						});
						
					});
					
					$(function() {
						
						$('.payment_button').click(function() {	

							credit_card_info  = perform_validation();
									
							if (credit_card_info) {
							
								var grand_total = parseFloat($('#grand_total_span').html());
									
								if (grand_total > 0) {						
									var json_add_ons = '';
									
									for (uno = 0; uno < users.length; uno++) {
										var user_add_ons = $('#add_ons_table_' + users[uno]).find('tr');
										var add_on_found = false;
										var add_on_info = '';
										
										$.each(user_add_ons, function() { 									
											var input = $(this).find('input');
											var info1 = $(input).attr('id');
											var select = $(this).find('select');
											
											if ($(input).size() > 0) {
											
												if ( $(input).attr('checked') == true && $(input).attr('disabled') == false) {
													add_on_found = true;
													var info2 = info1.split('_'); 
													var school_add_on_id = info2[1];
													
													add_on_info = add_on_info + school_add_on_id;
													
													if ( $(select).size() > 0) {
														add_on_info = add_on_info + ',' + $(select).val();
													}
													
													add_on_info = add_on_info + ';';
													
												}
												
											}
											
										});	

										if (add_on_found == true) {
											add_on_info = add_on_info.substr(0, add_on_info.length - 1);
											json_add_ons = json_add_ons + users[uno ]+ '-' + add_on_info + ":";
										}
										
									}
									
									if (json_add_ons.length > 0) {
										
										// Process credit card //
										var dataToSend = "cc_first_name=" + $('#cc_first_name').val() +
														 "&cc_amount=" + $('#grand_total_span').html() +			
														 "&cc_last_name=" + $('#cc_last_name').val() +
														 "&cc_address=" + $('#cc_address').val() +
														 "&cc_state=" + $('#cc_state').val() +
														 "&cc_zip=" + $('#cc_zip').val() +
														 "&cc_description=child add_ons" + 			
														 "&ccnum=" + $('#ccnum').val() +
														 "&ccexp=" + $('#ccexp').val() +
														 "&cccvv=" + $('#cccvv').val();			
											  
										$.ajax({ url: 'register_authorize_net.php', type: "GET", data: dataToSend, success: 
											function(data) {
												var info = data.split("\n");
												var success = info[0];
												
												if (success == "1") { 
													// Insert the add ons to the database //
													json_add_ons = json_add_ons.substr(0, json_add_ons.length - 1);
													var url = 'https://mashpia.com/add_functions.php?function_name=add_ons&add_ons=' + json_add_ons;
															
													$.getJSON(url, function(success) {
														if (success) 
															disableCheckBoxes(); 
														else 
															alert('ADD ONS UPDATE NOT SUCCESSFULL');
													});
												
												}
												else { 
													alert(info[0]); 
												}
											}
										});					
										// Process credit card //
										
									}
								}
								else {
									alert('No add ons chosen');
								}
								
							}
							
						});
						
					});
					
					function disableCheckBoxes() {	
						var user_tables = $('.pretty_grid');
						$.each(user_tables, function() { 		
							var checkboxes = $(this).find('input');
							$.each(checkboxes, function() { 
								if ( $(this).attr('class') == 'addOnCheckbox' ) {
									if ($(this).attr('checked') == true) {
										$(this).attr('disabled', 'disabled');
									}
								}
							});
							var spans = $(this).find('span');
							$.each(spans, function() { 
								$(this).html('0.00');
							});
						});
					}
					
					
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
					
					function formatCurrency(num) {
						num = num.toString().replace(/\$|\,/g,'');
						
						if (isNaN(num))
							num = "0";
							
						sign = (num == (num = Math.abs(num)));
						num = Math.floor(num*100+0.50000000001);
						cents = num%100;
						num = Math.floor(num/100).toString();
						
						if (cents < 10)
							cents = "0" + cents;
							
						for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
							num = num.substring(0,num.length-(4*i+3)) + ',' + num.substring(num.length-(4*i+3));						
						
						return (((sign)?'':'-') + num + '.' + cents);
					}					
				</script>

				<? $counter = 0; ?>
				
				<? foreach ($admin->children as $child) : $counter++; ?>
				
					<TABLE id="add_ons_table_<?=$child->user_id;?>" class="pretty_grid" style="width:100%; background-color:#EEEEEE;">
						<TR>
							<TH colspan="3" style="font-size:16px; font-weight:bold;"><?=$child->first . ' ' . $child->last;?></TH>
						</TR>
						
						<? foreach ($addOns as $addOn) : ?>
						
						<? $disabled = checkUserAddOn($child->user_id, $addOn['school_add_on_id']); ?>
						
						<TR>
							<TD style="font-size:16px; font-weight:bold;" style="text-align:center;">										
								<input type="checkbox" <?=$disabled;?> data="<?=$addOn['price'];?>" id="<?=$child->user_id;?>_<?=$addOn['school_add_on_id'];?>" class="addOnCheckbox">
								<?
								if ($addOn['title'] == 'Siddur') {
									switch ($child->gender) {
										case 'm':
										case 'M':
											echo "Blue ";											
											break;
										case 'f':
										case 'F':
											echo "Pink ";
											break;
									}
								}
								?>
								<?=$addOn['title'];?>
								
								
							<? if ($addOn['needs_size']) : ?>
								<? getSelect($addOn['title']); ?>
							<? endif; ?>
								
								
								
							</TD>
							<TD style="font-size:16px; font-weight:bold;" style="text-align:center;">		
								<strike><?=$addOn['value'];?></strike>
							</TD>
							<TD name="price" style="font-size:16px; font-weight:bold; text-align:right;">		
								<?=$addOn['price'];?>
							</TD>				
						</TR>
						<? endforeach; ?>
						
						<TR>
							<TD colspan="3" style="font-size:16px; font-weight:bold; text-align:right;">TOTAL: $<span id="total_<?=$child->user_id;?>">0.00</span></TD>
						</TR>
					
					</TABLE>
				
					<br>
					
				<? endforeach; ?>
				
				<TABLE id="grand_total_table" class="pretty_grid" style="width:100%; background-color:#EEEEEE;">
					<TR>
						<TH style="font-size:16px; font-weight:bold;">GRAND TOTAL</TH>
					</TR>					
					<TR>
						<TD style="font-size:16px; font-weight:bold; text-align:right;">GRAND TOTAL: $<span id="grand_total_span">0.00</span></TD>
					</TR>					
				</TABLE>
				
				<br />
				<br />

				<div id="box_cc_info">
								
					<h1>Credit Card Details</h1>
									
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
								</ul>
							</div>
											
						</div>
										
					</div>
									
				</div>
				
				<br />
				
				<CENTER>
					<INPUT type="button" class="payment_button" value="PAY">
				</CENTER>
				
			</DIV>
			
		</DIV>
		
	</BODY>
	
</HTML>