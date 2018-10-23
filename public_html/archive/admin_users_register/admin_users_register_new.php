<?php
session_start();

// Set the page to under construction if the school is not in beta
//if ($_GET['school_id'] != 82)
	//header("Location: under_construction.php");
	
// Schools and super users can access page.
$admin_auth = array('school');
// get the cookies and authorize user
require_once('header.php');
// import functions relating to date conversion
require_once('calendar.php');
// set the UI
$ui_type = 'school';
require_once('admin_ui.php');
//assure_id_school('school_id');

// get the admin id from the get request headers or if that fails from the decoded cookie
if (isset($_GET['admin_id'])) {
	$admin_id = $_GET['admin_id'];
} else {
	$admin_id = $admin_user['admin_id'];
}
// load the admin class
include("classes/admin.php");
//include("classes/school.php");

// load the correct "admin/user" from the database
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
// and map him to the orm model
$admin = new admin($row);
// load the school id and the schools for given admin
$admin->get_school_id(); 
$admin->get_schools();

// some  old debugging
//print_r($admin);

// I do not understand this code very well, but it seems that it allows a user to see any school they want by messing with GET paramaters
// - Menachem Hornbacher

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
	
if (isset($_POST["hidden_school_id"]))
{
	$school_id = $_POST["hidden_school_id"];
	
	if (isset($_POST["first"]))
		$first = $_POST["first"];
		
	if (isset($_POST["last"]))
		$last = $_POST["last"];
}
// get the schools the user can access
$schools = array();
if ($admin->auth == "super")
{	
	$sql = "SELECT * FROM schools ORDER BY school_name";
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) 
	{
		$school = new \classes\school($row);
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
			$school = new \classes\school($row);
			array_push($schools, $school);
		}
	}
}

// seems that this code was used to handle a increase in registration prices

//echo $school_id;
/*
$s = "select year from school_add_ons group by year desc limit 1";
$r = mysql_query($s);
$y = mysql_fetch_row($r);
$year = $y[0];
//get id of first add on
$s = "select school_add_on_id from school_add_ons where year = $year limit 1";
$r = mysql_query($s);
$id = mysql_fetch_row($r);
$add_on_start = $id[0];
$add_on_counter = $add_on_start;

$add_ons = array();
$sql = "select * from school_add_ons where year = " . $year;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$add_ons[$add_on_counter++] = $row;
}
//get number of last add on
$add_on_end = ($add_on_start + count($add_ons));
*/
/*
//set registration fees
if (in_array($school_id, array(79, 82, 177)))
    $reg_fee = 12;
else 
    $reg_fee = 50;
 * 
 */
