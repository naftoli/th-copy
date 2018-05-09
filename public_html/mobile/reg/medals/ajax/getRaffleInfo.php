<?php
require_once( dirname(__FILE__) . '/../../../../db.php' );
$user = mysql_real_escape_string( $_POST['user_id'] );

require_once( dirname(__FILE__) . '/../../../../raffles/yearly/classes/YearlyRaffle.php');
require_once( dirname(__FILE__) . '/../../../../raffles/shared/classes/Constants.php');
use raffles\yearly\YearlyRaffle as YearlyRaffle; // use the raffle class from its namespace
use raffles\shared\Constants as Constants;

// setup some functions for checking weekly/monthly/yearly raffle eligibility
function checkYearly( $user_id ) {
	$yearly_raffle = new YearlyRaffle();
	$yearly_raffle->set_user_eligibility( $user_id );
	$numTasks = $yearly_raffle->eligibility[ $user_id ];
	
	// send them a message with how many days left/done
	if ($numTasks >= 160) {
		$msg = "<span style='color: #004E22'>Eligible for end of year raffle</span>";
	} else {
		$msg = (160 - intval($numTasks)) . " days of tasks needed for end of year raffle";
	}
	return $msg;
}

function checkMonthly( $user_id ) {
	// find out current dates
	$dates = getDates( 'monthly' );
	$total = checkDaily( $user_id, $dates );
	$required = Constants::get_monthly_task_requirment();
	
	if ($total >= $required - 4) { // if it is between 20 and 16 we can check each week to see if we get 20.
		$start_date = $dates['start_date']; // default to this start date
		$end_date = $dates['start_date'] + 6; // get the end date for the first week
		// TODO, iterate and check for additional marks...
		$i = 0; // set $i to 0
		while ($total < 20 && $i < 4) { // while we are still below 20 and have not checked all 4 weeks yet.
			$update_total_sql = "SELECT COUNT(*) AS `total` FROM date_tasks dt JOIN date_tasks_marks dtmarks USING (date_task_id) WHERE dtmarks.user_id = $user_id
				AND dtmarks.mark_date >= $start_date AND dtmarks.mark_date <= $end_date AND daily_task = 0
				AND ((dt.quantity IS NOT NULL AND dtmarks.done_qty >= dt.quantity) OR dt.quantity IS NULL)";
			$update_total_query = mysql_query($update_total_sql);
			$update_total_row = mysql_fetch_assoc($update_total_query);
			if ($update_total_row['total'] > 0) $total++; // if the total from the query is greater then 0, add one more "day"
			
			// cleanup
			$i++; // increment $i
			$start_date = $end_date + 1; // go to the next day for the next start date
			$end_date = $start_date + 6; // get the end date for the first week			
		}
	}
	
	if ($total == $required) {
		$msg = "<span style='color: #004E22'>Eligible for " . $dates['name'] . " raffle</span>";
	} else {
		$msg = ($required - $total) . " days of tasks needed for " . $dates['name'] . " raffle";
	}
	return $msg;
}

function checkWeekly( $user_id ) {
	// find out current dates
	$dates = getDates( 'weekly' );
	$total = checkDaily( $user_id, $dates );
	$required = Constants::get_weekly_task_requirment();
	
	if ($total == $required - 1) { // if it is only (4) we can check for some marks that are not tied to any specific dates
		// get a total count of all the non daily missions marked between the start and end dates of this raffle
		$update_total_sql = "SELECT COUNT(*) AS `total` FROM date_tasks dt JOIN date_tasks_marks dtmarks USING (date_task_id) WHERE dtmarks.user_id = $user_id".
				" AND dtmarks.mark_date >= ". $dates['start_date'] ." AND dtmarks.mark_date <= ". $dates['end_date'] .
				" AND daily_task = 0 AND ((dt.quantity IS NOT NULL AND dtmarks.done_qty >= dt.quantity) OR dt.quantity IS NULL)";
		$update_total_query = mysql_query($update_total_sql);
		$update_total_row = mysql_fetch_assoc($update_total_query);
		// if the user did at least one task then add him to the list (as it brings his total from 4 to 5)
		if($update_total_row['total'] > 0) {
			$total = 5; // set the total to 5
		}
	}
	
	if ($total == $required) {
		$msg = "<span style='color: #004E22'>Eligible for פרשת " .  $dates['name'] . " raffle</span>";
	} else {
		$msg = ($required - $total) . " days of tasks needed for " .  $dates['name'] . " raffle";
	}
	return $msg;
}

function checkDaily( $user_id, $dates ) {
	$daily_sql = 'select dtmarks.mark_date from user_tracks ut'.
				' join date_tasks_missions dtm on ut.level = dtm.level and ut.track_id = dtm.track_id and ut.subject_id = dtm.subject_id'.
				' join date_tasks dt using (date_tasks_mission_id) join date_tasks_marks dtmarks using (date_task_id)'.
				' where dtmarks.user_id = '.$user_id.' and ut.user_id = '.$user_id. ' and dt.daily_task = 1'.
				' and dtmarks.mark_date >= '. $dates['start'] .' and dtmarks.mark_date <= ' . $dates['end'] .
				' group by dtmarks.mark_date';
	//echo $daily_sql."\n"; // if you want to debug...
	$daily_query = mysql_query($daily_sql); // run the query
	$total = mysql_num_rows( $daily_query ); // get the number of marks
	return $total;
}

function getDates( $type ) {
	$today = unixtojd();
	$sql = "select start_date, end_date, name from raffles
			where type = '" . $type . "'
			and start_date <= " . $today . "
			and end_date >= " . $today;
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	return array(
		'start'	=>	$row['start_date'],
        'end'	=>	$row['end_date'], 
        'name'  =>  $row['name']
	);
}

// check raffle eligibilities
$yearly = checkYearly( $user );
$monthly = checkMonthly( $user );
$weekly = checkWeekly( $user );
echo $weekly . "<br />" . $monthly . "<br />" . $yearly;
?>