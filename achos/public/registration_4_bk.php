<?php
//include("check_admin_id.php");

$next_page = "false";

include("db.php");
include("camps/includes/classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
$admin->get_school_id();

include("camps/includes/classes/school.php");
$sql = "SELECT * FROM schools WHERE school_id=" . $admin->school_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$school = new school($row);

$message = "";
if (isset($_POST["action"])) {

	$sql = "DELETE FROM school_add_on_grades WHERE school_id=" . $admin->school_id . " AND add_on_number=1";
	$query = mysql_query($sql);
	
	$sql = "DELETE FROM school_add_on_grades WHERE school_id=" . $admin->school_id . " AND add_on_number=2";
	$query = mysql_query($sql);

	if (isset($_POST["option_1"])) {
		$sql = "UPDATE schools SET add_on_one=" . $_POST["option_1"] . " WHERE school_id=" . $admin->school_id;
		$query = mysql_query($sql);	

		if ($_POST["option_1"] == 2) {
			$sql = "UPDATE users SET add_on_one=2 WHERE school_id=" . $admin->school_id;
			$query = mysql_query($sql);
		}
		
	}	
	
	if ($_POST["option_1"] == 3) {	
		if (isset($_POST["option_1_grade_Pre1a"])) {
			$sql = "INSERT INTO school_add_on_grades SET school_id=" . $admin->school_id . ", add_on_number=1, grade='" . $_POST["option_1_grade_Pre1a"] . "'";
			$query = mysql_query($sql);	
			$sql = "UPDATE users AS u JOIN classes AS c ON (u.school_id=c.school_id AND u.class_id=c.class_id AND c.class_grade='" . $_POST["option_1_grade_Pre1a"] . "') SET u.add_on_one=1 WHERE u.school_id=" . $admin->school_id;
			$query = mysql_query($sql);	
		}	
		if (isset($_POST["option_1_grade_1"])) {
			$sql = "INSERT INTO school_add_on_grades SET school_id=" . $admin->school_id . ", add_on_number=1, grade='" . $_POST["option_1_grade_1"] . "'";
			$query = mysql_query($sql);	
			$sql = "UPDATE users AS u JOIN classes AS c ON (u.school_id=c.school_id AND u.class_id=c.class_id AND c.class_grade='" . $_POST["option_1_grade_1"] . "') SET u.add_on_one=1 WHERE u.school_id=" . $admin->school_id;
			$query = mysql_query($sql);	
		}
		if (isset($_POST["option_1_grade_2"])) {
			$sql = "INSERT INTO school_add_on_grades SET school_id=" . $admin->school_id . ", add_on_number=1, grade='" . $_POST["option_1_grade_2"] . "'";
			$query = mysql_query($sql);
			$sql = "UPDATE users AS u JOIN classes AS c ON (u.school_id=c.school_id AND u.class_id=c.class_id AND c.class_grade='" . $_POST["option_1_grade_2"] . "') SET u.add_on_one=1 WHERE u.school_id=" . $admin->school_id;
			$query = mysql_query($sql);	
		}
		if (isset($_POST["option_1_grade_3"])) {
			$sql = "INSERT INTO school_add_on_grades SET school_id=" . $admin->school_id . ", add_on_number=1, grade='" . $_POST["option_1_grade_3"] . "'";
			$query = mysql_query($sql);	
			$sql = "UPDATE users AS u JOIN classes AS c ON (u.school_id=c.school_id AND u.class_id=c.class_id AND c.class_grade='" . $_POST["option_1_grade_3"] . "') SET u.add_on_one=1 WHERE u.school_id=" . $admin->school_id;
			$query = mysql_query($sql);	
		}
		if (isset($_POST["option_1_grade_4"])) {
			$sql = "INSERT INTO school_add_on_grades SET school_id=" . $admin->school_id . ", add_on_number=1, grade='" . $_POST["option_1_grade_4"] . "'";
			$query = mysql_query($sql);		
			$sql = "UPDATE users AS u JOIN classes AS c ON (u.school_id=c.school_id AND u.class_id=c.class_id AND c.class_grade='" . $_POST["option_1_grade_4"] . "') SET u.add_on_one=1 WHERE u.school_id=" . $admin->school_id;
			$query = mysql_query($sql);	
		}
		if (isset($_POST["option_1_grade_5"])) {
			$sql = "INSERT INTO school_add_on_grades SET school_id=" . $admin->school_id . ", add_on_number=1, grade='" . $_POST["option_1_grade_5"] . "'";
			$query = mysql_query($sql);		
			$sql = "UPDATE users AS u JOIN classes AS c ON (u.school_id=c.school_id AND u.class_id=c.class_id AND c.class_grade='" . $_POST["option_1_grade_5"] . "') SET u.add_on_one=1 WHERE u.school_id=" . $admin->school_id;
			$query = mysql_query($sql);	
		}
		if (isset($_POST["option_1_grade_6"])) {
			$sql = "INSERT INTO school_add_on_grades SET school_id=" . $admin->school_id . ", add_on_number=1, grade='" . $_POST["option_1_grade_6"] . "'";
			$query = mysql_query($sql);		
			$sql = "UPDATE users AS u JOIN classes AS c ON (u.school_id=c.school_id AND u.class_id=c.class_id AND c.class_grade='" . $_POST["option_1_grade_6"] . "') SET u.add_on_one=1 WHERE u.school_id=" . $admin->school_id;
			$query = mysql_query($sql);			
		}
		if (isset($_POST["option_1_grade_7"])) {
			$sql = "INSERT INTO school_add_on_grades SET school_id=" . $admin->school_id . ", add_on_number=1, grade='" . $_POST["option_1_grade_7"] . "'";
			$query = mysql_query($sql);		
			$sql = "UPDATE users AS u JOIN classes AS c ON (u.school_id=c.school_id AND u.class_id=c.class_id AND c.class_grade='" . $_POST["option_1_grade_7"] . "') SET u.add_on_one=1 WHERE u.school_id=" . $admin->school_id;
			$query = mysql_query($sql);			
		}
	}
	
	if (isset($_POST["option_2"])) {	
		$sql = "UPDATE schools SET add_on_two=" . $_POST["option_2"] . " WHERE school_id=" . $admin->school_id;
		$query = mysql_query($sql);		
	}
	
	if ($_POST["option_2"] == 3) {	
		if (isset($_POST["option_2_grade_3"])) {
			$sql = "INSERT INTO school_add_on_grades SET school_id=" . $admin->school_id . ", add_on_number=2, grade='" . $_POST["option_2_grade_3"] . "'";
			$query = mysql_query($sql);		
			$sql = "UPDATE users AS u JOIN classes AS c ON (u.school_id=c.school_id AND u.class_id=c.class_id AND c.class_grade='" . $_POST["option_2_grade_3"] . "') SET u.add_on_two=1 WHERE u.school_id=" . $admin->school_id;
			$query = mysql_query($sql);			
		}	
		if (isset($_POST["option_2_grade_4"])) {
			$sql = "INSERT INTO school_add_on_grades SET school_id=" . $admin->school_id . ", add_on_number=2, grade='" . $_POST["option_2_grade_4"] . "'";
			$query = mysql_query($sql);	
			$sql = "UPDATE users AS u JOIN classes AS c ON (u.school_id=c.school_id AND u.class_id=c.class_id AND c.class_grade='" . $_POST["option_2_grade_4"] . "') SET u.add_on_two=1 WHERE u.school_id=" . $admin->school_id;
			$query = mysql_query($sql);			
		}
		if (isset($_POST["option_2_grade_5"])) {
			$sql = "INSERT INTO school_add_on_grades SET school_id=" . $admin->school_id . ", add_on_number=2, grade='" . $_POST["option_2_grade_5"] . "'";
			$query = mysql_query($sql);		
			$sql = "UPDATE users AS u JOIN classes AS c ON (u.school_id=c.school_id AND u.class_id=c.class_id AND c.class_grade='" . $_POST["option_2_grade_5"] . "') SET u.add_on_two=1 WHERE u.school_id=" . $admin->school_id;
			$query = mysql_query($sql);					
		}
		if (isset($_POST["option_2_grade_6"])) {
			$sql = "INSERT INTO school_add_on_grades SET school_id=" . $admin->school_id . ", add_on_number=2, grade='" . $_POST["option_2_grade_6"] . "'";
			$query = mysql_query($sql);		
			$sql = "UPDATE users AS u JOIN classes AS c ON (u.school_id=c.school_id AND u.class_id=c.class_id AND c.class_grade='" . $_POST["option_2_grade_6"] . "') SET u.add_on_two=1 WHERE u.school_id=" . $admin->school_id;
			$query = mysql_query($sql);					
		}
		if (isset($_POST["option_2_grade_7"])) {
			$sql = "INSERT INTO school_add_on_grades SET school_id=" . $admin->school_id . ", add_on_number=2, grade='" . $_POST["option_2_grade_7"] . "'";
			$query = mysql_query($sql);		
			$sql = "UPDATE users AS u JOIN classes AS c ON (u.school_id=c.school_id AND u.class_id=c.class_id AND c.class_grade='" . $_POST["option_2_grade_7"] . "') SET u.add_on_two=1 WHERE u.school_id=" . $admin->school_id;
			$query = mysql_query($sql);					
		}
	}
	
	if ($message == "") {
		$next_page = "true";
		//header("Location: http://www.mashpia.com/registration_6.php");
		//header("Location: http://www.mashpia.com/registration_5.php");
	}
		
}

include("camps/includes/classes/user.php");
$users = array();
$sql1 = "SELECT id FROM admin_auths WHERE admin_id=" . $admin_id . " AND auth='user'";
$query1 = mysql_query($sql1);
while ($row1 = mysql_fetch_assoc($query1)) {
	$sql2 = "SELECT * FROM users WHERE user_id=" . $row1["id"] . " AND user_registered IS NOT NULL";
	$query2 = mysql_query($sql2);
	if (mysql_num_rows($query2) > 0) {
		$row2 = mysql_fetch_assoc($query2);
		$user = new user($row2);
	}
}

$no_of_options = 1;
$sql = "SELECT * FROM school_child_types WHERE school_id=" . $admin->school_id . " AND child_type_id=1";
$query = mysql_query($sql);
$num_rows = mysql_num_rows($query);
if ($num_rows > 0)
	$no_of_options = 2;

include("camps/includes/classes/school_add_on.php");
$school_add_ons = array();
$sql = "SELECT * FROM school_add_ons"; 
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$school_add_on = new school_add_on($row);
	array_push($school_add_ons, $school_add_on);
}