//require_once 'fees.php';
/*
if ($school_id == 61) {
	$reg_fee = 40;
}

$australian = array(112,66,55,110);
if (in_array( $school_id, $australian ) && unixtojd() < 2457087) {
	$reg_fee = 40;
}
*/
/*
$reg_fee = 45;
if (unixtojd() >= 2457655.5) $reg_fee = 55;

//$tuitionSchools = array(30,7,89,45,50,60,63,39,19,42,9,255,84,33,110,265);
include_once 'mobile/reg/ajax/regFeeSchools.php';
if (in_array($school_id, $tuitionSchools)) $reg_fee = 40;
else {
	if (unixtojd() < 2457665) {
		if (in_array($school_id, $fortyFive)) $reg_fee = 45;
		else if (in_array($school_id, $fifty)) $reg_fee = 50;
	}
}

if (in_array($school_id, $australia) && unixtojd() <= 2457813) $reg_fee = 40;
/*
$chaiElul = 2457268;
$jd = unixtojd();
switch ($school_id) {
	case 61: 
		if ($jd > $chaiElul) 
			$reg_fee = 55;
		else 
			$reg_fee = 45;
		break;
	case 5:
	case 42:
	case 63:
	case 263:
		if ($jd > 2457269) 
			$reg_fee = 50;
		break;
	case 3:
	case 49:
	case 81:
	case 185:
	case 192:
		if ($jd > 2457273) 
			$reg_fee = 50;
		break;
	case 7:
	case 9:
	case 30:
	case 45:
	case 60:
	case 89:
		if ($jd > 2457274) 
			$reg_fee = 50;
		break;
	case 2:
		if ($jd > 2457275) 
			$reg_fee = 50;
		break;
	case 58:
	case 106:
	case 194:
		if ($jd > 2457276) 
			$reg_fee = 50;
		break;
	case 89:
	case 176:
		if ($jd > 2457277) 
			$reg_fee = 50;
		break;
	case 4:
	case 84:
	case 21:
		if ($jd > 2457278) 
			$reg_fee = 50;
		break;
	case 162: 
		if ($jd > 2457304) 
			$reg_fee = 50;
		break;
	case 54:
		if ($jd > 2457309) 
			$reg_fee = 50;
		break;
	case 80:
		if ($jd > 2457320)
			$reg_fee = 50;
		break;
	case 55:
		$reg_fee = 40;
		if ($jd > 2457443)
			$reg_fee = 50;
		break;
	default: 
		if ($jd > $chaiElul) 
			$reg_fee = 50;
		break;
}
*/
// We moved directories, later imports please beware
chdir('mobile/reg/ajax');
require_once 'regFeeSchools.php';
$reg_fee = $userFee;
if (in_array($school_id, $tuitionSchools) || in_array($school_id, $tuitionSchoolsNoPay)) $reg_fee = 45;
if ($reg_fee == 55 && unixtojd() < 2458018 && in_array($school_id,  array_keys($extended))) {
	$reg_fee = $extended[$school_id];
	if ($reg_fee == 0) {
		$reg_fee = 45;
	}
}
// myshliach is always 45
if ($school_id == 61) $reg_fee = 45;

if (isset($_GET['fee'])) $reg_fee = $_GET['fee'];

