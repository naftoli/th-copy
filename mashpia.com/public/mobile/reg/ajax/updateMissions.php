<?php
chdir('../../../');
require 'db.php';
$user_id = mysql_real_escape_string( $_POST['user_id'] );

require_once("classes/mission_marks_updater.php");
require_once("classes/rank_updater.php");
require_once("classes/medal_updater.php");
require_once 'class.globalSettings.php';

$subjects = array();
$sql = "select subject_id from user_tracks where enrolled = 1 and user_id = " . $user_id;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $subjects[] = $row['subject_id'];
}

$start = GlobalSettings::getCurYearDates()['start'];
$mm = new mission_marks_updater();
foreach ($subjects as $subject) $mm->mission_marks_update_by_subject_id($user_id, $subject, $start);

//$medal_updater = new medal_updater();
//$medal_updater->update_medal_two($user_id);
//
//$rank_updater = new rank_updater();
//$rank_updater->update_rank_two($user_id);