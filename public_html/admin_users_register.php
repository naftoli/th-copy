<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$admin_auth = array('school');
require_once('header.php');
// import functions relating to date conversion
require_once('calendar.php');
// set the UI
$ui_type = 'school';
require_once('admin_ui.php');

// get the admin id from the get request headers or if that fails from the decoded cookie
if (isset($_GET['admin_id'])) {
	$admin_id = $_GET['admin_id'];
} else {
	$admin_id = $admin_user['admin_id'];
}

include("classes/admin.php");
//include("classes/school.php");

// load the correct "admin/user" from the database
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
// load the school id and the schools for given admin
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

$reg = null;
if (isset($_GET['registered'])) {
	$reg = $_GET['registered'];
}

if ($school_id == "")
	$school_id = 0;
	
if (isset($_POST["hidden_school_id"])) {
	$school_id = $_POST["hidden_school_id"];
	
	if (isset($_POST["first"]))
		$first = $_POST["first"];
		
	if (isset($_POST["last"]))
		$last = $_POST["last"];
}
// get the schools the user can access
$schools = array();
if ($admin->auth == "super") {	
	$sql = "SELECT * FROM schools ORDER BY school_name";
	$query = mysql_query($sql);
	while ( $row = mysql_fetch_assoc( $query ) ) {
		$school = new school($row);
		array_push($schools, $school);
	}
} else {
	$sql = "SELECT s.* ";
	$sql = $sql . "FROM admin_auths AS aa ";
	$sql = $sql . "JOIN schools AS s ON (aa.id=s.school_id) ";
	$sql = $sql . "WHERE aa.admin_id=" . $admin->admin_id . " ";
	$sql = $sql . "AND aa.auth='school' ";
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);
	
	if ( $num_rows >= 1 ) {
		while ( $row = mysql_fetch_assoc($query) ) {
			$school = new school($row);
			array_push($schools, $school);
		}
	}
}

// for old registration change code please see archive/admin_users_register/admin_users_register_new.php

// if the GET request has a fee set in it use that fee
if ( isset( $_GET['fee'] ) && $admin_user['auth'] == 'super') $reg_fee = $_GET['fee'];
else $reg_fee = false;
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Soldiers' Registration - Tzivos Hashem Management System</title>
		<link href="admin_styles.css" rel="stylesheet" type="text/css"/>
		<link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
		<link href="/styles/admin/forms.css"  rel="stylesheet" type="text/css"/>
		<link href="/styles/admin/modal.css"  rel="stylesheet" type="text/css"/>
<!--		JQuert Tools (loads Jquery 1.4) -->
		<script src="camps/scripts/jquery.tools.min.js"></script>
<!--		Our scripts (extracted and unit tested) -->
		<script src="js/admin/components/cc_modal.js"></script> <!-- Show the modal for credit cards -->
		<script src="js/utils/cc_validate.js"></script> <!-- Validate Credit cards -->
		<script src="js/utils/money_format.js"></script> <!-- Format money on the page -->
	</head>

	<body>		
		<? include('lang.php'); ?>
		<? include('admin_header.php'); ?>
		<div class="ui_<?=$ui_type?> <?=$align_start?>">
			<div class="body">
				<div class="sub_menu">		
					<?php if (isset($message) && $message != "") { ?>
						<h2><?=$message?></h2>
                    <?php } ?>
				</div>
				<h1><?=T_('Base Management')?></h1>
				
                <?php if ( count($schools) > 1 ) { ?>	
				    <div class="infobox2 marking_list clearfix">
						<div class="school_list select_box">
							<a class="prev button">
								<span class="icon"></span>
								<span class="label"><?=T_('Previous School')?></span>
							</a>
						
							<select name="school_id" id="school_id">
								<? foreach ($schools as $school) { ?>
									<? if ($school->school_id == $school_id) { ?>
									<option selected value="<?=$school->school_id;?>"><?=$school->school_name;?></option>
                                    <? } else { ?>
									<option value="<?=$school->school_id;?>"><?=$school->school_name;?></option>
									<? } ?>
                                <? } ?>
							</select>
							
							<a class="next button">
								<span class="icon"></span>
								<span class="label"><?=T_('Next School')?></span>
							</a>						
						</div>
					</div>
                <? } // end if we have a class selector to show ?>
				
				<div name="students_div" id="students_div">
					<div class="loader">Loading...</div>
				</div>