$h_school = false;    
//if school flagged as hebrew school set fee to 10
if ( $admin->school_id > 0 ) {
    $sql = "select inst_id from schools where school_id = " . $admin->school_id;
    $result = mysql_query( $sql );
    $row = mysql_fetch_assoc( $result );
    $inst_id = $row['inst_id'];
    if ( $inst_id == 4 ) {
        //$reg_fee = 6;
        $h_school = true;
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
				var reg_fee = <?=$reg_fee;?>;
				var sid = <?=$school_id?>;
				
				get_students();
			
				// next and previous school buttons
				$('.marking_list div a.next').click(function(){
					$(this).siblings('select').find('option:selected').next().attr('selected','selected').parent().change();
				});
				
				$('.marking_list div a.prev').click(function(){
					$(this).siblings('select').find('option:selected').prev().attr('selected','selected').parent().change();
				});
				
				// reload students when a school is selected from the dropdown
				$(".school_list select").sSelect().change(function () {
					get_students(); // ajax the students in
				});
				
				// "GO" search button after form for entering names and school
				$("#search_button").live('click', function() {
					get_students(); // ajax the new list of students
				});	

				// *********** REGISTER STUDENTS *********** //
				// button is located at the bottom of the page
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
					else {
						alert("You have not made any selection!");
						return false;
					}
					
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
					var email = '<?=$admin->admin_email?>';
					
					var dataToSend =  			
						"cc_first_name=" +  $('#cc_first').val() +
						"&cc_amount=" 	 +  $('#grand_total_id').html() +
						"&cc_last_name=" +  $('#cc_last').val() +
						"&cc_address="   +  "na" +
						"&cc_state="  	 +  "na" +
						"&cc_zip="  	 +  "na" +
						"&cc_description=child registration->"	+  user_ids + 
						"&ccnum="  		 +  $('#cc_number').val() +
						"&ccexp="  		 +  $('#cc_exp').val() +
						"&cccvv="  		 +  $('#cc_cvv').val() + 
						"&school_id="    +  sid + 
						"&email="		 + 	email;
																			    
				    /*
					if (sid == 82) {
						window.open('register_authorize_net_test.php?' + dataToSend);
						return;
					}
					*/				
					
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
							
							//allow test school (#82) to register without using cc
							if ((success == "1") || (1 == (sid == 82 ? 1 : 2)))
							//if (true)
							{
							
								var parameters = "";
								$.each($("#students_table").find("tr[name=student_row]"), function() { 	
								
									var user_id = $(this).attr("data");
									var amount = $(this).find("#student_total").html();
									//var optional = $(this).find("#optional_fee").val();
									var registered = $(this).find("#user_registered").html().trim();
									if (registered == "Not Yet Registered")
										registered = "unregistered";
									else 
										registered = "registered";
									
									//alert(optional);
									if (amount > 0)
									{
										parameters = parameters + user_id + ";" + amount + ";" + registered + ":";					
									}
									
								});
								
								//parameters = parameters.substr(0, parameters.length - 1);
								parameters = parameters + $("#grand_total_id").html() + ";" + sid;								
								var url = "add_functions.php?function_name=register_students&parameters=" + parameters;
								/*
								var admin = <?=$admin_id?>;
								if (admin == 9601) {
									alert(url);
									return;
								}
								*/
								$.getJSON(url, function(success) {
									if (success) {
										alert(success);
										//Console.log(success);
									}
								});
								
								//update students that are being registered for user tracks and birthday missions
								$.each($("#students_table").find("tr[name=student_row]"), function() {  
                                    var user_id = $(this).attr("data");
                                    var registered = $(this).find("#user_registered").html().trim();
                                    if (registered == "") {
								        //$.post('ajax/enrollIntoCampaigns.php', { id : user_id });
										$.post('ajax/setupBirthday.php', { id : user_id });
								    }
								});
								
								alert("Students have been registered/updated.");
								
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
					window.location = "/admin_school_new.php?school_id=" + sid + "&action=edit";	
				});
			
				// ********** REGISTARTION FEES ********** //
				$("#toggle_registration_fee").live('click', function() {
								
					if ($(this).is(":checked"))
						var checked = true;
					else
						var checked = false;
						
					/*
					if (checked == false)
					{
						<? for ($i = $add_on_start; $i < $add_on_end; $i++) { ?>
						
						if ($("#toggle_add_on_<?=$i;?>").is(":checked"))
						{
							$("#toggle_add_on_<?=$i;?>").attr("checked", false);
							/*
							$("#toggle_add_on_<?=$i;?>").click();
							$("#toggle_add_on_<?=$i;?>").attr("checked", false);
							
						}
						
						if ($("#toggle_add_on_2").is(":checked"))
						{
							$("#toggle_add_on_2").attr("checked", false);
							$("#toggle_add_on_2").click()							
							$("#toggle_add_on_2").attr("checked", false);							
						}
						
						<? } ?>
					}
					*/
					
					$.each($("#students_table").find("input[name=registration_fee]"), function() { 	
						
						if ($(this).attr('type') != 'hidden') {
						
							if (checked == true)
								$(this).parents("tr").attr("id", "registered");
							else
								$(this).parents("tr").attr("id", "unregistered");
								
							if (!$(this).is(':checked') && checked == true)
							{
								$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) + reg_fee));
								$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), reg_fee, "add"));
							}
							
							if ($(this).is(':checked') && checked == false)
							{
								$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) - reg_fee));
								$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), reg_fee, "subtract"));
							
								<? for ($i = $add_on_start; $i < $add_on_end; $i++) { ?>
						
								if ($(this).parent("div").find("input[name=add_on_<?=$i;?>]").is(":checked"))
								{
									$(this).parent("div").find("input[name=add_on_<?=$i;?>]").attr("checked", false);
									$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), <?=$add_ons[$i]['price'];?>, "subtract"));
									$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) - <?=$add_ons[$i]['price'];?>));
								}
								
								<? } ?>
							}
							
							$(this).attr("checked", checked);
							
						}
						
					});
					
				});
				
				$("#registration_fee").live('click', function() {				
					if ($(this).is(":checked"))
					{
						$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) + reg_fee));
						$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), reg_fee, "add"));
					}
					else
					{
						<? for ($i = $add_on_start; $i < $add_on_end; $i++) { ?>
						
						if ($(this).parent("div").find("input[name=add_on_<?=$i;?>]").is(":checked"))
						{
							$(this).parent("div").find("input[name=add_on_<?=$i;?>]").attr("checked", false);
							$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), <?=$add_ons[$i]['price'];?>, "subtract"));
							$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) - <?=$add_ons[$i]['price'];?>));
						}
						
						<? } ?>
						/*
						if ($(this).parent("div").find("input[name=add_on_two]").is(":checked"))
						{
							$(this).parent("div").find("input[name=add_on_two]").attr("checked", false);
							$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), 24, "subtract"));							
							$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) - 24));
						}
						*/
						$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) - reg_fee));
						$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), reg_fee, "subtract"));
					}
				});
				// ********** REGISTARTION FEES ********** //
				
				// ********** ADD ON ONE FEES ********** //
				/*
				<? //for ($i = $add_on_start; $i < $add_on_end; $i++) { ?>
				
				$("#toggle_add_on_<?=$i;?>").live('click', function() {						
					
					if ($(this).is(":checked"))
						var checked = true;
					else
						var checked = false;
                                            					
					$.each($("#students_table").find("input[name=add_on_<?=$i;?>]"), function() {
						
						if ($(this).attr('type') != 'hidden') 
						{		
							
							if ($(this).parents("tr").attr("id") == "registered" || $(this).parents("tr").find("input[name=registration_fee]").is(':checked'))
							{
					
								if (!$(this).is(':checked') && checked == true)
								{
									$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) + <?=$add_ons[$i]['price'];?>));
									$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), <?=$add_ons[$i]['price'];?>, "add"));
								}
								
								if ($(this).is(':checked') && checked == false) 
								{
									$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) - <?=$add_ons[$i]['price'];?>));
									$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), <?=$add_ons[$i]['price'];?>, "subtract"));
								}
							
								$(this).attr("checked", checked);
								
							}
							
						}
						
					});
				});				
				
				$("#add_on_<?=$i;?>").live('click', function() {	
					if ($(this).is(":checked"))
					{
						if ($(this).parents("div").find("input[name=registration_fee]").attr("type") != "hidden") {
							if ($(this).parents("div").find("input[name=registration_fee]").is(":checked") == false) {
								$(this).attr("checked", false);
								return;
							}
						}
						/*
						if ($(this).parents("div").find("input[name=registration_fee]")) { 
							if ($(this).parents("div").find("input[name=registration_fee]").is(":checked") == false)
							{
								$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) + reg_fee));
								$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), reg_fee, "add"));
								$(this).parent("div").find("input[name=registration_fee]").attr("checked", true);
							}
						}
						*/
						/*
						$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) + <?=$add_ons[$i]['price'];?>));
						$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), <?=$add_ons[$i]['price'];?>, "add"));
					}
					else
					{
						$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) - <?=$add_ons[$i]['price'];?>));
						$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), <?=$add_ons[$i]['price'];?>, "subtract"));
					}
				});
				<? //} ?>
				// ********** ADD ON ONE FEES ********** //
				
				
				// ********** ADD ON TWO FEES ********** //	
				/*
				$("#toggle_add_on_2").live('click', function() {				

					if ($(this).is(":checked"))
						var checked = true;
					else
						var checked = false;				
				
					$.each($("#students_table").find("input[name=add_on_2]"), function() { 
					
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
				
				$("#add_on_2").live('click', function() {				
					if ($(this).is(":checked"))
					{
						if ( $(this).parents("div").find("input[name=registration_fee]").is(":checked") == false)
						{
							$("#grand_total_id").html(money_format(parseFloat($("#grand_total_id").html()) + 40));
							$(this).parents("tr").find("td[name=student_total]").html(calculate_student_total(parseFloat($(this).parents("tr").find("td[name=student_total]").html()), reg_fee, "add"));
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
				*/
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
					
					<? if (!empty($reg)) echo "url += '&registered=1'"; ?>
					
					url += "&fee=<?=$reg_fee?>";
						
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
					<? if (isset($message) && $message != "") : ?>
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

