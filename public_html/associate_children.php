<?php
$admin_auth = array('user');
$ui_type = 'child';
require('header.php');
include("check_admin_id.php");

include ("camps/includes/classes/admin.php");
include ("camps/includes/classes/user.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$parent_name = " <i>" . $row['first'] ." " . $row['last'] . "</i>";
$admin = new \camps\classes\admin($row);
$admin->get_children();

$regChildren = array();
$nonRegChildren = array();
for ($cno = 0; $cno < count($admin->children); $cno++) {
	if ($admin->children[$cno]->user_registered > 0) {
		$regChildren[] = $admin->children[$cno];
	} else {
		$nonRegChildren[] = $admin->children[$cno];
	}
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
		
		<script type="text/javascript">
			var admin_id = <?=$admin_id?>;
			
			var children = new Array();
			<?
			for ($cno = 0; $cno < count($admin->children); $cno++) {
				echo "children[" . $cno . "]='" . $admin->children[$cno]->user_code . "';\n";
			}				
			?>
			
			$(function() {
					$('.barcode_row').not(':first').hide();
					$('span.add_barcode').click(function(){
						$(this).fadeOut();
						$(this).parent().next().slideDown();
					});
					$("#nav").height($("#content").height());
				});
			
				$(document).ready(function() {						
					// put listener on 'add child' button 
					$('#add_child').click(function() {				
						iterate_children_barcodes_and_get_student_info();
						return false;
					});
					
					// put listener on 'delete child' button 
					$('.delete_child').live('click', function() {
						parameters = [$(this).attr('value')]
						if (confirm_child_delete()){
							delete_parent_child_relationship(parameters);
							myLi = "#li" + $(this).attr('value');
							$(myLi).remove();	
						}
						window.location.href = 'associate_children.php';
					});
					
					$(".reg").each( function() {
						var user = $(this).attr('id');
						var year = 5776;
						var td = this;
						$.getJSON("camps/includes/get_functions.php?function_name=getYearlyReg&year=" + year + "&user=" + user, function(success) {
							if (success) {
								$(td).text("Registered for 5776.");
							}
						});
					});
				});		
				
				// confirm child delete
				function confirm_child_delete() {
					 var response = confirm('Are you sure you want to remove child entry from this parent?');
					 if (response){
						return true;
					 }
					 else{
						return false;
					 }
				}
								
				// on load, show all children of parents
				function show_all_children_of_parents() {
					var function_name = "get_children_of_admin_id"; 
					var parameters = admin_id;
					var url = "camps/includes/get_functions.php?function_name=" + function_name + "&parameters=" + parameters;																				
					$.getJSON(url, function(children) {	
						
						if (children == '1')
							alert("Student corresponding to bar code " + child_bar_code + " not found.");
						if (children == '0')
							alert("Bar code may already assigned to another parent.");
						else {						
							  
							for (cno = 0; cno < children.length; cno++) {
								var child = children[cno];
								show_student_information(child);   
							}
						}
					});																														
				}
				
				// show a single set of student information - used on both initial load and add student
				function show_student_information(user)
				{
					if (user.user_registered == null)
						user_registered_date = "NOT REGISTERED"
					else
						user_registered_date = (user.user_registered.substring(0,10));
					
					var myVar = "<li id='li"+ user.user_id +"' >" +
								"<span class='photo'><img alt='' width='32' height='32'  src='/file_view.php?id=" + user.user_photo_id + "'></span>" +
								"<table width=90%>"+
								"<tr>"+
								"<td width=40%></td>" +
								"<td width=30%></td>" +	 
								"<td width=20%></td>"+
								"</tr>" +
								"<tr>" +
								"<td class='label large'>"  + user.first + " " + user.last + "</td>"+
								//"<td class='role'>Grade " + class_grade + " - " +  class_teacher + "</td>"+
								"<td align='right'><img src='images/chk_off.png' class='delete_child' value='"+ user.user_id +"' alt='"+ user.user_id +"' width='12' height='12' /></td>" +
								"</tr>"+
								"<tr>"+
								//"<td class='role'>" + school_name + "</td>"+
								"<td class='role'>Registration date: " + user_registered_date + "<br>&nbsp;</td>"+
								"</tr></table></li>";
					$('#list_forms').append(myVar);
				}
				
				// on input of new bar code: iterate over all non-blank input barcode boxes, call validation and update if valid
				function iterate_children_barcodes_and_get_student_info() {
					var children = $('.lists').find('li input.barcode_input');								
					var count = 0;
					
					$.each(children, function() {
					
						var bar_code = $(this).val();
						
						if (bar_code.length != 0)
						{
							count++;
							// perform validation												
							var valid_barcode = perform_validation(bar_code);	

							// get student information
							if (valid_barcode)
							{
								get_student_based_on_barcode_number(bar_code);							
								// clear value on screen
								$(this).val("");
							}
						}
					}); 
					
					if (count==0)
						alert("No bar code(s) entered");
				}
				
				// validate bar code entry
				function perform_validation(this_bar_code)
				{
					if (this_bar_code.length != 20)
					{
						alert("Bar code must be 20 characters")
						return false;
					}
					if (isNaN(this_bar_code))
					{
						alert("Bar code must be numeric")
						return false;
					}
					return true;				
				}
				
				// get student information based on bar code number
				function get_student_based_on_barcode_number(child_bar_code)
				{
					var found = false;
					
					for (cno = 0; cno < children.length; cno++) 
					{
						var user_code = children[cno];
						if (user_code == child_bar_code) 
							found = true;							
					}
				
					if (found == true) 
					{
						alert("Child already assigned to you.");
					}
					else 
					{				
						var function_name = "get_user_code";												
						var parameters = [admin_id, child_bar_code.substring(1,20)];  // database actually stores 19 characters of bar code. Strip off first character
						var url = "camps/includes/get_functions.php?function_name=" + function_name + "&parameters=" + parameters;																
						
						$.getJSON(url, function(user) {
						
							if (user == '0')
							{
								alert("Bar code " + child_bar_code + " not found.");
								return false;
							}
							else if (user == '1')
							{
								alert("This student is already associated with another parent");
								return false;
							}
							else 
							{
								//update Admin_Auth table
								updated = fn_insert_admin_auth_table(<?=$admin_id?>, user.user_id);														
								
								if (updated == true)
								{
									show_student_information(user);
									return true;	
								}	
								return false;
							}
						});	
					}					
				}
					
				// populate Admin-Auths table with parent-student relationship 
				function fn_insert_admin_auth_table(admin_id,user_id) {
					var function_name = "insert_admin_auth_table"; 
					var parameters = [admin_id, user_id]
					var url = "camps/includes/add_functions.php?function_name=" + function_name + "&parameters=" + parameters;

					var result = true; 
				   $.ajax({ 
						 async: false, 
						 url: url, 
						 dataType: "json", 
						 success: function(user) {
							if (user == false) {					 
								alert("Insert process failed. Invalid bar code ID or stuadent already assigned.");
								result = false; 
							} else {
								document.location.href = "admin.php";
							}
						}, 
					});
					return result; 
				}
		
			// delete parent-child_relationship
			function delete_parent_child_relationship(parameters)
			{
				var function_name = "delete_parent_child_relationship"; 					
					var url = "camps/includes/delete_functions.php?function_name=" + function_name + "&parameters=" + parameters;					
					$.ajax({ 
						 async: false, 
						 url: url, 
						 dataType: "json", 
						 success: function(user) {
							if (user == false) {					 
						   }
						}, 
					});
					return 0; 
			}

		//confirm no more kids to add
		function check() {
			return confirm('Please confirm that you added all your children to your account.');
		}
			
		</script>
		
		<style>
		li{
		   list-style: none;
		}		
		</style>
	</head>

	<body>
		<? include("admin_header.php"); ?>
		
		<div class="body">

			<h1>Associate Children with your account</h1>
		
			<NOSCRIPT>
				<P STYLE="color: red; font-size: larger;">Notice: You have javascript disabled. Some parts of the site will not function without javascript.</P>
			</NOSCRIPT>
			
			<div class="content">
				
				<? if ( count( $regChildren ) > 0 ) : ?>												 
				<h2 id='associated_parent'>Registered Children associated with <?=$parent_name?></h2> 
				<div class="module" id="module-info">
					<div class="module_content">
						<div class="lists form" id='list_forms' >
							<ul>
							<? for ($cno = 0; $cno < count($regChildren); $cno++) : ?>
								<? $child = $regChildren[$cno]; ?>
																													
								<li>
									<span class='photo'>
										<img width="32" height="32"  src="/file_view.php?id=<?=$child->user_photo_id;?>">
									</span>
									<table width="90%">
										<tr>
											<td width="40%"></td>
											<td width="30"></td>
											<td width="20"></td>
										</tr>
										<tr>
											<td class='label large'>
												<?=$child->first;?> <?=$child->last;?>
											</td>
											<td align='right'>
												<img src='images/chk_off.png' class='delete_child' value="<?=$child->user_id;?>" width="12" height="12" />
											</td>
										</tr>
										<tr>
											<td class='role'>
												Registration date: <?=$child->user_registered;?><br>&nbsp;
											</td>
										</tr>
									</table>
								</li>
								
							<? endfor; ?>
						 </ul>
						</div>
					</div>
				</div>
				<? endif; ?>
				
				<? if ( count( $nonRegChildren ) > 0 ) : ?>
				
				<h2 id='associated_parent'>Not Yet Registered Children associated with <?=$parent_name?></h2>
				<div class="module" id="module-info">
					<div class="module_content">
						<div class="lists form" id='list_forms' >
							<ul>
							<? for ($cno = 0; $cno < count($nonRegChildren); $cno++) : ?>
								<? $child = $nonRegChildren[$cno]; ?>
																													
								<li>
									<span class='photo'>
										<img width="32" height="32"  src="/file_view.php?id=<?=$child->user_photo_id;?>">
									</span>
									<table width="90%">
										<tr>
											<td width="40%"></td>
											<td width="30"></td>
											<td width="20"></td>
										</tr>
										<tr>
											<td class='label large'>
												<?=$child->first;?> <?=$child->last;?>
											</td>
											<td align='right'>
												<img src='images/chk_off.png' class='delete_child' value="<?=$child->user_id;?>" width="12" height="12" />
											</td>
										</tr>
										<tr>
											<td class='role'>
												Not Currently Registered.
											</td>
										</tr>
										<tr>
											<td class="reg role" id="<?=$child->user_id?>"></td>
										</tr>
									</table>
								</li>
				
							<? endfor; ?>
						 </ul>
						</div>
					</div>
				</div>
				
				<? endif; ?>
											
				<h2>Associate (additional) children with this account</h2>
				
				<div class='infobox'>
					To associate a child:<br />
					1. Enter the 20 digit bar-code number<br />
					2. Click the "+" sign to add more children<br />
					3. Click "Add Child(ren)" once all children barcodes have been entered.
					<br /><br />
					Tip:<br />
					1. Make sure there are no extra spaces when copying and pasting the bar-code number<br />
					2. If the bar-code is not working, try manually typing the bar-code (do not copy and paste)<br />
					</div>
			
				<div class="module" id="module-info">
				
					<div class="module_content">
					
						<div class="lists form">
						
							<ul>
								<li>
									<span class="box">
										<p class="input">Please enter the 20 digit barcode number for each child.</p>
									</span>
								</li>
								
								<li id="barcode-1" class="barcode_row"  >
									<span class="label"><label for="barcode-1">Barcode</label></span>
									<span class="input">
										<input class='barcode_input' name="barcode-1" type="text" />
									</span>
									<span class="add_barcode"></span>
								</li>
								
								<li id="barcode-2" class="barcode_row"  >
									<span class="label"><label for="barcode-2">Barcode</label></span>
									<span class="input"><input  class='barcode_input' name="barcode-2" type="text" /></span>
									<span class="add_barcode"></span>
								</li>
								
								<li id="barcode-3" class="barcode_row"  >
									<span class="label"><label for="barcode-3">Barcode</label></span>
									<span class="input"><input  class='barcode_input' name="barcode-3" type="text" /></span>
									<span class="add_barcode"></span>
								</li>

								<li id="barcode-4" class="barcode_row"  >
									<span class="label"><label for="barcode-4">Barcode</label></span>
									<span class="input"><input  class='barcode_input' name="barcode-4" type="text" /></span>
									<span class="add_barcode"></span>
								</li>
								
								<li id="barcode-5" class="barcode_row"  >
									<span class="label"><label for="barcode-5">Barcode</label></span>
									<span class="input"><input  class='barcode_input' name="barcode-5" type="text" /></span>
									<span class="add_barcode"></span>
								</li>
								
								<li id="barcode-6" class="barcode_row"  >
									<span class="label"><label for="barcode-6">Barcode</label></span>
									<span class="input"><input  class='barcode_input' name="barcode-6" type="text" /></span>
									<span class="add_barcode"></span>
								</li>
								
								<li id="barcode-7" class="barcode_row"  >
									<span class="label"><label for="barcode-7">Barcode</label></span>
									<span class="input"><input  class='barcode_input' name="barcode-7" type="text" /></span>
									<span class="add_barcode"></span>
								</li>
								
								<li id="barcode-8" class="barcode_row"  >
									<span class="label"><label for="barcode-8">Barcode</label></span>
									<span class="input"><input  class='barcode_input' name="barcode-8" type="text" /></span>
									<span class="add_barcode"></span>
								</li>
								
								<li id="barcode-9" class="barcode_row"  >
									<span class="label"><label for="barcode-9">Barcode</label></span>
									<span class="input"><input  class='barcode_input' name="barcode-9" type="text" /></span>
									<span class="add_barcode"></span>
								</li>
								
								<li id="barcode-10" class="barcode_row"  >
									<span class="label">
										<label for="barcode-10">Barcode</label>
									</span>
									
									<span class="input">
										<input  class='barcode_input' name="barcode-10" type="text" />
									</span>
									
									<span class="add_barcode">
									</span>
								</li>																								
								
								<li>
									<!-- <input type="button" value="Add Child/Children" class="button" id='add_child' onclick="add_child();">  -->
									<input type="button" value="Add Child(ren)" class="button" id='add_child'">
								</li>
							</ul>
							
						</div>
						
					</div>
					
				</div>						

			</div>
			
		</div>

	</body>	
</html>