<!--    The modal for editing the users Credit card -->
				<div class="modal" id="cc_modal">
					<div class="modal-content">
						<h1 style="margin-bottom: 0px;">Update Credit Card<span class="close" id="update_cc_exit">&times;</span></h1>
						<h2 id="cc_modal_error"></h2>
						<form id="update_cc" action="/ajax/authorize/update_card.php" method="post">						
							<div class="input_group input_half">
								<label><?=T_('CC Number')?><br />
									<input TYPE="text" NAME="cc_number" id="cc_number" VALUE="" MAXLENGTH="19" required/>
									<span class="cc_form_error" id="cc_number_errors"></span>
								</label>
							</div><div class="input_group input_quarter">
								<label><?=T_('Expires MM/YY')?><br />
									<input TYPE="text" NAME="cc_exp" id="cc_exp" VALUE="" MAXLENGTH="5" required/>
									<span class="cc_form_error" id="cc_exp_errors"></span>
								</label>
							</div><div class="input_group input_quarter">
								<label><?=T_('CVV')?><br />
									<input TYPE="text" NAME="cc_cvv" id="cc_cvv" VALUE="" MAXLENGTH="4"/>
									<span class="cc_form_error" id="cc_cvv_errors"></span>
								</label>
							</div>
							<div class="input_group input_full">
								<label>
									<?=T_('Billing Address')?><br />
									<input type="text" name="billing_address" id="billing_address" value="" maxlength=255 required/><br/>
									<span class="cc_form_error" id="billing_address_errors"></span>
								</label><br />
							</div><div class="input_group input_third">
								<label>
									<?=T_('Billing City')?><br />
									<input type="text" name="billing_city" id="billing_city" value="" maxlength=255 required/>
									<span class="cc_form_error" id="billing_city_errors"></span>
								</label><br />
							</div><div class="input_group input_third">
								<label>
									<?=T_('Billing State/Province')?><br />
									<input type="text" name="billing_state" id="billing_state" value="" maxlength=255 required/>
									<span class="cc_form_error" id="billing_state_errors"></span>
								</label><br />
							</div><div class="input_group input_third">
								<label>
									<?=T_('Billing Zip/Postal code')?><br />
									<input type="text" name="billing_postal" id="billing_postal" value="" maxlength=10 required/>
									<span class="cc_form_error" id="billing_postal_errors"></span>
								</label><br />
							</div>
							<div class="modal-footer">
								<input type="submit" value="Update" id="update_cc_submit"/>
								<input type="button" value="Cancel" id="update_cc_cancel"/>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		
		<? include('admin_footer.php'); ?>
		<!--		Page is mostly just Ajax calls and written in JavaScript.-->
		<script type="text/javascript">
			$(document).ready(function() { // once the page is ready, set up and define all the functions so that they are hidden from the user
				
				var user_ids = "";	 // the user id list that is being registered			
				var first = "<?=$first;?>"; // the first name set in the post headers
				var last = "<?=$last;?>"; // the last name. Also set in the post headers
				var is_loaded = false;
				var reg_fee = 0; // the registration fee
				var sid = <?=$school_id?>; // the id of the school that is registering students
				
				get_students(); // make an ajax call to load all the students
			
				// next and previous school buttons
				$('.marking_list div a.next').click(function(){
					$(this).siblings('select').find('option:selected').next().attr('selected','selected').parent().change();
				});
				$('.marking_list div a.prev').click(function(){
					$(this).siblings('select').find('option:selected').prev().attr('selected','selected').parent().change();
				});
				// style the dropdown
				$(".school_list select").sSelect();
				// "GO" search button after form for entering names and school
				$("#search_button").live('click', get_students );
				// redirect the user to the edit page for the school should he press the review button
				$('#school_review').live('click', function() {	
					window.location = "/admin_school2.php?school_id=" + sid + "&action=edit";	
				});
                // show the modal when the button is pressed
				$('#school_cc_review').live('click', function() { cc_modal.show(); });
			
				// ****************************** REGISTARTION FEES ****************************** //
				
				// ********************* MASTER REGISTRATION SWITCH ************************ //
				$("#toggle_registration_fee").live('click', function() { // toggle all the registration fees.
					// default state is false
					var checked = false;
					// set to true if master is checked
					if ($(this).is(":checked")) {
						checked = true;
					}
					// find each registration toogle button
					$.each($("#students_table").find("input[name=registration_fee]"), function() { 	
						// that is visiable to the user
						if ($(this).attr('type') != 'hidden') {
						
							if (checked === true){ // if the master switch is set to on
								$(this).parents("tr").attr("id", "registered"); // set it to registered
							} else { // if the master switch is set to off
								$(this).parents("tr").attr("id", "unregistered"); // set it to unregistered
							}
							// calculate the new totals
							if (!$(this).is(':checked') && checked === true) { // if it is checked and the master checked status is true
								$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) + reg_fee));
								$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), reg_fee, "add"));
							}
							
							if ($(this).is(':checked') && checked === false) {
								$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) - reg_fee));
								$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), reg_fee, "subtract"));
							}
							// set the attrabute to checked.
							$(this).attr("checked", checked);
						} // end if not hidded
					}); // end the $.each
				}); // end master switch event handler
				
				// ********************* SINGLE REGISTRATION SWITCH ************************ //
				$("#registration_fee").live('click', function() { // onclick handler for each single registration line
					if ($(this).is(":checked")) {
						$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) + reg_fee));
						$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), reg_fee, "add"));
					} else {
						$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) - reg_fee));
						$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), reg_fee, "subtract"));
					}
				});
				
				// ********** MISC ********** //
				// if the class id box changess reload the students
				$('#class_id').live( "change", get_students );
				
				// if the school id changes, change the review school link and refresh the students
				$("#school_id").change(function() {
					$("#school_review").attr("href", "/admin_school2.php?school_id=" + $(this).val() + "&action=edit");
					get_students();
				});
				
				// ************* MODAL **************** //
				//$(".modal-content").fadeOut(); // enable the fad in effect right away
				
				// clicking on the cancel and exit buttons should close the modal
				$("#update_cc_cancel").add("#update_cc_exit").click(function(event) {
					event.preventDefault();
					cc_modal.hide();
				});
				// run submit cc modal when it is done
				$("#update_cc").submit(cc_modal.submit); // I belive the submit just runs if this funciton is null. Making debugging a pain in the rear end
				
				// ************** MODAL VALIDATIONS ***************
				cc_validate.setUpModalValidaitons(); // see js/admin/admin_cc_validate.js for function
				
				
				// *********** REGISTER STUDENTS *********** //
				// button is located at the bottom of the page
				$('#register_students').live('click', function() {	
					// reset the user id's to "";
					user_ids = "";
					
					// ***** Get the user ids of each child that is being registered ***** //
					$.each($("#students_table").find("td > div.checkboxes > input:checked"), function() { 		
                        // if the student total column has a value in it add its value to the studnet ids list
                        user_ids += ( $(this).parent()[0].id + ":" );
					});
					// ***** Get the user ids of each child that is being registered ***** //
					
					if (user_ids !== "") { // if we have userid's
						user_ids = user_ids.substr(0, user_ids.length - 1); // // remove the last : from the generated text
                    } else { // no students where selected
						alert("You have not made any selection!"); // notify the user that they have not made a selection
						return false;
					}
					
					if ($('#cc_card_check').attr('checked')) { // make sure the CC aprooval section is ticked before performing the credit card transaction
						var success = preform_authorize_validation();
					} else { // if it is not selected
						document.getElementById("cc_card_check").focus(); // focus on the cc_card check box
						alert("Please check Credit Card Approval button to authorize purchase on school credit card."); // and post an alert for the user
					}
										
				});
				
				// *********** CHARGE CREDIT CARD *********** //
				function preform_authorize_validation() {
					// cast the json into a post params list
					var dataToSend = $.param({
						school_id: sid,
						description: "child registration->" + user_ids,
						amount: $('#grand_total_id').html(),
						customer_profile_id: $("#authorize_customer_profile_id").val(),
						payment_profile_id: $("#authorize_payment_profile_id").val()
					});
                    
                    if ( $('#grand_total_id').html() != '0.00' ) {
                        $.post(
                            "/ajax/authorize/charge_card.php",
                            dataToSend,
                            function(data) { // on a sucessfull hit this function is called
                                data = JSON.parse(data); // parse the result to json
                                
                                // add || sid == 82 to allow the test account access
                                
                                if(data.success) { // if the charge was sucessfull{
                                    console.log(data.response);// log out the transaction response
                                    registerStudents(); // register the students
                                    // generate the response and show it in the correct box
                                    var response  = data.response.transactionResponse.messages[0]; // load the correct section of the response
                                    var message = response.description + " (" + response.code + ")"; // format the text
                                    $('#credit_card_approval_results').html(message + "<br>") ; // show the user the response					
                                    $('#box_cc_auth').css("display", "block");
                                } else { // alert the user that the charge has failed.
                                    //alert(data.response); // show the failure in an alert for now
                                    cc_modal.setError(data.response);
                                    cc_modal.show();
                                    $('#credit_card_approval_results').html(data.response + "<br>") ; // show the user the error					
                                    $('#box_cc_auth').css("display", "block");
                                }
                            }
                        );
                    } else {
                        registerStudents(); // register the students
                    }
				}
				
				// *********** REGISTER STUDENTS *********** //
				function registerStudents() {
					var parameters = ""; // the paramaters for the request
					$.each($("#students_table").find("tr[name=student_row]"), function() { // for each student in the table
						var user_id = $(this).attr("data"); // get the user id
						var amount = $(this).find("#student_total").html(); // set the amount to the total
                        var selected = $(this).find('div.checkboxes input')[0].checked;
                        //var optional = $(this).find("#optional_fee").val(); // get the optional fee. removed.
						var registered = $(this).find("#user_registered").html().trim(); // check if student was registered
						if (registered == "Not Yet Registered"){ // set the correct formatting for the text
							registered = "unregistered";
						} else {
							registered = "registered";
						}
						//alert(optional); // show the optional amount
						if (selected){ // if students where registered. set paramaters to the following <paramaters><user_id>;<amount>;<registered>:
							parameters = parameters + user_id + ";" + amount + ";" + registered + ":";					
						}
					});
					// add the grand total and the user_ids to the paramaters
					parameters = parameters + $("#grand_total_id").html() + ";" + sid;								
					var url = "add_functions.php?function_name=register_students&parameters=" + parameters; // send the paramaters to the correct file
                    // get json back from the server.
					$.getJSON(url, function(success) {
						if (success) { // if something was returned.
							alert(success); // alert the user
					    }
					});
					
					//update students that are being registered for user tracks and birthday missions
					$.each($("#students_table").find("tr[name=student_row]"), function() {  
						var user_id = $(this).attr("data");
						var registered = $(this).find("#user_registered").html().trim();
						if (registered === "") {
							//$.post('ajax/enrollIntoCampaigns.php', { id : user_id }); // enroll in the campaings
							$.post('ajax/setupBirthday.php', { id : user_id });
						}
					});
					
					alert("Students have been registered/updated.");
					
					get_students(); // refresh the students
				}
				
				// ********** LOAD STUDENTS ********** //
				function get_students() {
					// generate the url to load the students from
                    var url = "/register_school_students.php?school_id=<?=$admin->school_id;?>";
					if ($("#school_id").val() > 0) {
						url = "/register_school_students.php?school_id=" + $("#school_id").val();
					}
					// add the registered flag
					<? if (!empty($reg)) echo "url += '&registered=1'"; ?>
					
					<?php if ( $reg_fee !== false ) echo "url += \"&fee=$reg_fee\""; ?>
					
					// add the class id, first and last names if the page has been loaded once
					if (is_loaded === true){
						if ($("#class_id").val() > 0) {
							url = url + "&class_id=" + $("#class_id").val();
						}
						if ($("#first").val()) {
							url = url + "&first=" + $("#first").val();
						}
						if ($("#last").val()) {
							url = url + "&last=" + $("#last").val();
						}
                    }
                    $("#students_div").html('<div class="loader"></div>');
					// get the data and set the html
					$.get(url, function(data) {
                        $("#students_div").html(data);
                        reg_fee = parseInt($('#reg_fee').val(), 10);
					});
				}
				
				is_loaded = true; // all the items, functions and page are loaded. change refresh behavior accordingly
			});
		</script>
	</body>
</html>
