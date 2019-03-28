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
include("includes/spinner.php"); 
$subject_name = gr("subject", "");
$campaign_row = mysql_fetch_assoc(mq("SELECT subject_id, subject_black_image_id, subject_gold_image_id, subject_slogan, subject_description, subject_commitments FROM subjects WHERE subject_name = '$subject_name' "));
mq("INSERT INTO user_tracks (user_id, subject_id, enrolled) VALUES ({$user['user_id']}, {$campaign_row['subject_id']}, 1) ON DUPLICATE KEY UPDATE enrolled = 1");
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
                                <div class="slider_title">Mazal Tov!</div>
                                <div class="scroll-pane">
                                    <form>
                                	<div class="mainbox">
                                    	<div class="col2_image"><?=linkImgFile($campaign_row['subject_gold_image_id'], 128, 128);?></div>
                                        <div class="col2_text">You are now enrolled in the <?=T_($subject_name)?> Campaign...<br />You can now begin your missions.
                                        </div>
                                        <div class="clear"></div>
                                        <div class="button button_icons">
                                        	<div style="vertical-align:middle;">
                                            <?=linkImgFile($campaign_row['subject_black_image_id'],NULL,NULL," style='float:left;margin:20px 0 0 15px;'");?>
                                            <a href="../missions.php"  style="padding-left:20px;">Current Missions</a>
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
