<?php 
$admin_auth = array('school','class'); 
require('header.php');
//include("check_admin_id.php");
require 'class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();
?>
<!doctype html>
<html dir="<?=$dir?>">
<head>
  <meta charset="utf-8">
  <!--[if IE]><![endif]-->

  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <title><?=T_('Setup Guide'), ' - ', T_('Tzivos Hashem Management System')?></title>
  <meta name="description" content="">
  <meta name="author" content="">
  <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;">
  <link rel="shortcut icon" href="/favicon.ico">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">
  <link rel="stylesheet" href="admin_styles.css">
  <style>
      .inner {
          margin-left: 5%;
          margin-right: 5%;
      }
  </style>
</head>
<body>
  <? include('admin_header.php'); ?>

<script>
$(function() {
    $('div.inner').hide();
    $('h3').not(".inner").click( function() {
        $(this).next().children(".innerP").hide();
        $(this).next("div.inner").slideToggle('fast');
        $(this).parents('li').toggleClass('open');
    });
    $('div.inner h3').click( function() {
        $(this).next(".innerP").slideToggle('fast');
    });
});
</script>

  <div id="container">
    <header>
		<h1>Tzivos Hashem <?=$year?> - Setup Guide</h1>
    </header>
    
    <div id="main">
                    <div class="module" id="module-info">
                        <div class="module_content">
                        	<p>This guide will walk you through all the necessary steps to get your base up and running.<br />
                                After completing each step of the guide, please return to the Setup Guide to continue the steps.</p>
                        </div>
                    </div>	
                    			
                    <div class="module" id="module-alerts">
                        <div class="module_content list_expand">
                            <ul>
                                <li>
                                    <h3><span class="icon"></span>Uploading and updating your school roster</h3>
                                    <div class="inner">
                                        <h3><span class="icon"></span>Step 1: NEW SCHOOLS ONLY - Create your Classes</h3>
                                        <div class="innerP">
                                            <p>Fill in the Grade, Sub Grade, Teacher, Teacher's Email and Teacher's Cell Phone for each class.</p>
                                            <p><a href="http://mashpia.com/admin_class.php?action=add">Create your Classes</a></p>
                                        </div>
                                        <h3><span class="icon"></span>Step 2: NEW SCHOOLS ONLY - Upload Class Lists</h3>
                                        <div class="innerP">
                                            <p>1. Download the template spreadsheet file and fill in all the mandatory fields (marked by a *).</p>
                                            <p>Please Note: You MUST fill in the First Name, Last Name, English Date of Birth, Gender, and Mission Type fields of each student filled out.</p>
                                            <p>2. Upload your class list in the template spreadsheet file and your data will be automatically entered.</p>
                                            <p><a href="admin_school_file.php">Upload class lists</a></p> 
                                        </div>
										<h3><span class="icon"></span>Step 1: PRE-EXISTING SCHOOLS ONLY - Manage / Add New Classes</h3>
                                        <div class="innerP">
                                            <p>Fill in the Grade, Sub Grade, Teacher, Teacher's Email and Teacher's Cell Phone for each class.</p>
                                            <p><a href="http://mashpia.com/admin_class.php">Add New Classes for this year</a></p>
                                        </div>
                                        <h3><span class="icon"></span>Step 2: PRE-EXISTING SCHOOLS ONLY - Upload new Class List</h3>
                                        <div class="innerP">
                                            <p>1. Download the template spreadsheet file and fill in all the mandatory fields (marked by a *).</p>
                                            <p>Please Note: You MUST fill in the First Name, Last Name, English Date of Birth, Gender, and Mission Type fields of each student filled out.</p>
                                            <p>Please make sure to upload NEW students only!</p>
                                            <p>(If you only have a few new students to enter, you can do so manually by going to "Base Management=>Students=>Add Individual").</p>
                                            <p>2. Upload your list in the template spreadsheet file and your data will be automatically entered.</p>
                                            <p><a href="admin_school_file.php">Upload Latest School/Class List</a></p>
                                        </div>
                                        <h3><span class="icon"></span>Step 3: PRE-EXISTING SCHOOLS ONLY - Platoon transition</h3>
                                        <div class="innerP">
                                            <p>Pre-existing schools will need to update platoon (class) information (change teachers and students to their new classes for the new year)</p>
                                            <p><a href="admin_class_transition.php">Platoon Transition</a></p>
                                        </div>
                                    </div>
                                </li>
                                <!--
                                <li>
                                    <h3><span class="icon"></span>Chayol Recruitment</h3>
                                    <div class="inner">
                                        <h3><span class="icon"></span>Step 1: Order more brochures / posters</h3>
                                        <div class="innerP">
                                            <p> If you need more registration brochures / posters click <a href="order_form.php">here</a> to order.</p>
                                        </div>
                                        <h3><span class="icon"></span>Step 2: Print letter to Parents on school stationery</h3>
                                        <div class="innerP">
                                            <p>Parents have the ability to create an account where they can register their children, print and mark their child’s missions. 
                                            However, to add their children to their account they need their child’s 20-digit code.</p>
                                            <p>Use the following link to generate a letter to be printed for each child to bring home that includes the child’s 20 digit code.</p>
                                            <p>If it does not fit as is on your school stationary then select all (Ctrl A) then copy (Ctrl C) then paste in a word file (Ctrl V) and modify accordingly.</p>
                                            <p><a href="parent_children_barcodes.php">Print Parent Invitations</a></p>
                                        </div>
                                        <h3><span class="icon"></span>Step 3: Download and Print Registration Form for Students</h3>
                                        <div class="innerP">
                                            <p>Click <a href="downloads/Registration Brochure 5775 LR.pdf">here</a> to download form.</p>
                                        </div>
                                        <h3>Step 4: Send the Parents letter and the brochures home to parents with the kids</h3>
                                        <h3>Step 5: Hang up recruitment posters around school</h3>
                                    </div>
                                </li>
                                -->
                                <li>
                                    <h3><span class="icon"></span>Chayol Registration</h3>
                                    <div class="inner">
                                        <h3><span class="icon"></span>Step 1: Get teachers to make sure that ALL their students register by Chof Gimmel Elul.</h3>
                                        <div class="innerP">
                                            <p><a href="teachers_letter.php">Print letter to teachers</a></p>
                                            <p><a href="unregistered_report.php">Print list of unregistered students (to be given out to the teachers)</a></p>
                                        </div>
                                        <h3><span class="icon"></span>Step 2: Register Students for <?=$year?></h3>
                                        <div class="innerP">
                                            <p>Students added to the system are not registered for this years program.
                                            Use this page to register students in this years program.</p>
                                            <p>The registration fee for <?=$year?> is $55. Please take advantage of the early bird special by registering before Chof Gimmel Elul (September 14)
											and pay as low as $50 per child. (or $45 if part of tuition).
											</p>
                                            <p><a href="admin_users_register.php">Register Students</a></p>
                                        </div>
                                        <h3><span class="icon"></span>Step 3: Upload Student Photos</h3>
                                        <div class="innerP">
                                            <p>Students will see their photo when logged in to their parent account.</p>					
                                            <p>Use this page to upload photos for new students and update photos for existing students.</p>
                                            <p><a href="admin_users_photo.php">Upload Student Photos</a></p>
                                        </div>
										<h3><span class="icon"></span>Step 4: Create Parent Accounts</h3>
                                        <div class="innerP">
                                            <p>Click <a href="child_list.php">here</a> to create parent accounts.</p>
                                        </div>
                                    </div>
                                </li>
                                <!--
                                <li>
                                    <h3><span class="icon"></span>Missions</h3>
                                    <div class="inner">
                                        <h3>Step 1: Summer Missions Data Entry</h3>
                                        <div class="innerP">
                                        	<p>Please make sure to collect the summer missions as soon the children come back from school.</p>
                                        </div>
                                        <h3>Step 2: Print Tishrei Missions before Yud Zayin Elul</h3>
                                        <div class="innerP">
                                            <p>On Friday, Yud Zayin Elul your students will be unregistered. You will no longer be able to print or mark missions until they are registered again. 
                                            	Please print out Tishrei missions before the children are unregistered.</p>
                                        </div>
                                    </div>
                                </li>
                                <!--
                                <li>
                                    <h3><span class="icon"></span>Upload Latest School/Class List</h3>
                                    <p>Download the template spreadsheet file.</p>
                                    <p>Upload your list in the template spreadsheet file and your data will be automatically entered.</p>
									<p style="color:red">Please make sure to upload NEW students only!</p>
									<p>(If you only have a few new students to enter, you can do so manually by going to "Base Management=>Students=>Add Individual").</p>
                                    <p><a href="admin_school_file.php">Upload Latest School/Class List</a></p>
                                </li>
                                <li>
                                	<h3><span class="icon"></span>Platoon Transition (Pre-existing Schools Only)</h3>
                                    <p>Pre-existing schools will need to update platoon (class) information.</p>
                                    <p><a href="admin_class_transition.php">Platoon Transition</a></p>
                                </li>
                                <li>
                                	<h3><span class="icon"></span>Print Parent Invitations</h3>
                                    <p>Parents have the ability to create an account and associate their account with their children.</p>
                                    <p>To associate their account they need a 20-digit code (also the child's barcode) for each child.</p>
                                    <p>Once their account is associated they can register their children in this years program.</p>
                                    <p>Use this link to generate a letter to be printed for each child to bring home.</p>
                                    <p><a href="parent_children_barcodes.php">Print Parent Invitations</a></p>
                                </li>
                                <li>
                                	<h3><span class="icon"></span>Upload Student Photos</h3>
                                    <p>If your school has a kiosk students will see their photo when they log in.</p>
					 <p>Use this page to upload photos for new students and update photos for existing students.</p>
                                    <p><a href="admin_users_photo.php">Upload Student Photos</a></p>
                                </li>
                                <li>
                                	<h3><span class="icon"></span>Register Students for 5774</h3>
                                    <p>Students added to the system are not regsitered for this years program.</p>
                                    <p>Use this page to register students in this years program.</p>
                                    <p><a href="admin_users_register.php">Register Students</a></p>
                                </li>
				<!--
                                <li>
                                	<h3><span class="icon"></span>Enroll School to Campaigns</h3>
                                    <p>Enroll your school to the campaigns you want available in your school.</p>
                                    <p><a href="admin_school_subjects.php">Enroll School to Campaigns</a></p>
                                </li>
                                <li>
                                	<h3><span class="icon"></span>Enroll Students to Campaigns</h3>
                                    <p>When a school enroll to a campaign it becomes available to students.</p>
                                    <p>Enroll students to available campaigns.</p>
                                    <p><a href="admin_users_subject.php">Enroll Students to Campaigns</a></p>
                                </li>
                                <li>
                               	<h3><span class="icon"></span>Put Students on Correct Ladders</h3>
                                  	<p>Each campaign consists of missions with different levels of difficulty known as ladders.</p>
                                  	<p>Make sure to place each student on the correct ladder.</p>
                                   <p><a href="admin_users_track.php">Put Students on Correct Ladders</a></p>
                                </li>
                                -->
                            </ul>
                        </div>
                    </div>					
    </div>
  </div> <!-- end of #container -->
  <? include('admin_footer.php'); ?>

  
</body>
</html>