include("camps/includes/classes/add_on_option.php");
$add_on_options = array();
$sql = "SELECT * FROM add_on_options"; 
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$add_on_option = new add_on_option($row);
	array_push($add_on_options, $add_on_option);
}

include("camps/includes/classes/school_child_type.php");
$school_child_types = array();
$sql = "SELECT * FROM school_child_types WHERE school_id=" . $admin->school_id;
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$school_child_type = new school_child_type($row);
	$school_child_type->get_child_type_name();
	array_push($school_child_types, $school_child_type);
}

for ($stno = 0; $stno < count($school_child_types); $stno++) {
	if (count($school_child_types) > 1) {
		if ($school_child_types[$stno]->child_type_id==1) $st_chabad = 1;
		if ($school_child_types[$stno]->child_type_id==2) $st_frum = 1;
		if ($school_child_types[$stno]->child_type_id==3) $st_not_frum = 1;
	}
}

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" dir="<?=$dir?>">

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<title>Registration Wizard Tzivos Hashem Management System</title>
		<link rel="alternate" media="print" href="index.php">
		<link href="admin_styles.css" rel="stylesheet" type="text/css" />
		<script src="camps/scripts/jquery.tools.min.js"></script>
		<script src="scripts/jquery.placeholder.js"></script>
		
		<script>
			var next_page = "<?=$next_page;?>";
			var admin_id = <?=$admin_id;?>;
			var school_id = <?=$school_id;?>;
		
			$(function() {
				$('input').placeholder();
				$('.toggle').hide();
				$('input[type="radio"][value="3"]:checked').parents('ul').find('.toggle').show();
				$('input[type="radio"]').change(function(){
						$(this).parents('ul').find('.toggle').slideUp('fast');
						$(this).filter('[value="3"]:checked').parents('ul').find('.toggle').slideDown('fast');
				});
				$('.slider:last .module.list_expand li.expand').nextAll().not('.right').hide();
				$('.slider:last .module.list_expand li.expand').click(function(){
					$(this).nextAll().not('.right').slideToggle('fast');
					$(this).toggleClass('open');
				});
				if ($('.st_table.title div').is(":not(':visible')")) {$('.st_table.title').parents('li').remove()};
				$("#nav").height($("#content").height());

			});

			function check_radio_buttons(frm) {	
				var option_1_value = 0;
				for(i = 0; i < frm.option_1.length; i++) {
					if (frm.option_1[i].checked) {
						option_1_value = frm.option_1[i].value;
					}
				}
				var option_2_value = 0;
				for(i = 0; i < frm.option_2.length; i++) {
					if (frm.option_2[i].checked) {
						option_2_value = frm.option_2[i].value;
					}
				}

				var option_1_grade = false;
				if (option_1_value == 3) {
					var add_on_grades_div = document.getElementById("add_on_grades_0");
					var checkboxes = $(add_on_grades_div).find("input");
					
					for (cbno = 0; cbno < $(checkboxes).size(); cbno++) {
						var checkbox = $(checkboxes).get(cbno);
						if (checkbox.checked) {
							option_1_grade = true;
							break;
						}
					}					
				}
				else {
					option_1_grade = true;
				}
				
				var option_2_grade = false;
				if (option_2_value == 3) {
					var add_on_grades_div = document.getElementById("add_on_grades_1");
					var checkboxes = $(add_on_grades_div).find("input");
					
					for (cbno = 0; cbno < $(checkboxes).size(); cbno++) {
						var checkbox = $(checkboxes).get(cbno);
						if (checkbox.checked) {
							option_2_grade = true;
							break;
						}
					}					
				}
				else {
					option_2_grade = true;
				}

								
				if (option_1_grade == false) {
					alert("You must choose at least one grade for Add-On Option 1");
					return false;
				}
				else if (option_2_grade == false) {
					alert("You must choose at least one grade for Add-On Option 2");
					return false;				
				}
				else if (document.getElementById("cc-agree").checked == false) {
					alert("You must agree for your card to be charged the registration fee.");
					return false;
				}
				else {
				
					var clicked = false;
					for (rno = 0; rno < frm.option_1.length; rno++) {
						 if (frm.option_1[rno].checked == true) {
							var selected_option = frm.option_1[rno];
							clicked = true;
						}
					}
					
					if (clicked == false) {
						document.getElementById("option_1").focus();
						alert("You must pick Student Add-On Option 1");
						return false;
					}
					else {				
						clicked = false;
						for (rno = 0; rno < frm.option_2.length; rno++) {
							 if (frm.option_2[rno].checked == true) 
								clicked = true;
						}
						
						if (clicked == false) {
							document.getElementById("option_2").focus();
							alert("You must pick Student Add-On Option 2");
							return false;
						}					
					}
					
					if (clicked == true) 
						return true;
				}
			}
			
			function check_next_page() {
				if (next_page == "true") {
					var registration_form_six = document.forms["registration_form_six"];
					registration_form_six.elements["admin_id"].value = admin_id;
					registration_form_six.elements["school_id"].value = school_id;
					registration_form_six.submit();
				}
			}						
		</script>
		<!--Copyright Ariel Shkedi 2007-2010-->
	</head>

	<body onload="check_next_page();">
	
		<FORM name="registration_form_six" method="post" action="https://mashpia.com/registration_6.php">
			<input type="hidden" name="admin_id" value="">
			<input type="hidden" name="school_id" value="">
		</FORM>
	
		<NOSCRIPT>
			<P STYLE="color: red; font-size: larger;">Notice: You have javascript disabled. Some parts of the site will not function without javascript.</P>
		</NOSCRIPT>
		
		<div id="wrapper">
		
			<div id="nav" class="wizard">
			
				<div class="col_title_bg"></div>
				
				<div class="col_title">Menu</div>
				
				<? include("registration_menu.php"); ?>				
			</div>
			
			
			<div id="content" class="registration">
			
				<div class="col_title_bg"></div>
				
				<div class="slider_container">
				
					<div class="slider">
					
						<div class="col_title"></div>
						
						<div class="col_content left">
						
							<h1>Registration Wizard - Setup Program</h1>
	 
							<form name="form_5" action="registration_4.php" method="post" accept-charset="UTF-8" name="login" onsubmit="return check_radio_buttons(this);"> 
							
								<input type="hidden" name="action" value="update">
								<input type="hidden" name="school_id" value="<?=$admin->school_id;?>">
								<input type="hidden" name="admin_id" value="<?=$admin_id;?>">

								<h2>School Yearly Membership Benefits and Fees</h2> 
								<p>Register your school for ONLY $500 and you receive:</p>

								<div class="module list_expand" id="module-info">
									<div class="module_content">
										<div class="lists form">
											<ul>
												<li class="expand">
													<div class="box">
														<h4><span class="icon"></span>School Benefits and Fees</h4>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>Tzivos Hashem Management System ($1000 value)</h4>
														<p>Your state-of-the-art online management system for staff, students and parents.</p>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>Recruitment Poster ($150 value)</h4>
														<p>Bring the energy and excitement of a new year to your base with this 4' x 2.3' display poster.</p>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>Registration Brochures ($50 value)</h4>
														<p>Gives valuable encouragement to the children for enrolling in the world's most powerful army!</p>
														<p>Describes the tremendous value of the program with an imaginative outline for the parents.</p>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>Ongoing Support ($300 value)</h4>
                                                       	<p>Receive quick responses to your questions and comments with a simple click, phone call or email.</p>
													</div>   
												</li>
											</ul>
											<ul>
												<li class="expand">
													<div class="box">
														<h4>Bonus for new schools</h4>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>Billboard ($350 value)</h4>
                                                        <p>Display this full color 4' x 8' canvas billboard in your base hallway, featuring a program overview, prize gallery and a notice board for ongoing reports.</p>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>Bar-code Scanner ($100 value)</h4>
                                                        <p>USB handheld scanner to scan achievement cards and ID cards.</p>
													</div>   
												</li>
												<li class="right">
													<div class="box">
														<h4>Total Value: $1500</h4>
													</div>   
												</li>
												<li class="right">
													<div class="box">
														<h4>Discount Package Price: $500</h4>
													</div>   
												</li>
											</ul>
										</div>
									</div>
								</div>

								<h2>Student Yearly Membership Benefits and Fees</h2>
								<p>Once your school is registered, you can begin registering individual students, or have parents enroll their children.</p>
								<p>For ONLY $40 each registered student will receive:</p>
								
								<div class="module list_expand" id="module-info">
									<div class="module_content">
										<div class="lists form">
											<ul>
												<li class="expand">
													<div class="box">
														<h4><span class="icon"></span>Student Benefits and Fees</h4>
													</div>   
												</li>
												<li class="expand">
													<div class="box">
														<h4>New for 5772</h4>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>Siddur with Biur Tefillah ($36 value)</h4>
														<p>A siddur with child friendly translation and basic explanation of each tefillah made specially for children in Tzivos Hashem.</p>
													</div>   
												</li>
												<li class="expand">
													<div class="box">
														<h4>Soldier Magazines</h4>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>For Chabad Children:</h4>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>10 Hachayol Magazines ($36 value)</h4>
														<p>Each magazine contains 20 full-color pages of Chassidishe content and fun, that will instill your child with a sense of pride and joy in being a chossid of the Rebbe and passion to bring Moshiach now!</p>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>For Frum Children, a choice of:</h4>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>6 Moshiach Times ($22 value)</h4>
														<p>The Moshiach Times is geared towards frum children and contains a wealth of information about Yiddishkeit, stories, articles about history, nature, puzzles, and humor - and it's all presented with warmth and care.</p>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>4 Kids Magazine ($14 value)</h4>
														<p>The Kids Magazine is geared towards not yet frum children and is bursting with the latest in graphics, ideas and games guaranteed to enthrall your children and teach them about the Yomin Tovim in a way they've never seen it before!</p>
													</div>   
												</li>
												<li class="expand">
													<div class="box">
														<h4>Management System</h4>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>Student & Parent Account ($12 value)</h4>
														<p>Children scan their cards to update accounts, report progress, earn miles and buy prizes!<br />
														Parents follow their children's progress and help them enter missions and print reports.</p>
													</div>   
												</li>
												<li class="expand">
													<div class="box">
														<h4>Rank System</h4>
													</div>   
												</li>
												<li>
													<div class="box">
														<p>Personalized mission sheets are distributed in school every week. After completing their missions, children are to fill out their mission sheets and review it with their commander, who, in turn, gives the child a sticker for each completed mission.<br />
														The children keep track of completed missions by placing these stickers on the sticker board. After completing a predetermined number of missions, students will earn a medal. This achievement coincides with the giving and pasting of the final sticker for that sticker board.<br />
														Hard-earned medals are kept in an attractive rank book beginning with Private. When the book is full, it's time for a promotion! When a child is promoted they receive a new rank book, rank card and a beautiful certificate to display.</p>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>Missions ($10 value)</h4>
														<p>Personalized mission sheets are distributed in school every week.</p>
													</div>   
												</li><li>
													<div class="box">
														<h4>Mission Sticker Book and Sticker Boards ($25 value)</h4>
														<p>Students track completed missions and count down to new medals.</p>
													</div>   
												</li><li>
													<div class="box">
														<h4>Mission Binder ($10 value) Bonus for first-time registrants</h4>
														<p>A leatherette zipper-ring binder embossed with the TH logo helps students keep their sticker boards safe and stylish.</p>
													</div>   
												</li><li>
													<div class="box">
														<h4>Recognition Medals ($10 value)</h4>
														<p>After completing a set number of missions students earn a medal. Earning a certain number medals earns a rank promotion.</p>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>Rank Books ($10 value)</h4>
														<p>Hard-earned medals are kept in an attractive rank book beginning with Private. When the book is full, it’s time for a promotion!</p>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>Rank Cards ($2.50 value)</h4>
														<p>Just for signing up, each student receives a scan-able Tzivos Hashem ID card. Students receive a new rank card every time they are promoted in rank.</p>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>Rank Certificates ($1 value)</h4>
														<p>For each earned rank promotion, students receive a beautiful certificate to display.</p>
													</div>   
												</li>
												<li class="expand">
													<div class="box">
														<h4>Mileage Program</h4>
													</div>   
												</li>
												<li>
													<div class="box">
														<p>In addition to the missions and rank system that Tzivos Hashem has set up, you as a school has the ability to motivate children to do other things which are not part of the handed-out mission sheet.<br />
														We provide you with the ability to print achievement cards (cards worth miles) for whatever you decide.<br />
														Children can accumulate these miles and use them in the global Chinese auctions, as well as in the school online store. (Please Note: Each school is required to provide the prizes for the store on their own).</p>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>Scratch off Achievement Cards ($5 value)</h4>
														<p>Receive a set of 30 scratch off achievement cards for each student registered. Cards contain anywhere from 1 to 500 miles.</p>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>Global Chinese Auctions</h4>
														<p>Offer students 3 spectacular pre-set Chinese auctions during each school year.</p>
													</div>   
												</li>
												<li class="right">
													<div class="box">
														<h4>Total Value: $155</h4>
													</div>   
												</li>
												<li class="right">
													<div class="box">
														<h4>Discount Package Price: $40</h4>
													</div>   
												</li>
											</ul>
										</div>
									</div>
								</div>
								
								
								<? for ($ano = 0; $ano < $no_of_options; $ano++) : ?>
									<? $add_on = $school_add_ons[$ano]; ?>
									
									<h2><?=$add_on->title;?></h2>
									
									<div class="module" id="module-info">
										
										<div class="module_content">
										
											<div class="lists form">
											
												<ul class="<?=($st_chabad)?'stt_chabad':''?> <?=($st_frum)?'stt_frum':''?> <?=($st_not_frum)?'stt_not_frum':''?>">
												
													<li>
														<div class="box">															
															<div class="st_table title">
																	<div class="st_chabad">Chabad</div>
																	<div class="st_frum">Frum</div>
																	<div class="st_not_frum">Not Yet Frum</div>
															</div>
														</div>   
													</li>
												
													<li>
														<div class="box">
															<div class="st_table">
																<div class="st_chabad"></div>
																<div class="st_frum<?=($ano == 1)?' unchecked':''?>"></div>
																<div class="st_not_frum<?=($ano == 1)?' unchecked':''?>"></div>
															</div>
															<h4><?=$add_on->line_1;?></h4>
															<p><?=$add_on->description_1;?></p>
														</div>   
													</li>
													
													<li>
														<div class="box">
															<div class="st_table">
																<div class="st_chabad"></div>
																<div class="st_frum<?=($ano == 1)?' unchecked':''?>"></div>
																<div class="st_not_frum<?=($ano == 1)?' unchecked':''?>"></div>
															</div>
															<h4><?=$add_on->line_2;?></h4>
															<p><?=$add_on->description_2;?></p>
														</div>   
													</li>
													
													<li class="right">
														<div class="box">
															<h4>Total Value: $<?=$add_on->value;?></h4>
														</div>   
													</li>
													<li class="right">
														<div class="box">
															<h4>Discount Package Price: $<?=$add_on->price;?></h4>
														</div>   
													</li>
													
													<!-- ***** Add On Options ***** -->
													<li>
														<div class="box">														
															<? for ($aono = 0; $aono < count($add_on_options); $aono++) : ?>
																<? $add_on_option = $add_on_options[$aono]; ?>
																<h4>
																	<? if ( ($ano == 0 && $school->add_on_one == $add_on_option->add_on_option_id) || ($ano == 1 && $school->add_on_two == $add_on_option->add_on_option_id) ) : ?>
																		<label><input type="radio" checked name="option_<?=($ano + 1);?>" id="option_<?=($ano + 1);?>" value="<?=$add_on_option->add_on_option_id;?>" /><?=$add_on_option->description;?></label>
																	<? else : ?>
																		<label><input type="radio" name="option_<?=($ano + 1);?>" id="option_<?=($ano + 1);?>" value="<?=$add_on_option->add_on_option_id;?>" /><?=$add_on_option->description;?></label>
																	<? endif; ?>
																</h4>
															<? endfor; ?>
															
														</div>  												
													</li>
													<!-- ***** Add On Options ***** -->
												
													<DIV name="add_on_grades_<?=$ano;?>" id="add_on_grades_<?=$ano;?>">
													<li class="toggle">
														<div class="box">
															<h4>
																<? $sql = "SELECT aoog.*, saog.school_add_on_grade_id FROM add_on_option_grades AS aoog LEFT JOIN school_add_on_grades AS saog ON (saog.add_on_number=" . ($ano + 1) . " AND saog.school_id=" . $admin->school_id . " AND aoog.grade=saog.grade) WHERE add_on_option_id=" . $add_on->school_add_on_id; ?>																
																<? $query = mysql_query($sql); ?>
																<? while ($row = mysql_fetch_assoc($query)) : ?>
																	
																	<? if ($row["school_add_on_grade_id"] > 0) : ?>
																		<label><input type="checkbox" checked name="option_<?=($ano + 1);?>_grade_<?=$row['grade'];?>" value="<?=$row['grade'];?>" /><?=$row['grade'];?></label>
																	<? else : ?>
																		<label><input type="checkbox" name="option_<?=($ano + 1);?>_grade_<?=$row['grade'];?>" value="<?=$row['grade'];?>" /><?=$row['grade'];?></label>
																	<? endif; ?>
																<? endwhile; ?>
															</h4>
														</div>   
													</li>
													</DIV>
												
												</ul>
												
											</div>
											
										</div>
										
									</div>																			
									
								<? endfor; ?>
								
								<div class="module" id="module-info">
									<div class="module_content">
										<div class="lists form">
											<ul>
												<li>
													<div class="box">
														<p><input type="checkbox" id="cc-agree" name="cc-agree" value="true" />I agree for my card to be charged the registration fee for every student that I (or the program director) registers into the TH program from my school. [Parents who register directly will pay their own registration fee/s.]</p>
													</div>   
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
