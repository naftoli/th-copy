<?php
include("db.php");
include("check_admin_id_2.php");

$next_page = "false";

include ("camps/includes/classes/admin.php");
include ("camps/includes/classes/user.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$parent_name = " <i>" . $row['first'] ." " . $row['last'] . "</i>";
$admin = new admin($row);
$admin->get_children();
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
		<script src="scripts/jquery.placeholder.js"></script>
		
		<style>
		</style>
		
		<script type="text/javascript">
			var admin_id = <?=$admin_id?>;
			
			var children = new Array();
			<?
				for ($cno = 0; $cno < count($admin->children); $cno++) {
					echo "children[" . $cno . "]='" . $admin->children[$cno]->user_code . "';\n";
				}				
			?>
			
			$(function() {
					$('input').placeholder();
					$('.barcode_row').not(':first').hide();
					$('span.add_barcode').click(function(){
						$(this).fadeOut();
						$(this).parent().next().slideDown();
					});
					$("#nav").height($("#content").height());
				});
			
				$(document).ready(function() {	
					// put parent name on top of page
					$('#associated_parent').append("<?=$parent_name?>");
					
					// on load - get all children of parent				
					//show_all_children_of_parents();

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
						return false;
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
				function show_student_information(user){			

					if (user.user_registered == null)
						user_registered_date = "NOT REGISTERED"
					else
						user_registered_date = (user_registered.substring(0,10));
								
					//////////var function_name = "get_user_code";																
					myVar = "<li id='li"+ user.user_id +"' >" +
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
							"</tr>"+
							"</table>"+						 
							"</li>";
					
					$('#list_forms').append(myVar);							
				}
				
				// on input of new bar code: iterate over all non-blank input barcode boxes, call validation and update if valid
				function iterate_children_barcodes_and_get_student_info() {
					var children = $('.lists').find('li input.barcode_input');								
					var count = 0;
					
					$.each(children, function() {
						var this_bar_code = $(this).val();								
						if (this_bar_code.length != 0){
							count ++;
							// perform validation												
							var ret = perform_validation(this_bar_code);	

							// get student information
							if (ret){													
								get_student_based_on_barcode_number(this_bar_code);							
								// clear value on screen
								$(this).val("");
							}
						}					
					}); 
					
					if(count==0){
						alert("No bar code(s) entered");
					}	
				}
				
				// validate bar code entry
				function perform_validation(this_bar_code){
					if (this_bar_code.length != 20){
						alert("Bar code must be 20 characters")
						return false;
					}
					if (isNaN(this_bar_code)){
						alert("Bar code must be numeric")
						return false;
					}
					return true;				
				}
				
				// get student information based on bar code number
				function get_student_based_on_barcode_number(child_bar_code){
					var found = false;
					for (cno = 0; cno < children.length; cno++) {
						var user_code = children[cno];
						if (user_code == child_bar_code) 
							found = true;							
					}
				
					if (found == true) {
						alert("Child already assigned to you.");
					}
					else {
				
						var function_name = "get_user_code";												
						var parameters = [admin_id, child_bar_code.substring(1,20)];  // database actually stores 19 characters of bar code. Strip off first character
						var url = "camps/includes/get_functions.php?function_name=" + function_name + "&parameters=" + parameters;																
						
						$.getJSON(url, function(user) {
						
							if (user == '0'){
								alert("Bar code " + child_bar_code + " not found.");
								return false;
							}
							else if (user == '1'){
								alert("This student is already associated with another parent");
								return false;
							}
							else {							
								//update Admin_Auth table
								result = fn_insert_admin_auth_table(<?=$admin_id?>,user.user_id);														
								
								if (result == true ){
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
	return confirm('Before you continue, please make sure to add all your children by clicking the "+" sign next to the barcode input.\n\(If you haven\'t put in all your children click \'cancel\'\)\nAre you sure that you want to continue?');
}

			
		</script>
		
		<style>
		li{
		   list-style: none;
		}		
		</style>
		<!--Copyright Ariel Shkedi 2007-2010-->	
	</head>

	<body>
	
		<NOSCRIPT>
			<P STYLE="color: red; font-size: larger;">Notice: You have javascript disabled. Some parts of the site will not function without javascript.</P>
		</NOSCRIPT>
		
		<div id="wrapper">
		
			<div id="nav" class="wizard">
			
				<div class="col_title_bg"></div>
				
				<div class="col_title">Menu</div>
				<? $curr = 2; ?>
				<? include("register_parent_menu.php"); ?>
			</div>
			
			<div id="content">
			
				<div class="col_title_bg"></div>
				
				<div class="slider_container">
				
					<div class="slider">
					
						<div class="col_title"></div>
						
						<div class="col_content">
							<h1>Add Children</h1>
	 
							<h2 id='associated_parent'>Children associated with</h2> 
							
							<div class="module" id="module-info">
								<div class="module_content">
									<div class="lists form" id='list_forms' >
										<ul>
										<? for ($cno = 0; $cno < count($admin->children); $cno++) : ?>
											<? $child = $admin->children[$cno]; ?>
																																
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
															<? if ($child->user_registered > 0) : ?>
															Registration date: <?=$child->user_registered;?><br>&nbsp;
															<? else : ?>
															Not Registered.
															<? endif; ?>
														</td>
													</tr>
												</table>
											</li>
							
										<? endfor; ?>
									 </ul>
									</div>
								</div>
							</div>
							
							
							<!--<form action="" method="post" accept-charset="UTF-8" name="login">-->
							
								<h2>Associate children with this account</h2> 
								
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
													<input type="button" value="Add Child/Children" class="button" id='add_child'">
												</li>
											</ul>
											
										</div>
										
									</div>
									
								</div>
								
							<!--</form> -->
							
										<form method="post" action='https://www.mashpia.com/register_parent_3_new.php' onsubmit='return check()'>
											<input type="hidden" name="admin_id" value="<?=$admin_id;?>">
											<input type="submit" value="Continue" class="button" id='continue'>
										</form>									
							
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>	
</html>
