<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
session_start();

//echo "<pre>"; print_r( $_SESSION ); echo "</pre>";
if ( !isset( $_SESSION['school_id'] ) ) 
    header( "Location: registration.php" );

$admin_id = $_SESSION['admin_id'];
$school_id = $_SESSION['school_id'];
$next_page = "false";

include("db.php");
require_once( __DIR__ . '/api/header/db.php' ); // import ActiveRecord and PDO

require 'class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();
// get the registration info for the school
try {
    $schoolInfo = School::find( $school_id, [ 'include' => 'school_registrations' ] );
    $schoolInfo = $schoolInfo->getRegInfo( $year );
} catch ( \Exception $e ) {
    $query = mysql_query("SELECT reg_type FROM schools WHERE school_id=" . $school_id);
    $type = mysql_fetch_assoc($query)['reg_type'];
    $schoolInfo = SchoolRegistration::getDefault( $school_id, $type, $year );
};

$message = "";
if (isset($_POST['submit'])) {

	if (!isset($_POST['reg_type']) || !isset($_POST['store_points'])) {
		$message = "You need to choose the type of school that you have and your store options.";
	} else {
		$store_points = mysql_real_escape_string( $_POST['store_points'] );
		switch ($store_points) {
			case 1:
				$reset_points = 2458285;
				break;
			case 2:
				$reset_points = 2458347;
				break;
			case 3:
				$reset_points = 0;
				break;
			case 4:
				$str_date = mysql_real_escape_string( $_POST['store_date'] );
				if ( empty( $str_date) ) {
					$message = "You need to enter a valid date for the store points reset.";
					break;
				}
				$dates = explode('-', $str_date);
				$yy = $dates[0];
				$mm = $dates[1];
				$dd = $dates[2];
				$reset_points = gregoriantojd($mm, $dd, $yy);
				break;
		}

		if ( !$message ) {
			$sql = "UPDATE schools 
					SET reg_type = " . mysql_real_escape_string($_POST['reg_type']) . ", 
					store_reset = " . $reset_points . " 
					WHERE school_id = " . $school_id;
			$schoolInfo->type = $_POST['reg_type'];
			if ( !mysql_query($sql) || !$schoolInfo->save() ) {
				$message = "Error updating school.";
			}
		}
	}
    
	if ($message == "") {
		$next_page = "true";
	}
		
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" dir="<?=$dir?>">

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8;" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<title>School Registration</title>
		<link rel="alternate" media="print" href="index.php">
		<link href="admin_styles.css" rel="stylesheet" type="text/css" />
		<script src="camps/scripts/jquery.tools.min.js"></script>
		<script src="scripts/jquery.placeholder.js"></script>
		
		<script>
			var next_page = "<?=$next_page;?>";
		
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
						$(".regAmount").text('<?=$schoolInfo->getChildFee( true, 3 ) .'/'. $schoolInfo->getChildFee( true, 3, true )?>');
					} else if ( val == 2 ) {
						$(".regAmount").text('<?=$schoolInfo->getChildFee( true, 2 ) .'/'. $schoolInfo->getChildFee( true, 2, true )?>');
					} else {
                        $(".regAmount").text(<?=$schoolInfo->getChildFee( true, 1 )?>);
                    }
				});
			});
			
			function check_next_page() {
				if (next_page == "true") {
					location.href = "registration_6.php";
				}
			}
			
			function validate() {
				if (!$(".reg_type:checked").length) {
					alert("You must choose what type of school registration you have.");
					return false;
				}
				if (!$(".store_points:checked").length) {
					alert("You must choose your store option.");
					return false;
				}
				var store = $(".store_points:checked").val();
				if (store == 4) {
					if (!$(".store_date").val()) {
						alert("You must choose a valid date for resetting your store.");
						return false;
					}
				}
				return true;
			}			
		</script>
	</head>

	<body onload="check_next_page();">
	
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
							    <h2>Type of School Registration</h2>
								<div class="module list_expand" id="module-info">
									<div class="module_content">
										<div class="lists form">
											<ul>
												<li>
													<h4>
                                                        <input type="radio" name="reg_type" class="reg_type" value="1" 
													        <?php if ($schoolInfo->type == 1) echo "checked" ?> /> 
                                                        Tzivos Hashem registration is included in school tuition
                                                    </h4>
                                                    $<?=$schoolInfo->getChildFee( true, 1 )?> included in each child’s tuition; 
													parents still complete the registration process on their own without additional payment. 
													<strong>School credit card gets automatically charged at once for all unregistered students by 24 Elul (Sep 4).</strong>
												</li>
												<li>
													 <h4>
                                                        <input type="radio" name="reg_type" class="reg_type" value="2" 
                                                            <?php if ($schoolInfo->type == 2) echo "checked" ?> />
															$<?=$schoolInfo->getChildFee( true, 1 )?> per student. 
															Tzivos Hashem is not included in tuition, yet every child in our school will be registered
                                                    </h4>
                                                    Since we guarantee that every child will register, 
                                                    parents will receive an additional discount of $<?=GlobalSettings::getGuarenteedDiscount()?> 
                                                    (above the early bird discount of $<?=GlobalSettings::getEarlyBird()?>).
                                                    If all children are not registered by 
                                                    <?=
                                                    iconv ('WINDOWS-1255', 'UTF-8', substr(  
                                                        jdtojewish( unixtojd( $schoolInfo->early_bird->getTimestamp() ) + 1, true ), 0, -6
                                                    ));
                                                    ?>
                                                    (<?= $schoolInfo->early_bird->format('F j') ?>)
                                                    <!--Chof Gimmel Elul (September 14)--> 
                                                    then Tzivos Hashem will automatically charge the credit card on file for the additional discount provided ($<?=GlobalSettings::getGuarenteedDiscount()?> per child registered).
												</li>
												<li>
                                                    <h4><input type="radio" name="reg_type" class="reg_type" value="3" 
                                                    <?php if ($schoolInfo->type == 3) echo "checked" ?>
                                                    /> Each student will register on their own</h4>
                                                    Registration is not included in tuition; 
                                                    each child registers individually for the early-bird price of $<?=$schoolInfo->getChildFee( true, 3 )?>
                                                    or regular price of $<?=$schoolInfo->getChildFee( true, 3, true )?> from 
                                                    <?=
                                                    iconv ('WINDOWS-1255', 'UTF-8', substr(  
                                                        jdtojewish( unixtojd( $schoolInfo->early_bird->getTimestamp() ) + 1, true ), 0, -6
                                                    ));
                                                    ?>
                                                    (<?= $schoolInfo->early_bird->format('F j') ?>) onward.
												</li>
											</ul>
										</div>
									</div>
								</div>
							
								<h2>School Yearly Membership Benefits and Fees</h2> 
								<p>Click <a href="https://docs.google.com/document/d/1W9-gsHpu2yiEvKpNdmJJ_A4aCUPLnGgUFyqNzJq_vuk/edit" target="_blank">here</a> 
								to see what’s included in the school and child registration packages</p>
								<p>Register your school for ONLY $<?=$schoolInfo->fee?> and you receive:</p>

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
														<h4>Tzivos Hashem Online Management System ($950 value)</h4>
                                                        <p>
                                                            State of the art Online portal for staff, students and parents. 
															Constantly new features and ticketing system for direct communication 
															with our development team.
                                                        </p>
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
														<h4>Your Price: $<?=$schoolInfo->fee?></h4>
													</div>   
												</li>
											</ul>
										</div>
									</div>
								</div>

								<h2>Student Yearly Membership Benefits and Fees</h2>
								<p>Once your school is registered, you can begin registering individual students, or have parents enroll their children.</p>
								<p>
                                    For ONLY $<span class="regAmount"><?
                                    if ( $schoolInfo->type == '1' ) { echo $schoolInfo->getChildFee( true ); }
                                    else { echo $schoolInfo->getChildFee( true )?>/<?=$schoolInfo->getChildFee( true, false, true ); }
                                    ?></span> each registered student will receive:
                                </p>
								
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
														<h4>Total Value: Exceeds $150</h4>
													</div>   
												</li>
												<li class="right">
													<div class="box">
														<h4>
                                                            Your Price: $<span class="regAmount"><?
                                                            if ( $schoolInfo->type == '1' ) { echo $schoolInfo->getChildFee( true ); }
                                                            else { echo $schoolInfo->getChildFee( true )?>/<?=$schoolInfo->getChildFee( true, false, true ); }
                                                            ?></span>
                                                        </h4>
													</div>   
												</li>
											</ul>
										</div>
									</div>
								</div>

								<h2>School Store Option</h2>
								<div class="module list_expand" id="module-info">
									<div class="module_content">
										<div class="lists form">
											<ul>
												<li>
													<h4>
														When would you like the school store points of all your chayolim to go back to 0?
														(please note that this does not effect the auction points)
													</h4>	
												</li>
												<li>
													<input type="radio" name="store_points" class="store_points" value="1" />
													Beis Tammuz/June 15 (Chayolim can use the points they earned from summer missions and on)
												</li>
												<li>
													<input type="radio" name="store_points" class="store_points" value="2" />	
													Hey Elul/Aug 16 (Chayolim will not be able to use the points they earned from the majority of summer missions)
												</li>
												<li>
													<input type="radio" name="store_points" class="store_points" value="3" />
													Never (Points will continue accumulating from last year)
												</li>
												<li>
													<input type="radio" name="store_points" class="store_points" value="4" />
													Choose your own Date: <input type="date" name="store_date" class="store_date" />
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
