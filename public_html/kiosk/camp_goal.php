<?php
include_once ("../header.php");
require_once('../file_save.php');
include_once('../calendar.php');
$title ='Campaign Goal';
$user_row = mysql_fetch_assoc(mq("
SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2, dob,
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

/* Tanya injection: Start
 * This is a temporary insertion of the tanya campaign I (andy) developed separately from any company infrastructure.
 */
// Enable tanya injection
if ($_GET["i"] == "2b3ec3fbc6ff09833c2172bd040487ce") {
	$strTanyaTitle = "
		<div class=\"org_photo\">" . (!is_null($user_row['school_logo_id']) ? linkImgFile($user_row['school_logo_id'],100,100) : '') . "</div>
			Base: #" . $user_row['school_number'] . "<br>
			" . $user_row['school_name'] . "<br>
			" . $user_row['rank_name'].' '.$user_row['first'].' '.$user_row['last'] . "
		</div>
	";
	if (@$_GET["tanya_demo"]=="true") {
		setcookie("Tanya", "true");
		print "<font color='black'>Tanya::Demo has been enabled, thanks for testing!<br><a href='./camp_goal.php?subject=" . $_GET["subject"] . "&i=" . $_GET["i"] . "'>Continue</a></font>";
		exit;
	}
	if (@$_COOKIE["Tanya"] == "true") {
		require("./campaigns/tanya/interface/demo.php");
		exit;
	}
}
// Tanya injection: End


include("includes/header.php");
include("includes/slider.php");
include("includes/checkbox.php");
$subject_name = gr("subject", "");
$sql = "SELECT subject_id, subject_gold_image_id, subject_slogan, subject_description, subject_commitments FROM subjects WHERE subject_name='" . $subject_name . "'";
$campaign_row = mysql_fetch_assoc(mq("SELECT subject_id, subject_gold_image_id, subject_slogan, subject_description, subject_commitments FROM subjects WHERE subject_name = '$subject_name' "));
$subject_id = $campaign_row["subject_id"];

$enrolled_row = mysql_fetch_assoc(mq("SELECT enrolled FROM user_tracks WHERE user_id = {$user['user_id']} and subject_id = {$campaign_row['subject_id']}"));
$enrolled = ($enrolled_row["enrolled"]==1);

/*$now_jd = unixtojd();
$hdate_arr = dateToHebrewSplit($now_jd);
$hdate = $hdate_arr[0].' '.$hdate_arr[1].' '.$hdate_arr[2];*/
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
<!-- GC March 5 2010
                <div class="button button_icons">
                    <div><a href="camp_enroll_1.php?subject=<?// GC March 5 2010 =$subject_name?>&c=<?// GC March 5 2010 =$subject_id?>" class="icon_enroll">Enroll</a></div>
                </div>

				<div class="button button_icons">
					<div><a class="icon_missions" href="mission.php">Current Missions</a></div>
				</div>
GC March 5 2010 -->
            </div>
            <div class="one_column">
                <ul class="buttons button_icons">

					<? 
					$types = array(2,3,12,13);
					if (in_array($user_row["school_type_id"], $types)): 
					?>

						<?php if ($campaign_row["subject_id"] <> 27): ?>
							<li>
								<a href="camp_overview.php?subject=<?=$subject_name?>&c=<?=$subject_id?>" class="icon_ranks">Overview</a>
							</li>
						<?php endif; ?>

						<?php if ($enrolled == 1): ?>
<!--							<li>
								<a class="icon_back_to" href="../missions.php">View Current Missions</a>
							</li>
-->						<?php else : ?>
							<li>
								<a href="camp_enroll_1.php?subject=<?=$subject_name?>&c=<?=$subject_id?>" class="icon_enroll">Enroll</a>
							</li>
						<?php endif; ?>

						<li>
							<a class="icon_medals" href="prof_medals.php?subject_id=<?=$campaign_row["subject_id"]?>">Medals</a>
						</li>

					<? endif; ?>

                    <!-- li><a href="camp_overview.php?p=3&subject=<?=$subject_name?>&c=<?=$subject_id?>" class="icon_medals">Medals</a></li>
                    <li><a href="camp_overview.php?p=4&subject=<?=$subject_name?>&c=<?=$subject_id?>" class="icon_miles">Miles</a></li>
                    <li><a href="camp_overview.php?p=6&subject=<?=$subject_name?>&c=<?=$subject_id?>" class="icon_ladders">Ladders</a></li -->
                </ul>
            </div>
        </div>
        <div id="footer">
			<?php include("includes/bottombar.php"); ?>
      </div>
    </div>

	<input type="hidden" id="enrolled" value="<?php echo $enrolled; ?>" />
</body>

<?php include("includes/footer.php"); ?>
