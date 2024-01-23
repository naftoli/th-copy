<?php
require_once( dirname(__FILE__) . '/../../../../db.php' );
$user = mysql_real_escape_string( $_POST['user_id'] );

require_once( dirname(__FILE__) . '/../../../../raffles/yearly/classes/YearlyRaffle.php');
require_once( dirname(__FILE__) . '/../../../../raffles/shared/classes/Constants.php');
use raffles\yearly\YearlyRaffle as YearlyRaffle; // use the raffle class from its namespace
use raffles\shared\Constants as Constants;

// check raffle eligibilities
$yearly = checkYearly( $user );
$monthly = checkMonthly( $user );
$weekly = checkWeekly( $user );

if ( $weekly ) { ?>
  <div class="progress <?= $weekly['percent_done'] == 100 ? "compleate" : ""?>">
    <div class="progress-bar" role="progressbar" style="width: <?= $weekly['percent_done']?>%;"></div>
    <span ><?= $weekly['msg'] ?></span>
  </div>
<? }
if ( $monthly ) { ?>
  <div class="progress <?= $monthly['percent_done'] == 100 ? "compleate" : ""?>">
    <div class="progress-bar" role="progressbar" style="width: <?= $monthly['percent_done']?>%;"></div>
    <span ><?= $monthly['msg'] ?></span>
  </div>
<? }
if ( $yearly ) { ?>
  <div class="progress <?= $yearly['percent_done'] == 100 ? "compleate" : ""?> <?= $yearly['missed-deadline'] ? "missed-deadline" : ""?>">
    <div class="progress-bar" role="progressbar" style="width: <?= $yearly['percent_done']?>%;"></div>
    <span ><?= $yearly['msg'] ?></span>
  </div>
<? }

/**
 * checkYearly
 * 
 * returns the percent_done and msg for the user_id passed in
 *
 * @param string $user_id
 * @return array/boolean
 */
function checkYearly( $user_id ) {
    $yearly_raffle = new YearlyRaffle;
    $quota = $yearly_raffle->required_days_of_tasks();
    $num_days = $yearly_raffle->set_user_eligibility( $user_id )[ $user_id ];
    
    $raffle_info = formatRaffleInfo( $num_days, $quota, 'end of year', 'yearly' );

    //if ( $yearly_raffle->getEnd() < unixtojd() + 30 ) return false;

    if ( $yearly_raffle->getEnd() < unixtojd() && $num_days < $quota ) {
        return [
            "percent_done" => $raffle_info[ "percent_done" ], 
            "msg" => (isset($_COOKIE['lang']) && $_COOKIE['lang'] == 'he' ?
                "($num_days / $quota<span class='hide-small'> ימים נשלמו</span>) תאריך ההגרלה השנתית עבר" :
                "Yearly Raffle Deadline Passed ($num_days / $quota<span class='hide-small'> days completed</span>)"),
            'missed-deadline' => true
        ];
    } else {
        return $raffle_info;
    }
}

/**
 * checkMonthly
 *
 * returns the percent_done and msg for the user_id passed in
 *
 * @param string $user_id
 * @return array
 */
