<?php
session_start();
if ( !isset( $_SESSION['hschool'] ) ) 
    header( "Location: admin.php" );
$h_school = $_SESSION['hschool'];

include("check_admin_id.php");
$next_page = "false";

include("db.php");
include("classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
$admin->get_school_id();

include("classes/school.php");
$sql = "SELECT * FROM schools WHERE school_id=" . $admin->school_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$school = new school($row);

$message = "";
if (isset($_POST['submit'])) {
	
	foreach ($_POST as $k => $v) {
		$_POST[$k] = mysql_real_escape_string(trim($v));
	}
	
    $school_id = $_POST['school_id'];
	if (!isset($_POST['reg_type'])) {
		$message = "You need to choose the type of school that you have.";
	} else {
		$sql = "update schools set reg_type = " . mysql_real_escape_string($_POST['reg_type']) . " where school_id = " . $school_id;
		mysql_query($sql);
	}
    /*
    //add scanner qty to database
    if (isset($_POST['qty'])) {
	$qty = trim($_POST['qty']);
	if ( $qty == "" ) 
        $qty = 0;

        $sql = "select * from school_accessories where school_id = " . $school_id . " and year = " . $year;
        $result = mysql_query( $sql );
        if ( mysql_num_rows($result) > 0 ) {
            $sql = "update school_accessories set scanners = " . $qty . " where school_id = " . $school_id . " and year = " . $year;
        } else {
            $sql = "insert into school_accessories values ('', $school_id, $year, $qty)";
        }
        mysql_query( $sql );
    }
	*/
	if ($message == "") {
		$next_page = "true";
	}
		
}

include("classes/user.php");
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
require 'class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" dir="<?=$dir?>">

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<title>School Registration</title>
		<link rel="alternate" media="print" href="index.php">
		<link href="admin_styles.css" rel="stylesheet" type="text/css" />
		<script src="camps/scripts/jquery.tools.min.js"></script>
		<script src="scripts/jquery.placeholder.js"></script>
		
		<script>
			var next_page = "<?=$next_page;?>";
			var admin_id = <?=$admin_id;?>;
			var school_id = <?=$admin->school_id;?>;
		
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
				
				$(".reg_type").click( function() {
					var val = $(this).val();
					if (val == 3) {
						$(".regAmount").text('50/55');
					} else {
						$(".regAmount").text('45');
					}
				});
			});
			
			function check_next_page() {
				if (next_page == "true") {
					var registration_form_six = document.forms["registration_form_six"];
					registration_form_six.elements["admin_id"].value = admin_id;
					registration_form_six.elements["school_id"].value = school_id;
					registration_form_six.submit();
				}
			}
			
			function validate() {
				if (!$(".reg_type").is(":checked")) {
					alert("You must choose what type of school registration you have.");
					return false;
				}
				return true;
			}			
		</script>
	</head>

	<body onload="check_next_page();">
	
		<FORM name="registration_form_six" method="post" action="registration_6.php">
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
						
							<h1>School Registration</h1>
	 
							<form id="form_5" name="form_5" action="registration_4.php" method="post" accept-charset="UTF-8"> 
								<input type="hidden" name="school_id" value="<?=$admin->school_id;?>">
								<input type="hidden" name="admin_id" value="<?=$admin_id;?>">
                            
                            <? if ( !$h_school ) { ?>
								<h2>Type of School Registration</h2>
								<div class="module list_expand" id="module-info">
									<div class="module_content">
										<div class="lists form">
											<ul>
												<li>
													 <h4><input type="radio" name="reg_type" class="reg_type" value="1" /> Tzivos Hashem registration is included in school tuition</h4>
													 $45 included in each child’s tuition; parents still complete the registration process on their own without additional payment.
												</li>
												<li>
													 <h4><input type="radio" name="reg_type" class="reg_type" value="2" /> Tzivos Hashem is not included in tuition, yet every child in our school will be registered</h4>
													 Since we guarantee that every child will register, parents will pay the discounted price of $45 when registering on the site; any children not registered by Chof Gimmel Elul (September 14) will be registered through the school’s credit card.
												</li>
												<li>
													 <h4><input type="radio" name="reg_type" class="reg_type" value="3" /> Each student will register on their own</h4>
													 Registration is not included in tuition; each child registers individually for the early-bird price of $50 or regular price of $55 from Chof Gimmel Elul (September 14) onward.
												</li>
											</ul>
										</div>
									</div>
								</div>
							
								<h2>School Yearly Membership Benefits and Fees</h2> 
								<p>Register your school for ONLY $770 and you receive:</p>

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
														<h4>Tzivos Hashem Management System ($950 value)</h4>
														<p>Your state-of-the-art online management system for staff, students and parents.</p>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>Recruitment Poster and Brochures ($50 value)</h4>
														<p>Bring the energy and excitement of a new year to your base with this poster and brochure.
                                                        Gives valuable encouragement to the children for enrolling in the world's most powerful army!
                                                        The brochure describes the tremendous value of the program with an imaginative outline for the parents.
                                                        </p>
													</div>   
												</li>
												<li class="right">
													<div class="box">
														<h4>Total Value: $1000</h4>
													</div>   
												</li>
												<li class="right">
													<div class="box">
														<h4>Discount Package Price: $770</h4>
													</div>   
												</li>
											</ul>
										</div>
									</div>
								</div>

								<h2>Student Yearly Membership Benefits and Fees</h2>
								<p>Once your school is registered, you can begin registering individual students, or have parents enroll their children.</p>
								<p>For ONLY $<span class="regAmount">45</span> each registered student will receive:</p>
								
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
														<h4>Soldier Magazines</h4>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>Hachayol Magazines ($65 value)</h4>
														<p>Each magazine contains a range of 8 to 16 full-color pages of Chassidishe content and fun, that will instill your child with a sense of pride and joy in being a chossid of the Rebbe and passion to bring Moshiach now!</p>
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
														<p>Personalized mission sheets are distributed in school every week. After completing their missions, children are to fill out their mission sheets and review it with their commander.<br />
														After completing a predetermined number of missions, students will earn a medal. 
														Hard-earned medals are kept in an attractive rank book beginning with Private. 
														When the book is full, it's time for a promotion! 
														When a child is promoted they receive a new rank book, and rank card.</p>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>Missions ($23 value)</h4>
														<p>Personalized mission sheets are distributed in school every week.</p>
													</div>   
												</li><li>
													<div class="box">
														<h4>Recognition Medals ($8 value)</h4>
														<p>After completing a set number of missions students earn a medal. Earning a certain number medals earns a rank promotion.</p>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>Rank Books ($10 value)</h4>
														<p>Hard-earned medals are kept in an attractive rank book beginning with Private. When the book is full, it's time for a promotion!</p>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>Rank Cards ($2.00 value)</h4>
														<p>Just for signing up, each student receives a scan-able Tzivos Hashem ID card. Students receive a new rank card every time they are promoted in rank.</p>
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
                                                        <h4>8 Raffles</h4>
                                                        <p>Eight times throughout the year the children can participate in a worldwide raffle.</p>
                                                    </div>   
                                                </li>
                                                <li>
                                                    <div class="box">
                                                        <h4>Global Chinese Auctions</h4>
                                                        <p>Offer students one spectacular end of the year Chinese auction.</p>
                                                    </div>   
                                                </li>
												<li class="right">
													<div class="box">
														<h4>Total Value: $120</h4>
													</div>   
												</li>
												<li class="right">
													<div class="box">
														<h4>Discount Package Price: $<span class="regAmount">45</span></h4>
													</div>   
												</li>
											</ul>
										</div>
									</div>
								</div>
								<!--
								<h2>Additional Store Prizes </h2>
								<p>You may purchase additional prizes at 50% off, and offer them to your children at the cost of their own miles in the online store.</p>
								
								<div class="module list_expand" id="module-info">
									<div class="module_content">
										<div class="lists form">
											<ul>
												<li class="expand">
													<div class="box">
														<h4><span class="icon"></span>View Prizes</h4>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>770 Photo Album + 320 Pictures of the Rebbe</h4>
														<p>See the rooms, share the stories and feel the special sense of living in the Rebbe's Daled Amos, with this magnificent spiral-bound album.
														Plus 320 picture stickers, divided into packs of 5, to be given out each time a Chayol earns 50 miles.</p>
														<p><b>Price: $48</b></p>
														<p><b>Your Price: $24</b></p>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>The Fellig Tehillim</h4>
														<p>Full-color Fellig Chitas Edition of Tehillim, featuring tabs for the daily Yom, translations, illustrations and insights.</p>
														<p><b>Price: $36</b></p>
														<p><b>Your Price: $18</b></p>
													</div>   
												</li>
												<li>
                                                    <div class="box">
                                                        <h4>Haggadah for Kids</h4>
                                                        <p><b>Price: $23</b></p>
                                                        <p><b>Your Price: $15</b></p>
                                                    </div>   
                                                </li>
                                                <li>
                                                    <div class="box">
                                                        <h4>Weekly Siddur with Biur Tefillah</h4>
                                                        <p><b>Price: $36</b></p>
                                                        <p><b>Your Price: $25</b></p>
                                                    </div>   
                                                </li>
                                                <li>
                                                    <div class="box">
                                                        <h4>Shabbos and Yom Tov Siddur with Biur Tefillah</h4>
                                                        <p><b>Price: $36</b></p>
                                                        <p><b>Your Price: $25</b></p>
                                                    </div>   
                                                </li>
												<li>
													<div class="box">
														<h4>Tzivos Hashem Sweatshirt</h4>
														<p>Green cozy sweatshirts with the Tzivos Hashem logo on it, available in small, medium, large and extra large.</p>
														<p><b>Price: $20</b></p>
														<p><b>Your Price: $10</b></p>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>Tzivos Hashem Cap</h4>
														<p>Green striking caps with the Tzivos Hashem logo on it to wear all year round!</p>
														<p><b>Price: $12</b></p>
														<p><b>Your Price: $6</b></p>
													</div>   
												</li>
												<li>
													<div class="box">
														<h4>Tzivos Hashem Yarmulka</h4>
														<p>Navy Yarmulkas with the Tzivos Hashem logo on it.</p>
														<p><b>Price: $10</b></p>
														<p><b>Your Price: $5</b></p>
													</div>   
												</li>
												<li>
                                                    <div class="box">
                                                        <h4>Tzivos Hashem Backpack</h4>
                                                        <p><b>Price: $20</b></p>
                                                        <p><b>Your Price: $10</b></p>
                                                    </div>   
                                                </li>
                                                <li>
                                                    <div class="box">
                                                        <h4>Binder and sticker book</h4>
                                                        <p><b>Price: $23</b></p>
                                                        <p><b>Your Price: $15</b></p>
                                                    </div>   
                                                </li>
                                                <li>
                                                    <div class="box">
                                                        <h4>Haggadah for kids</h4>
                                                        <p><b>Price: $23</b></p>
                                                        <p><b>Your Price: $15</b></p>
                                                    </div>   
                                                </li>
											</ul>
										</div>
									</div>
								</div>
								-->
								
							<? } else { ?>
							    <h2>Registration Fee</h2>
                                <p>Registration fee for Hebrew School 5778 is $770.</p>
                                <p>This includes the use of the Hebrew School Management System.</p>
                                <div class="module list_expand" id="module-info">
                                    <div class="module_content">
                                        <div class="lists form">
                                            <ul>
                                                <li class="expand">
                                                    <div class="box">
                                                        <h4><span class="icon"></span>Features of Hebrew School Management System</h4>
                                                    </div>   
                                                </li>
                                                <li>
                                                    <div class="box">
                                                        <h4>Uploads</h4>
                                                        <p>Upload your school List</p>
                                                        <p>Upload pictures of your students</p>
                                                    </div>   
                                                </li>
                                                <li>
                                                    <div class="box">
                                                        <h4>Register your students</h4>
                                                        <p>Student registration can be done within the system after finishing the school setup</p>
                                                    </div>   
                                                </li>
                                                <li>
                                                    <div class="box">
                                                        <h4>Personalized ID Cards</h4>
                                                        <p>Print paper ID cards with a personalized Barcode</p>
                                                        <p>Order permanent (hard plastic) ID cards ($2 each)</p>
                                                    </div>   
                                                </li>
                                                <li>
                                                    <div class="box">
                                                        <h4>Print Achievement Cards with barcodes</h4>
                                                        <p>You can print cards for anything you want</p>
                                                        <p>You can decide how many “points” the cards should be worth</p>
                                                    </div>   
                                                </li>
                                                <li>
                                                    <div class="box">
                                                        <h4>Print Reports</h4>
                                                        <p>Print Point Reports</p>
                                                        <p>Print Reports of prizes ordered online by your students</p>
                                                    </div>   
                                                </li>
                                                <li>
                                                    <div class="box">
                                                        <h4>Manage your Hebrew School Store</h4>
                                                        <p><b>You can:</b></p>
                                                        <p>Add Prizes of your choice</p>
                                                        <p>You can add prizes for all grades or for specific class</p>
                                                        <p>Determine how many points each prize should cost</p>
                                                        <p>Keep inventory of how many of each prize you have left</p>
                                                        <p>Print barcodes for the items in your store</p>
                                                        <p>Scan a child’s ID card then scan a prize barcode to purchase</p>
                                                        <p><b>Children can:</b></p>
                                                        <p>View the prizes in your online store</p>
                                                        <p>Add / Remove prize to their shopping cart</p>
                                                        <p>Purchase prizes</p>
                                                    </div>   
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <!--
                                <h2>Accessories</h2> 
                                
                                <p>In order to utilize our program you will need to purchase a scanner for each 
                                  computer that you wish to setup.</p>
                                  <p>Each scanner costs $50.</p>
                                
                                <? //find out if school already entered once a quantity for scanners but got stuck in the registration process
                                //$sql = "select scanners from school_accessories where year = '5774' and school_id = " . $admin->school_id;
                                //$result = mysql_query( $sql );
                                //$row = mysql_fetch_assoc( $result );
                                //$qty = $row['scanners'];
                                ?>
                                
                                <div class="module" id="module-info">
                                    <div class="module_content">
                                        <div class="lists form">
                                            <ul>
                                                <li>
                                                    <div class="box">
                                                        <h4>Purchase Scanners</h4>
                                                        <p>
                                                            <span class="label">Quantity:</span>
                                                            <span class="input small">
                                                                <input type="text" name="qty" id="qty" value="<?=isset($qty)?$qty:''?>">
                                                            </span>
                                                        </p>
                                                    </div>   
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
								-->
							<? } ?>
								
								<div class="module" id="module-info">
									<div class="module_content">
										<div class="lists form">
											<ul>
												<li>
													<input name="submit" type="submit" value="Continue" class="button" onclick="return validate()"> 
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
