<?php
//header("Location: admin_users_register_new.php");
session_start();

//header("Location: under_construction.php");
$admin_auth = array('school'); 
require_once('header.php'); 
require_once('calendar.php');
$ui_type = 'school';
require_once('admin_ui.php');
//assure_id_school('school_id');

if (isset($_GET['admin_id'])) {
	$admin_id = $_GET['admin_id'];
} else {
	$admin_id = $admin_user['admin_id'];
}

include("classes/admin.php");
include("classes/school.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
$admin->get_school_id(); 
$admin->get_schools();
	
$admin_user['admin_id'] = $admin_id;
if ( $admin->auth != "")
	$admin_user['auth'] = $admin->auth;
else
	$admin_user['auth'] = $admin->get_school_auth();

$school_id = 0;
$first = "";
$last = "";

if (isset($_GET['school_id']))
	$school_id = $_GET['school_id'];

if ($school_id == "")
	$school_id = 0;
	
if (isset($_POST["hidden_school_id"]))
{
	$school_id = $_POST["hidden_school_id"];
	
	if (isset($_POST["first"]))
		$first = $_POST["first"];
		
	if (isset($_POST["last"]))
		$last = $_POST["last"];
}

$schools = array();
if ($admin->auth == "super")
{	
	$sql = "SELECT * FROM schools ORDER BY school_name";
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) 
	{
		$school = new school($row);
		array_push($schools, $school);
	}
} 	
else
{
	$sql = "SELECT s.* ";
	$sql = $sql . "FROM admin_auths AS aa ";
	$sql = $sql . "JOIN schools AS s ON (aa.id=s.school_id) ";
	$sql = $sql . "WHERE aa.admin_id=" . $admin->admin_id . " ";
	$sql = $sql . "AND aa.auth='school' ";
	$sql = $sql . "AND aa.role_id=16";
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);
	
	if ($num_rows > 1)
	{
		while ($row = mysql_fetch_assoc($query)) 
		{
			$school = new school($row);
			array_push($schools, $school);
		}
	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML>
	<HEAD>

		<TITLE>Soldiers' Registration - Tzivos Hashem Management System</TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<script src="camps/scripts/jquery.tools.min.js"></script>
		
		<SCRIPT type="text/javascript">
			$(document).ready(function() {
				
				var user_ids = "";				
				var first = "<?=$first;?>";
				var last = "<?=$last;?>";
				var is_loaded = false;
				
				get_students();
			
				$('.marking_list div a.next').click(function(){
					$(this).siblings('select').find('option:selected').next().attr('selected','selected').parent().change();
				});
				
				$('.marking_list div a.prev').click(function(){
					$(this).siblings('select').find('option:selected').prev().attr('selected','selected').parent().change();
				});
				
				$(".school_list select").sSelect().change(function () {
					get_students();
				});
			
				$("#search_button").live('click', function() {
					get_students();
				});				
				
				// *********** REGISTER STUDENTS *********** //
				$('#register_students').live('click', function() {	
						
					user_ids = "";
					
					// ***** Get the user ids of each child that is being registered ***** //
					$.each($("#students_table").find("td[name=student_total]"), function() { 		
						
						if ($(this).html() > 0)
							user_ids = user_ids + $(this).parents("tr").attr("data") + ":";
						
					});
					// ***** Get the user ids of each child that is being registered ***** //
					
					if (user_ids != "")
						user_ids = user_ids.substr(0, user_ids.length - 1)
					
					
					if ($('#cc_card_check').attr('checked')) 
					{
						var success = perform_credit_card_validation();
					}
					else
					{
						document.getElementById("cc_card_check").focus();
						alert("Please check Credit Card Approval button to authorize purchase on school credit card.");
					}
										
				});
				// *********** REGISTER STUDENTS *********** //
				
				function perform_credit_card_validation() 
				{	
					var dataToSend =  			
						"cc_first=" +  $('#cc_first').val() +
						"&cc_amount=" 	 +  $('#grand_total_id').html() +
						"&cc_last_name=" +  "na" +
						"&cc_address="   +  "na" +
						"&cc_state="  	 +  "na" +
						"&cc_zip="  	 +  "na" +
						"&cc_description=child registration->"	+  user_ids + 
						"&ccnum="  		 +  $('#cc_number').val() +
						"&ccexp="  		 +  $('#cc_exp').val() +
						"&cccvv="  		 +  $('#cc_cvv').val() ;									
				
					$.ajax({
						url: 'register_authorize_net.php',
						type: "GET",
						data: dataToSend,
						async: false, 
						success: function(data) 
						{									 					
							var info = data.split("\n");										
							var success = info[0];
							cc_transaction_number_global = info[3];
							
							if (success == "1") 
							{
								var parameters = "";
								$.each($("#students_table").find("tr[name=student_row]"), function() { 	
								
									var user_id = $(this).attr("data");
									var amount = $(this).find("#student_total").html();
									var registered = $(this).find("td[name=user_registered]").html();							
									
									if (amount > 0)
									{
										if ($(this).find("input[name=add_on_one]").is(":checked"))
										{
											var add_on_one = "1" + $(this).find("select[name=shirt_size]").val();
										}
										else
										{
											var add_on_one = "0";
										}
										
										if ($(this).find("input[name=add_on_two]").is(":checked"))
											var add_on_two = "1";
										else
											var add_on_two = "0";
										
										if (registered.trim() == "")
											registered = "0";
										else
											registered = "1";
											
										parameters = parameters + user_id + ";" + amount + ";" + add_on_one + ";" + add_on_two + ";" + registered + ":";							
									}
									
								});
								
								parameters = parameters.substr(0, parameters.length - 1);						
								var url = "add_functions.php?function_name=register_students&parameters=" + parameters;

								$.getJSON(url, function(success) {
								});	

								alert("Students have been registered/updated");
								
								get_students();
							}
							else 
							{						
								$('#credit_card_approval_results').html(info[0] + "<br>") ;					
								$('#box_cc_auth').css("display", "block");
							}
							
						}			  
						// ***** SUCCESS ***** //
						
					});	
					// ***** AJAX ***** //
				} 		
				
			
				$('#school_review').live('click', function() {	
					window.location = "/admin_school_new.php?school_id=" + $("#school_id").val() + "&action=edit";	
				});
			
				// ********** REGISTARTION FEES ********** //
				$("#toggle_registration_fee").live('click', function() {
								
					if ($(this).is(":checked"))
						var checked = true;
					else
						var checked = false;
					
					if (checked == false)
					{
						if ($("#toggle_add_on_one").is(":checked"))
						{
							$("#toggle_add_on_one").attr("checked", false);
							$("#toggle_add_on_one").click();
							$("#toggle_add_on_one").attr("checked", false);
						}
						
						if ($("#toggle_add_on_two").is(":checked"))
						{
							$("#toggle_add_on_two").attr("checked", false);
							$("#toggle_add_on_two").click()							
							$("#toggle_add_on_two").attr("checked", false);							
						}
					}
					
					$.each($("#students_table").find("input[name=registration_fee]"), function() { 						
					
						if (checked == true)
							$(this).parents("tr").attr("id", "registered");
						else
							$(this).parents("tr").attr("id", "unregistered");
							
						if (!$(this).is(':checked') && checked == true)
						{
							$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) + 36));
							$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), 36, "add"));
						}
						
						if ($(this).is(':checked') && checked == false)
						{
							$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) - 36));
							$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), 36, "subtract"));
						}
						
						$(this).attr("checked", checked);
						
					});
					
				});
				
				$("#registration_fee").live('click', function() {				
					if ($(this).is(":checked"))
					{
						$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) + 36));
						$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), 36, "add"));
					}
					else
					{
						if ($(this).parent("div").find("input[name=add_on_one]").is(":checked"))
						{
							$(this).parent("div").find("input[name=add_on_one]").attr("checked", false);
							$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), 14, "subtract"));
							$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) - 14));
						}
						
						if ($(this).parent("div").find("input[name=add_on_two]").is(":checked"))
						{
							$(this).parent("div").find("input[name=add_on_two]").attr("checked", false);
							$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), 24, "subtract"));							
							$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) - 24));
						}
						
						$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) - 36));
						$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), 36, "subtract"));
					}
				});
				// ********** REGISTARTION FEES ********** //
				
				// ********** ADD ON ONE FEES ********** //				
				$("#toggle_add_on_one").live('click', function() {						
					
					if ($(this).is(":checked"))
						var checked = true;
					else
						var checked = false;
					
					$.each($("#students_table").find("input[name=add_on_one]"), function() { 						
					
						if ($(this).parents("tr").attr("id") == "registered")
						{
						
							if (!$(this).is(':checked') && checked == true)
							{
								$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) + 14));
								$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), 14, "add"));
							}
							
							if ($(this).is(':checked') && checked == false)
							{
								$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) - 14));
								$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), 14, "subtract"));
							}
						
							$(this).attr("checked", checked);
							
						}
						
					});
				});				
				
				$("#add_on_one").live('click', function() {	
					if ($(this).is(":checked"))
					{
						if ($(this).parents("div").find("input[name=registration_fee]").is(":checked") == false)
						{
							$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) + 36));
							$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), 36, "add"));
							$(this).parent("div").find("input[name=registration_fee]").attr("checked", true);
						}
						
						$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) + 14));
						$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), 14, "add"));
					}
					else
					{
						$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) - 14));
						$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), 14, "subtract"));
					}
				});
				// ********** ADD ON ONE FEES ********** //				
				
				// ********** ADD ON TWO FEES ********** //				
				$("#toggle_add_on_two").live('click', function() {				

					if ($(this).is(":checked"))
						var checked = true;
					else
						var checked = false;				
				
					$.each($("#students_table").find("input[name=add_on_two]"), function() { 
					
						if ($(this).parents("tr").attr("id") == "registered")
						{
							if (!$(this).is(':checked') && checked == true)
							{
								$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) + 24));
								$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), 24, "add"));
							}
							
							if ($(this).is(':checked') && checked == false)
							{
								$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) - 24));
								$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), 24, "subtract"));							
							}
							
							$(this).attr("checked", checked);
						}
						
					});
				});								
				
				$("#add_on_two").live('click', function() {				
					if ($(this).is(":checked"))
					{
						if ( $(this).parents("div").find("input[name=registration_fee]").is(":checked") == false)
						{
							$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) + 36));
							$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), 36, "add"));
							$(this).parent("div").find("input[name=registration_fee]").attr("checked", true);
						}
					
						$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) + 24));
						$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), 24, "add"));
					}
					else
					{
						$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) - 24));
						$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), 24, "subtract"));
					}
				});
				// ********** ADD ON TWO FEES ********** //				
				
				$('#class_id').live("change", function() {
					get_students();
				});
				
				
				$("#school_id").change(function() {
					$("#school_review").attr("href", "/admin_school_new.php?school_id=" + $(this).val() + "&action=edit");
					get_students();
				});
				
				function calculate_student_total(student_total, amount, add_or_substract)
				{
					if (add_or_substract == "add")
						return money_format(student_total + amount);
					else
						return money_format(student_total - amount);
				}
				
				function get_students()
				{	
					if ($("#school_id").val() > 0)
						var url = "register_school_students.php?school_id=" + $("#school_id").val();
					else
						var url = "register_school_students.php?school_id=<?=$admin->school_id;?>";
						
					if (is_loaded == true)
					{
						if ($("#class_id").val() > 0)
							url = url + "&class_id=" + $("#class_id").val();
												
						if ($("#first").val() != "")
							url = url + "&first=" + $("#first").val();
							
						if ($("#last").val() != "")
							url = url + "&last=" + $("#last").val();
					}
					
					var http = getHTTPObject();
					http.open("GET", url, true);
								
					http.onreadystatechange = function() {
															
						if (http.readyState == 4 && http.status == 200) 
						{
							$("#students_div").html(http.responseText);
						} 
											
					}
											
					http.send(null);								
				}
				
				function getHTTPObject() {
					var xmlhttp;

					if (window.XMLHttpRequest) 
					{
						xmlhttp = new XMLHttpRequest();
					}
					else if (window.ActiveXObject)
					{ 
						xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
									
						if (!xmlhttp) 
						{
							xmlhttp=new ActiveXObject("Msxml2.XMLHTTP");
						}
					}
								
					return xmlhttp; 
				}
				
				function money_format(amount)
				{
					var i = parseFloat(amount);
					
					if (isNaN(i)) 
						i = 0.00; 
						
					var minus = '';
					
					if (i < 0) 
						minus = '-';
						
					i = Math.abs(i);
					i = parseInt((i + .005) * 100);
					i = i / 100;
					s = new String(i);
					
					if (s.indexOf('.') < 0) 
						s += '.00'; 
						
					if (s.indexOf('.') == (s.length - 2))
						s += '0'; 
						
					s = minus + s;
					
					return s;
				}
					
				is_loaded = true;
			});
		</SCRIPT>
	</HEAD>

	<BODY>		
		<? include('lang.php'); ?>
		<? include('admin_header.php'); ?>
		
		<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
		
			<DIV class="body">
			
				<DIV class="sub_menu">		
					<? if ($message != "") : ?>
						<H2>
							<?=$message?>
						</H2>
					<?endif;?>
				</DIV>
				
				<H1>
					<?=T_('Base Management')?>
				</H1>
				
				<? if (count($schools) > 1) : ?>	

					<div class="infobox2 marking_list clearfix">
					
						<div class="school_list select_box">
							<a class="prev button">
								<span class="icon"></span>
								<span class="label"><?=T_('Previous School')?></span>
							</a>
						
							<SELECT name="school_id" id="school_id">
								<? foreach ($schools as $school) : ?>
									<? if ($school->school_id == $school_id) : ?>
									<OPTION SELECTED value="<?=$school->school_id;?>"><?=$school->school_name;?></OPTION>
									<? else : ?>
									<OPTION value="<?=$school->school_id;?>"><?=$school->school_name;?></OPTION>
									<? endif; ?>
								<? endforeach; ?>
							</SELECT>
							
							<a class="next button">
								<span class="icon"></span>
								<span class="label"><?=T_('Next School')?></span>
							</a>						
						</div>
					
					</div>

					<!--
					<P>
						<LABEL>
							<?//=T_('Select Institution')?>: 
							<select name="school_id" id="school_id">
								<? //foreach ($schools as $school) : ?>
								
									<? //if ($school->school_id == $school_id) $selected = "selected"; else $selected = ""; ?>
									
									<option value="<?//=$school->school_id;?>" <?//=$selected;?>>
										<?//=$school->school_name;?>
									</option>
									
								<? //endforeach; ?>
							</select>
						</LABEL> 

					</P>	
					-->
					
				<? endif; ?>
				
				<DIV name="students_div" id="students_div">
				</DIV>
				
			</DIV>
		
		</DIV>
		
		<? include('admin_footer.php'); ?>
		
	</BODY>
</HTML>