function checkMonthly( $user_id ) {
	// find out current dates
    $raffle = getRaffle( 'monthly' );
    if ( $raffle === false ) return false;

	$total = checkDaily( $user_id, $raffle );
	$required = ($raffle['days_of_tasks'] ?? Constants::get_monthly_task_requirment());

	if ($total < $required && checkOveride($user_id, $raffle['raffle_id'])) $total = $required;

	if ($total < $required) {
	    $rollover = 2459171;
		if ($raffle['start_date'] < $rollover) {
            if ($total >= $required - 12) { // if it is between 60 and 48 we can check each week to see if we get 60.
                $start_date = $raffle['start_date']; // default to this start date
                $end_date = $raffle['start_date'] + 6; // get the end date for the first week
                // TODO, iterate and check for additional marks...
                $i = 0; // set $i to 0
                while ($total < $required && $i < 12) { // while we are still below 60 and have not checked all 12 weeks yet.
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
        } else {
            $grid_id = 13012;
            $sql = "select count(distinct mark_date) as total from date_tasks_marks dtm
                        join date_tasks dt using (date_task_id) 
                        where dtm.user_id = " . $user_id . " 
                        and dt.grid_id = " . $grid_id . " 
                        and dtm.mark_date >= " . $raffle['start_date'] . " 
                        and dtm.mark_date <= " . $raffle['end_date'];
            $result = mysql_query($sql);
            $total = mysql_fetch_assoc($result)['total'];
        }
    }

    return formatRaffleInfo( $total, $required, $raffle['name'], 'monthly' );
}

function checkWeekly( $user_id ) {
	// find out current dates
    $raffle = getRaffle( 'weekly' );
    if ( $raffle === false ) return false;

	$total = checkDaily( $user_id, $raffle );
	$required = ($raffle['days_of_tasks'] ?? Constants::get_weekly_task_requirment());

	if ($total < $required && checkOveride($user_id, $raffle['raffle_id'])) $total = $required;

	if ($total < $required) {
	    $rollover = 2459167;
	    if ($raffle['start_date'] < $rollover) {
            if ($total == $required - 1) { // if it is only (4) we can check for some marks that are not tied to any specific dates
                // get a total count of all the non daily missions marked between the start and end dates of this raffle
                $update_total_sql = "SELECT COUNT(*) AS `total` FROM date_tasks dt JOIN date_tasks_marks dtmarks USING (date_task_id) WHERE dtmarks.user_id = $user_id".
                        " AND dtmarks.mark_date >= ". $raffle['start_date'] ." AND dtmarks.mark_date <= ". $raffle['end_date'] .
                        " AND daily_task = 0 AND ((dt.quantity IS NOT NULL AND dtmarks.done_qty >= dt.quantity) OR dt.quantity IS NULL)";
                $update_total_query = mysql_query($update_total_sql);
                $update_total_row = mysql_fetch_assoc($update_total_query);
                // if the user did at least one task then add him to the list (as it brings his total from 4 to 5)
                if ($update_total_row['total'] > 0) {
                    $total = 5; // set the total to 5
                }
            }
        } else {
            $grid_id = 13012;
            // find out how many different days were marked
            $sql = "select count(distinct mark_date) as total from date_tasks_marks dtm
                    join date_tasks dt using (date_task_id) 
                    where dtm.user_id = " . $user_id . " 
                    and dt.grid_id = " . $grid_id . " 
                    and dtm.mark_date >= " . $raffle['start_date'] . " 
                    and dtm.mark_date <= " . $raffle['end_date'];
            $result = mysql_query($sql);
            $total = mysql_fetch_assoc($result)['total'];
        }
    }

	return formatRaffleInfo( $total, $required, $raffle['name'], 'weekly' );
}

function checkDaily( $user_id, $raffle ) {
	$daily_sql = 'select dtmarks.mark_date from user_tracks ut'.
				' join date_tasks_missions dtm on ut.level = dtm.level and ut.track_id = dtm.track_id and ut.subject_id = dtm.subject_id'.
				' join date_tasks dt using (date_tasks_mission_id) join date_tasks_marks dtmarks using (date_task_id)'.
				' where dtmarks.user_id = '.$user_id.' and ut.user_id = '.$user_id. ' and dt.daily_task = 1'.
				' and dtmarks.mark_date >= '. $raffle['start_date'] .' and dtmarks.mark_date <= ' . $raffle['end_date'] .
				' group by dtmarks.mark_date';
	//echo $daily_sql."\n"; // if you want to debug...
	$daily_query = mysql_query($daily_sql); // run the query
	$total = mysql_num_rows( $daily_query ); // get the number of marks
	return $total;
}

function checkOveride( $user_id, $raffle_id) {
	$sql = "SELECT * FROM raffle_eligibility
			WHERE user_id = $user_id
			AND raffle_id = $raffle_id
			AND eligible = 1
			LIMIT 1";
	$query = mysql_query($sql);
	return mysql_num_rows($query);
}

/**
 * formatRaffleInfo
 * 
 * formats the information for each of the raffles
 *
 * @param number $total
 * @param number $required
 * @param string $raffle_name
 * @return void
 */
function formatRaffleInfo( $total, $required, $raffle_name, $type ){
    if ( $total > 0 )
        $percent_done = $total > $required ? 100 : ( $total / $required ) * 100;
    else
        $percent_done = 0;

	// send them a message with how many days left/done
	if ( $total >= $required ) {
		switch ( $type ) {
			case 'yearly':
				$msg = isset( $_COOKIE['lang'] ) && $_COOKIE['lang'] == 'he' ? "זכאות לדגל ה 180" : "Eligible for the 180 flag";
				break;
			case 'monthly':
				$msg = isset( $_COOKIE['lang'] ) && $_COOKIE['lang'] == 'he' ? "זכאות לדגל ה 60 הקרוב" : "Eligible for the upcoming 60 flag";
				break;
			case 'weekly':
				$msg = isset( $_COOKIE['lang'] ) && $_COOKIE['lang'] == 'he' ? "זכאות לדגל ה 5 הקרוב" : "Eligible for the upcoming 5 flag ($raffle_name)";
		}
	} else {
		$msg = ( $required - intval( $total ) ) . (isset( $_COOKIE['lang'] ) && $_COOKIE['lang'] == 'he' ? " ימים של משימות הנדרשים כדי לעבור " : " days of tasks needed to pass ");
		switch ( $type ) {
			case 'yearly':
				$msg .= isset( $_COOKIE['lang'] ) && $_COOKIE['lang'] == 'he' ? "הדגל ה 180" : "the 180 flag";
				break;
			case 'monthly':
				$msg .= isset( $_COOKIE['lang'] ) && $_COOKIE['lang'] == 'he' ? "הדגל ה 60 הקרוב" : "the upcoming 60 flag";
				break;
			case 'weekly':
				$msg .= isset( $_COOKIE['lang'] ) && $_COOKIE['lang'] == 'he' ? "הדגל ה 5 הקרוב" : "the upcoming 5 flag ($raffle_name)";
		}
    }

	return [ "percent_done" => $percent_done, "msg" => $msg, 'missed-deadline' => false ];
}

function getRaffle( $type ) {
	$today = unixtojd();
	$sql = "SELECT raffle_id, start_date, end_date, name, days_of_tasks FROM raffles
			WHERE type = '" . $type . "'
			AND start_date <= " . $today . "
            AND end_date >= " . $today;
    $result = mysql_query($sql);
    if ( mysql_num_rows( $result ) == 0 ) return false;
	return mysql_fetch_assoc($result);
}