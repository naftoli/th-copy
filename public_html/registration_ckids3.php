<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
session_start();

if ( !isset( $_SESSION['admin_id'] ) ) 
    header( "Location: registration_ckids.php" );

if ( !isset( $_SESSION['school_id'] ) ) 
    header( "Location: registration_ckids2.php" );

$admin_id = $_SESSION['admin_id'];
$school_id = $_SESSION['school_id'];

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
		</script>
	</head>

	<body>
	
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
	 
							<form id="form_5" name="form_5" action="registration_6.php" method="post" accept-charset="UTF-8"> 							
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
								
								<div class="module" id="module-info">
									<div class="module_content">
										<div class="lists form">
											<ul>
												<li>
													<input name="submit" type="submit" value="Continue" class="button"> 
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
