<?php
ini_set('display_errors',1);
$admin_auth = array(); 
require('header.php');
require 'class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();
$year--;

//$date = cal_from_jd(unixtojd(), CAL_JEWISH);
$schools = implode(',', array_merge(array(-1), array_filter(gra('school_id'), 'is_numeric')));

if (gr('class_era')) {
	// mq("UPDATE classes SET class_era = " . $year . " WHERE class_era = 0 AND school_id IN ($schools)");
	
	// // automatically create new classes based on last years classes
	// require_once 'class.gradeCreation.php';
	// $g = new GradeCreation( $schools );
	// $msg = '';
	// if (!$g->createGrades()) {
	// 	$msg = "Error creating new classes for new year.";
	// }
	
	// $message = sprintf(T_('%d classes marked as year %d.'), mysql_affected_rows(), $year);
	// if ($msg) $message .= "<br />" . $msg;
}  
elseif (gr('school_era')) {
	mq("UPDATE schools SET 
				school_era = " . $year . ",
				package_id = NULL,
				add_on_one  = 0,  
				add_on_two  = 0				
				WHERE school_era IS NULL AND school_id IN ($schools)");
				
	$message = sprintf(T_('%d schools marked as year %d.'), mysql_affected_rows(), $year);
} 
elseif (gr('user_registered')) {	
	mq("UPDATE users SET 	user_registered = NULL, 
							user_registration_fee = NULL, 
							add_on_one = 0,
							add_on_two = 0
							WHERE school_id IN ($schools)");
							
	$message = sprintf(T_('%d users de-registered.'), mysql_affected_rows());
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
					<? $result = mq('SELECT school_name, school_id FROM schools where chayolei = 1 ORDER BY school_name'); ?>
					<? while($row = mysql_fetch_assoc($result)): ?>
						<LABEL><INPUT type="checkbox" name="school_id[]" value="<?=$row['school_id']?>"> <?=es($row['school_name'])?></LABEL><BR>
					<? endwhile; ?>
					
					<P><?=T_('For the selected school')?>:<BR>
					
					<!-- <INPUT type="submit" name="class_era" value="Mark classes as last year">
					<BR> - <?=T_('Or')?> - <BR> -->
					<INPUT type="submit" name="school_era" value="Mark the school as last year">
					<BR> - <?=T_('Or')?> - <BR>
					<INPUT type="submit" name="user_registered" value="Un-register students">
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
