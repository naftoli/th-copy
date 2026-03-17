<?php
ini_set('display_errors',1);
$admin_auth = array(); 
require('header.php');
require 'class.globalSettings.php';
$regYear = GlobalSettings::getRegistrationYear();
$last_year = $regYear - 1;

//$date = cal_from_jd(unixtojd(), CAL_JEWISH);
$schools = implode(',', array_merge(array(-1), array_filter(gra('school_id'), 'is_numeric')));

function registerPreRegisteredStudentsForYear() {
	global $MASHPIA_DB, $regYear, $schools;

	$db = $MASHPIA_DB;
    $added = 0;

    $sqlUsers = "SELECT user_id, reg_date, paid 
                 FROM user_registration  
                 WHERE year = :year
				 AND school_id IN ($schools)";
    $stmtUsers = $db->prepare($sqlUsers);
    $stmtUsers->execute([':year' => $regYear]);

    while ($rowUser = $stmtUsers->fetch(PDO::FETCH_ASSOC)) {
        $sql = "UPDATE users 
                SET user_registered = :reg_date,
                    user_registration_fee = :paid
                WHERE user_id = :user_id";
        $stmtUpdate = $db->prepare($sql);
        if ($stmtUpdate->execute([
            ':reg_date' => $rowUser['reg_date'],
            ':paid'     => $rowUser['paid'],
            ':user_id'  => $rowUser['user_id'],
        ])) {
            $added++;
            $user = $soldierFactory($rowUser['user_id']);
            $user->enrollInCampaigns();
            $user->setupBirthdayMissions(false);
            $user->moveMarksFromArchive();
        }
    }

    return $added;
}

if (gr('class_era')) {
	// mq("UPDATE classes SET class_era = " . $year . " WHERE class_era = 0 AND school_id IN ($schools)");
	
	// // automatically create new classes based on last years classes
	// require_once 'class.gradeCreation.php';
	// $g = new GradeCreation( $schools );
	// $msg = '';
	// if (!$g->createGrades()) {
	// 	$msg = "Error creating new classes for new year.";
	// }
	
	// $message = sprintf(T_('%d classes marked as year %d.'), mysql_affected_rows(), $last_year);
	// if ($msg) $message .= "<br />" . $msg;
}  
elseif (gr('school_era')) {
	mq("UPDATE schools SET 
				school_era = " . $last_year . ",
				package_id = NULL,
				add_on_one  = 0,  
				add_on_two  = 0, 
				store_reset = null 				
				WHERE school_era IS NULL AND school_id IN ($schools)");

	mq("UPDATE classes SET 
				updated = 0 
				WHERE class_era = 0 AND school_id IN ($schools)");

	// update the siddur gift
	mq("UPDATE schools SET siddur_gift = 0 WHERE school_id IN ($schools)");
				
	$message = sprintf(T_('%d schools marked as year %d.'), mysql_affected_rows(), $last_year);
} 
elseif (gr('user_registered')) {
	mq("UPDATE users SET 	user_registered = NULL, 
							user_registration_fee = NULL, 
							add_on_one = 0,
							add_on_two = 0
							WHERE school_id IN ($schools)");
	$num = mysql_affected_rows();

	// register pre-registered students
	$added = registerPreRegisteredStudentsForYear();
							
	$message = sprintf(T_('%d users de-registered. %d users re-registered from pre-registration.'), $num, $added);
}
else if (gr('eligibility')) {
    require $_SERVER['DOCUMENT_ROOT'] . '/tasks/setChayoleiChidonEligibility.php';
    if (updateChayolei() && updateChidon()) {
        $message = "Chayolei and Chidon eligibility was set.";
    }
}
elseif(gr('user_tracks')) {
	// mq("UPDATE user_tracks JOIN users USING (user_id) SET enrolled = 0 WHERE school_id IN ($schools)");
	// $message = sprintf(T_('%d user-subjects un-enrolled'), mysql_affected_rows());
	// mq('DELETE FROM user_tracks WHERE track_id IS NULL AND level IS NULL AND enrolled = 0');
} 
elseif(gr('tanya_year')) {
	// mq("UPDATE tanya_users JOIN users USING (user_id) SET year = year + 1 WHERE school_id IN ($schools) AND year < 8 AND lines_done - lines_offset > 0");
	// $message = sprintf(T_('%d tanya years changed'), mysql_affected_rows());
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
	<HEAD>
		<TITLE><?=T_('End of year tasks'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<style>
			body {
				line-height: 1.3;
			}
			input[type='submit'] {
				padding: 10px;
				border-radius: 5px;
				font-size: 14px;
				margin: 10px 0;
			}
		</style>
	</HEAD>
	
	<BODY>
		<? include('admin_header.php'); ?>
		
		<DIV CLASS="body">
		
			<H1><?=T_('End of year tasks')?></H1>
			
			<? if (!empty($message)) : ?>
				<H2><?=$message?></H2>
			<? endif; ?>

			<FORM action="admin_yearly.php" method="post" accept-charset="UTF-8">
				<P>
					<? $result = mq('SELECT school_name, school_id FROM schools where chayolei = 1 OR chidon = 1 ORDER BY school_name'); ?>
					<? while($row = mysql_fetch_assoc($result)): ?>
						<? if (empty($row['school_name'])) continue; ?>
						<?php 
						if (isset($_GET['schools']) && $_GET['schools'] != '') {
							$schools_array = explode(',', $_GET['schools']);
							if (!in_array($row['school_id'], $schools_array)) {
								continue;
							}
						}
						?>
						<LABEL><INPUT type="checkbox" name="school_id[]" value="<?=$row['school_id']?>"> <?=es($row['school_name'])?></LABEL><BR>
					<? endwhile; ?>
					
					<P><?=T_('For the selected school')?>:<BR>
					
					<!-- <INPUT type="submit" name="class_era" value="Mark classes as last year">
					<BR> - <?=T_('Or')?> - <BR> -->
					<INPUT type="submit" name="school_era" value="Mark the school as last year">
					<BR> - <?=T_('Or')?> - <BR>
					<INPUT type="submit" name="user_registered" value="Un-register students">
                    <br />
                    <?= T_("Or") ?>
                    <br />
                    <input type="submit" name="eligibility" value="Set student Eligibility" />
					<!--
					<BR> - <?=T_('Or')?> - <BR>
					<INPUT type="submit" name="user_tracks" value="Un-enroll students from subjects">
					<BR> - <?=T_('Or')?> - <BR>
					<INPUT type="submit" name="tanya_year" value="Add 1 to tanya year">
					-->
				</P>
			</FORM>

		</DIV>
		
		<? include('admin_footer.php'); ?>
		
	</BODY>
	
</HTML>
