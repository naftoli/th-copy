<?php 
include_once ("../header.php");
require_once('../file_save.php');
include_once('../calendar.php');
$title ='Campaign Goal';
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
$campaign_row = mysql_fetch_assoc(mq("SELECT subject_id, subject_gold_image_id, subject_slogan, subject_description, subject_commitments FROM subjects WHERE subject_name = '$subject_name' "));

?>


<body class="blue">
<!--<?=$user['user_id']."|".$campaign_row["subject_id"]?>
    --><div id="wrapper">
        <div id="header">
          <?php include("includes/topbar.php"); ?>
        </div>
        <div id="main">
            <div id="page_title"><?=T_($subject_name)?></div>
            <div class="one_column padding_left campaign_main">
                <div class="medalImage"><?=linkImgFile($campaign_row['subject_gold_image_id'], 200, 210);?></div>
                <!--<div class="blockquote">
                	--><?=T_($campaign_row["subject_slogan"])?>
                    <!--<span>-</span>
                </div>
            --></div>
            <div class="one_column padding_top">
            	<div class="goal">
                	<div class="title icon_target">
                    	Goal
                    </div>
                    <div class="text">
                    	<?=T_($campaign_row["subject_description"])?>
                    </div>
                </div>
                <div class="button button_icons">
                    <div>
                      <? $row = mysql_fetch_assoc(mq("SELECT enrolled FROM user_tracks WHERE user_id = {$user['user_id']} AND subject_id = {$campaign_row['subject_id']}")); ?>
                      <? if($row && $row['enrolled']):?>
                        <a href="../missions.php" style="padding-left:20px;">Current Missions</a> 
                      <? else: ?>
                        <a href="camp_enroll_1.php?subject=<?=$subject_name?>" class="icon_enroll">Enroll</a>
                      <? endif;?>
                    </div>
                </div>
            </div>
            <div class="one_column">
                <ul class="buttons button_icons">
<!--
                    <li><a href="camp_overview.php?subject=<?=$subject_name?>" class="icon_ranks">Overview</a></li>
                    <li><a href="camp_overview.php?p=3&subject=<?=$subject_name?>" class="icon_medals">Medals</a></li>
                    <li><a href="camp_overview.php?p=4&subject=<?=$subject_name?>" class="icon_miles">Miles</a></li>
                    <li><a href="camp_overview.php?p=6&subject=<?=$subject_name?>" class="icon_ladders">Ladders</a></li>
-->
                </ul>
            </div>
        </div>
        <div id="footer">
			<?php include("includes/bottombar.php"); ?>
      </div>
    </div>
</body>

<?php include("includes/footer.php"); ?>
