<?php 

include_once ("../header.php");
require_once('../file_save.php');
$title ='Withdraw points';
include("includes/header.php"); 
$user_row = mysql_fetch_assoc(mq("
SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state, user_postal, user_country, user_phone,
       user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id,
       team_name, school_name, school_number, school_id, school_city, school_state, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, rank_name, rank_image_id, rank_color
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

$subjects_result = mq("SELECT subjects.subject_id, subject_name, subject_type, subjects.subject_black_image_id 
FROM subjects 
JOIN school_subjects ON (subjects.subject_id = school_subjects.subject_id AND school_id = {$user_row['school_id']}) 
WHERE subject_type NOT IN ('school_points', 'home_points') 
ORDER BY subject_name");
/*SELECT subjects.subject_id, subjects.subject_name, subjects.subject_black_image_id
FROM school_subjects, subjects
WHERE
    school_subjects.subject_id = subjects.subject_id AND
    school_subjects.school_id = {$user_row['school_id']}
ORDER BY subjects.subject_id");*/

include("includes/header.php"); 
?>

<body class="blue">
    <div id="wrapper">
        <div id="header">
          <?php include("includes/topbar.php"); ?>
        </div>
        <div id="main">
            <div id="page_title">Choose a Campaign
            </div>
            <div>
                <div class="three_column">
                  <ul class="buttons button_icons">
                  <? while($row = mysql_fetch_assoc($subjects_result))
                  {?>
                        <li style="vertical-align:middle;">
                        <?=linkImgFile($row['subject_black_image_id'],NULL,NULL," style='float:left;margin:20px 0 0 15px;'");?>
                        <a href="camp_goal.php?subject=<?=$row["subject_name"]?>" style="padding-left:20px;"><?=$row["subject_name"]?></a></li>
                 <?}?>
                  </ul>
                </div>
            </div>
        </div>
        <div id="footer">
			<?php include("includes/bottombar.php"); ?>
      </div>
    </div>
</body>

<?php include("includes/footer.php"); ?>
