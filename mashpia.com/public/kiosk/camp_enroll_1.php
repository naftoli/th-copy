<?php 
include_once ("../header.php");
require_once('../file_save.php');
$title ='Campaign Enrol';
$user_row = mysql_fetch_assoc(mq("
SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state, user_postal, user_country, user_phone,
       user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id,
       team_name, school_name, school_number, school_city, school_state, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, rank_name, rank_image_id, rank_color
FROM users
     LEFT JOIN schools USING (school_id)
     LEFT JOIN institutions USING (inst_id)
     LEFT JOIN classes USING (school_id, class_id)
     LEFT JOIN teams USING (school_id, team_id)
     LEFT JOIN (SELECT user_id, MAX(rank_ord) rank_ord FROM rank_marks WHERE user_id = {$user['user_id']} GROUP BY user_id) rank USING (user_id)
     LEFT JOIN ranks USING (rank_ord)
WHERE user_id = {$user['user_id']}
ORDER BY class_grade, class_sub, last, first
"));
include("includes/header.php"); ?>

<?php 
include("includes/slider.php"); 
include("includes/checkbox.php"); 
$subject_name = gr("subject", "");
?>

<body class="blue">

    <div id="wrapper">
        <div id="header">
          <?php include("includes/topbar.php"); ?>
        </div>
        <div id="main">
            <div id="page_title">Enrollment</div>
            <div class="three_column padding_top">
              <div class="content">
                    <div id="slider">
                      <ul class="enroll">
                            <li>
                                <div class="slider_title">Parent's Commitment</div>
                                <div class="scroll-pane">
                                    <form>
                                	<div class="mainbox">
                                    	<div class="col2_image iconl_parents"></div>
                                        <div class="col2_text">My Parent's have commited, signed and given me permission to join this campaign.</div>
                                        <div class="clear"></div>
                                        <div class="buttons_bar">
                                            <div class="button button_icons">
                                                <div><a href="campaigns.php" class="icon_cancel">Cancel</a></div>
                                            </div>
                                            <div class="button button_icons">
                                                <div><a href="camp_enroll_2.php?subject=<?=$subject_name?>" class="icon_check">Next</a></div>
                                            </div>
                                        </div>
                                    </div>
                                    </form>
                                </div>
                            </li>
                      </ul>
                    </div>
              </div>
            </div>
        </div>
        <div id="footer">
			<?php include("includes/bottombar.php"); ?>
      </div>
    </div>
</body>

<?php include("includes/footer.php"); ?>
