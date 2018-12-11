<?php 
$admin_auth = array('school','class'); 
require('header.php');
//include("check_admin_id.php");
?>
<!doctype html>
<html dir="<?=$dir?>">
<head>
  <meta charset="utf-8">
  <!--[if IE]><![endif]-->

  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <title><?=T_('5773 Setup Guide'), ' - ', T_('Tzivos Hashem Management System')?></title>
  <meta name="description" content="">
  <meta name="author" content="">
  <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;">
  <link rel="shortcut icon" href="/favicon.ico">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">
  <link rel="stylesheet" href="admin_styles.css">

</head>
<body>
  <? include('admin_header.php'); ?>

<script>
$(function() {
    $('.slider:last .module#module-alerts li h3').nextAll().hide();
    $('.slider:last .module#module-alerts li h3').click(function(){
        $(this).nextAll().slideToggle('fast');
        $(this).parents('li').toggleClass('open');
    });
});
</script>

  <div id="container">
    <header>
        <h1><?=T_('Tzivos Hashem 5773 - Setup Guide')?></h1>
    </header>
    
    <div id="main">
                    <div class="module" id="module-info">
                        <div class="module_content">
                            <p>This guide will walk you through all the necessary steps to get your base up and running.<br />
                            After completing each step of the guide, please return to the Setup Guide to continue the steps.</p>
                        </div>
                    </div>  
                                
                    <div class="module" id="module-alerts">
                        <div class="module_content list_expand numbered">
                            <ul>
                                <li>
                                    <h3><span class="icon"></span>NEW SCHOOLS ONLY - Create your Classes</h3>
                                    <p>Fill in the Grade, Sub Grade, Teacher and Default Year for each class.</p>
                                    <p><a href="http://mashpia.com/admin_class.php">Create your Classes</a></p>
                                </li>
                                <li>
                                    <h3><span class="icon"></span>Upload Latest School/Class List</h3>
                                    <p>Download the template spreadsheet file.</p>
                                    <p>Upload your list in the template spreadsheet file and your data will be automatically entered.</p>
                                    <p style="color:red">Please make sure to upload NEW students only!</p>
                                    <p>(If you only have a few new students to enter, you can do so manually by going to "Base Management=>Students=>Add Individual").</p>
                                    <p><a href="admin_school_file.php">Upload Latest School/Class List</a></p>
                                </li>
                                <!--
                                <li>
                                    <h3><span class="icon"></span>Platoon Transition (Pre-existing Schools Only)</h3>
                                    <p>Pre-existing schools will need to update platoon (class) information.</p>
                                    <p><a href="admin_class_transition.php">Platoon Transition</a></p>
                                </li>
                                -->
                                <li>
                                    <h3><span class="icon"></span>Print Parent Invitations</h3>
                                    <p>Parents have the ability to create an account and associate their account with their children.</p>
                                    <p>To associate their account they need a 20-digit code (also the child's barcode) for each child.</p>
                                    <p>Once their account is associated they can register their children in this years program.</p>
                                    <p>Use the following link to generate a letter to be printed for each child to bring home.</p>
                                    <p><a href="parent_children_barcodes.php">Print Parent Invitations</a></p>
                                </li>
                                <li>
                                    <h3><span class="icon"></span>Upload Student Photos</h3>
                                    <p>If your school has a kiosk students will see their photo when they log in.</p>
                                    <p>Use the following link to upload photos for new students and update photos for existing students.</p>
                                    <p><a href="admin_users_photo.php">Upload Student Photos</a></p>
                                </li>
                                <li>
                                    <h3><span class="icon"></span>Setup Store</h3>
                                    <p>Store setup involves setting up your prizes for the store.</p>
                                    <p>Use the following link to setup your store.</p>
                                    <?php
                                        $strV2Path = 'v2';
                                        if (md5($_SERVER["REMOTE_ADDR"]) == 'b80f2403fa99a27a614f07bae5d94917')
                                            $strV2Path = 'v2dev1'
                                    ?>
                                    <p><a href="http://<?php print $strV2Path; ?>.mashpia.com/login/frommashpia/instituiton_type/Institution%20Administrator/user_id/<?=$admin_user['admin_id']==2?56:$admin_user['admin_id'];?>/dashextra/<?php print base64_encode("/index/tstyle/schoolstemplate1"); ?>">Setup Store</a></p>
                                </li>
                                <li>
                                    <h3><span class="icon"></span>Register Students for 5773</h3>
                                    <p>Students added to the system are not regsitered for this years program.</p>
                                    <p>Use the following link to register students in this years program.</p>
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