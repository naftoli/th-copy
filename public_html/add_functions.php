<?php
ini_set('display_errors', 1);
require_once( __DIR__ . '/db.php' );
require_once( __DIR__ . '/api/header/db.php' );
require_once( __DIR__ . '/yearly_prize/classes/TotalWeeklyTasks.php' );

//var_dump($_GET);
$function_name = $_GET['function_name'];
$parameters = $_GET['parameters'];
$parameters = explode(",", $parameters);

if (isset($_GET['update'])) {
	$update = $_GET['update'];
	echo $function_name($parameters, $update);
} else {
	echo $function_name($parameters);
}

function add_ons() {
	$add_ons = $_GET['add_ons'];
	$add_on_users = explode(':', $add_ons);

	$success = 1;

	foreach ($add_on_users as $user) :
		$user_info = explode('-', $user);
		$user_id = $user_info[0];
		$user_school_add_ons_info = explode(':', $user_info[1]);

		foreach ($user_school_add_ons_info as $user_school_add_ons) :
			$school_add_ons_info = explode(';', $user_school_add_ons);

			foreach ($school_add_ons_info as $school_add_ons) :
				$add_ons_info = explode(',', $school_add_ons);

				if (count($add_ons_info) == 1)
					$sql = "INSERT INTO user_add_ons (user_id, school_add_on_id, date) VALUES (" . $user_id . ", " . $add_ons_info[0] . ", CURDATE())";
				else
					$sql = "INSERT INTO user_add_ons (user_id, school_add_on_id, size, date) VALUES (" . $user_id . ", " . $add_ons_info[0] . ", '" . $add_ons_info[1] . "',  CURDATE())";

				$query = mysql_query($sql);

				if (!$query) {
					$success = 0;
					break 3;
				}

			endforeach;

		endforeach;

	endforeach;

	return json_encode($success);
}

function register_students($parameters) {
    require_once( __DIR__ . '/class.globalSettings.php' );
	$admin_id = isset( $_COOKIE['admin_id'] ) ? mysql_real_escape_string( $_COOKIE['admin_id'] ) : 0;

    // parse the params
    $parms = $parameters[0];
    $users = explode(":", $parms);

    //variables for transactions table
    $transaction_info = explode( ";", array_pop( $users ) );
    $total = $transaction_info[0];
    $school_id = $transaction_info[1];
	$total_registered = 0;
	
	$year = GlobalSettings::getRegistrationYear( $school_id );

    $user_ids = []; $user_amounts = [];
    foreach( $users as $user ) {
        $user_info = explode( ";", $user );
        $user_ids[] = $user_info[ 0 ];
        $user_amounts[] = intval($user_info[ 1 ]);
	}

    // load all the users in an array
    $users = Soldier::find( $user_ids );
	$users = is_array( $users ) ? $users : [ $users ];
	$total_registered = count( $users );
    // and register all the users
    foreach( $users as $index => $user ){
        $user->registerChayolei( $admin_id, $year, $user_amounts[$index] );
    }
}

function assign_winner($parameters) {
	$auction_id = $parameters[0];
	$user_id = $parameters[1];
	$hidden_user_id = $parameters[2];
	$hidden_prize_id = $parameters[3];
	$hidden_quantity = $parameters[4] - 1;

	$sql = "INSERT INTO auction_winners SET auction_id=" . $auction_id . ", user_id=" . $user_id . ", prize_id=" . $hidden_prize_id . ", quantity=1";
	$query = mysql_query($sql);

	$sql = "UPDATE auction_winners SET quantity=" . $hidden_quantity . " WHERE auction_id=" . $auction_id . " AND user_id=" . $hidden_user_id . " AND prize_id=" . $hidden_prize_id;
	$query = mysql_query($sql);

	if ($query)
		return json_encode("1");
	else
		return json_encode("0");
}

function add_date_tasks_marks($parameters) {
	//print_r($parameters);
	$user_id = $parameters[0];
	$date_task_ids = explode(":", $parameters[1]);
	$mark_dates = explode(":", $parameters[2]);

	//print_r($date_task_ids);
	//$subjects = array();
	$numTasks = count($date_task_ids);
	for ($dtmno = 0; $dtmno < $numTasks; $dtmno++) {

		$date_task_id = $date_task_ids[$dtmno];
		$mark_date = $mark_dates[$dtmno];
		//if ($user_id == 8273) {
		//	echo $date_task_id . "<br />" . $mark_date; exit;
		//}

		if (!empty($date_task_id)) {
			//$sql = "SELECT dt.*, dtm.end_date, dtm.subject_id FROM date_tasks AS dt JOIN date_tasks_missions AS dtm USING (date_tasks_mission_id)  WHERE date_task_id=" . $date_task_id;
			$sql = "select * from date_tasks where date_task_id = " . $date_task_id;
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			$daily_task = $row['daily_task'];
			//if (!in_array($row['subject_id'], $subjects)) $subjects[] = $row['subject_id'];
			//print_r($row);
			//if ($user_id == 50689) {
				//print_r($row);
				//continue;
			//}

			if ($daily_task) {
				$parameters = array($user_id, $date_task_id, $mark_date);
				// if it's last mark then update missions
				if ($dtmno == ($numTasks - 1)) add_daily_task_mark($parameters, true);
				else add_daily_task_mark($parameters, false);
			}
			else {
				// if it's last mark then update missions
				if ($dtmno == ($numTasks - 1)) add_task_mark($parameters, true);
				else add_task_mark($parameters, false);
			}
		}
	}
	/*
	require_once 'classes/medal_updater.php';
	require_once 'classes/rank_updater.php';
	$medal_updater = new medal_updater();
	$rank_updater = new rank_updater();

	require_once("classes/mission_marks_updater.php");
	$mm = new mission_marks_updater();

	foreach ($subjects as $subject) {
        $mm->mission_marks_update_by_subject_id($user_id, $subject);
    }

    $medal_updater->update_medal_two($user_id);
    $rank_updater->update_rank_two($user_id);
    */
}

// ***** WEEKLY, SHABBOS, NO LABEL TASKS ***** //
function add_task_mark($parameters, $update = true) {
	$user_id = $parameters[0];
	$date_task_id = $parameters[1];
	$mark_date = $parameters[2];

	$sql = "SELECT dt.*, dtm.start_date, dtm.end_date, dtm.subject_id ";
	$sql = $sql . "FROM date_tasks AS dt ";
	$sql = $sql . "JOIN date_tasks_missions AS dtm USING (date_tasks_mission_id)  ";
	$sql = $sql . "WHERE date_task_id=" . $date_task_id;

	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$date_tasks_mission_id = $row["date_tasks_mission_id"];
	//$mark_description = $row["name"];
	$mandatory = $row['mandatory_qty'];
	$subject_id = $row['subject_id'];
	$grid_id = $row['grid_id'];
	$points = $row['points'];

	if ($mark_date == 0)
		$mark_date = $row["end_date"];

	// need to make sure marks in system aren't greater than or less than current mission dates
	// this will make sure user cannot switch langs and marks same mission twice
	if ($mark_date > $row['end_date']) {
		$mark_date = $row['end_date'];
	}
	if ($mark_date < $row['start_date']) {
		$mark_date = $row['start_date'];
	}

	// make sure mark doesn't already exist either in current lang or in different lang / level
	// get start and end date for checking marks
	//$sql = "select start_date, end_date from date_tasks_missions where date_tasks_mission_id = " . $date_tasks_mission_id;
	//$result = mysql_query($sql);
	//$numRows = mysql_num_rows($result);

	if ($grid_id) {
		$start_date = $row['start_date'];
		$end_date = $row['end_date'];
		$sql = "select * from date_tasks_marks dtm
				join date_tasks dt using (date_task_id)
				where dt.grid_id = " . $grid_id . "
				and user_id = " . $user_id . "
				and mark_date >= " . $start_date . "
				and mark_date <= " . $end_date;
	} else {
		$sql = "select * from date_tasks_marks
				where date_task_id = " . $date_task_id . "
				and user_id = " . $user_id;
	}
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		return json_encode(true); // it's already been marked
	}

	$insert_sql = "INSERT INTO date_tasks_marks SET date_task_id=" . $date_task_id . ", user_id=" . $user_id . ", mark_date=" . $mark_date . ", done_qty=1, mark_points=" . $points;
	//echo $insert_sql . "<br />";
	$insert_query = mysql_query($insert_sql);

	if ($insert_query) {

		if ($mandatory > 0 && $update) {
			//if ($user_id == 50689) echo "checking mission completion...";
			check_mission_completion($user_id, $subject_id, $date_tasks_mission_id, $mark_date, $update);
        }

        // update the users information in the user_yearly_gift table
        TotalWeeklyTasks::updateUser( $user_id, $mark_date, false );

		return json_encode(true);
	}
	else {
		return json_encode(false);
	}

}

function check_mission_completion($user_id, $subject_id, $date_tasks_mission_id, $mark_date, $update = true) {
	$sql = "SELECT dt.date_task_id, dt.quantity, dtm.done_qty, dtm.date_task_id, dt.name ";
	$sql = $sql . "FROM date_tasks AS dt ";
	$sql = $sql . "LEFT JOIN date_tasks_marks AS dtm ON (dt.date_task_id=dtm.date_task_id AND dtm.user_id=" . $user_id . ") ";
	$sql = $sql . "WHERE dt.date_tasks_mission_id=" . $date_tasks_mission_id . " ";
	$sql = $sql . "AND dt.mandatory_qty > 0 ";
	$sql = $sql . "AND (dtm.date_task_id IS NULL OR dtm.done_qty < dt.quantity)";

	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);

	if ($num_rows == 0) {
		$sql = "SELECT dtm.subject_id, dtm.mission_value, dtm.mission_name FROM date_tasks_missions AS dtm WHERE date_tasks_mission_id=" . $date_tasks_mission_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);

		// check that mission needs to be inserted
		$sql = "select * from date_tasks_mission_marks where user_id = " . $user_id . " and date_tasks_mission_id = " . $date_tasks_mission_id;
		$result = mysql_query($sql);
		if (mysql_num_rows($result) == 0) {
			$insert_sql = "INSERT INTO date_tasks_mission_marks SET user_id=" . $user_id . ", date_tasks_mission_id=" . $date_tasks_mission_id . ", subject_id=" . $row['subject_id'] . ", mission_value=" . $row['mission_value'] . ", mission_name='" . mysql_real_escape_string($row['mission_name']) . "', mark_date=" . $mark_date . ", mark_override=0";
			$insert_query = mysql_query($insert_sql);
		}
		/*
		if ($update) {
			require_once("classes/rank_updater.php");
			require_once("classes/medal_updater.php");
			require_once("classes/mission_marks_updater.php");

			$mm = new mission_marks_updater();
			$mm->mission_marks_update_by_subject_id($user_id, $subject_id);

			$medal_updater = new medal_updater();
			$medal_updater->update_medal_two($user_id);

			$rank_updater = new rank_updater();
			$rank_updater->update_rank_two($user_id);
		}
		*/
	}
}
// ***** WEEKLY, SHABBOS, NO LABEL TASKS ***** //

// ***** DAILY TASKS ***** //
function add_daily_task_mark($parameters, $update = true)
{
	$user_id = $parameters[0];
	$date_task_id = $parameters[1];
	$mark_date = $parameters[2];

	$sql = "SELECT dt.*, dtm.start_date, dtm.end_date
			FROM date_tasks dt
			join date_tasks_missions dtm using (date_tasks_mission_id)
			WHERE date_task_id=" . $date_task_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$points = $row['points'];
	$mandatory = $row['mandatory_qty'];
	$needed = $row['needed'];
	//$mark_description = $row["name"];
	$date_tasks_mission_id = $row['date_tasks_mission_id'];
	$grid_id = $row['grid_id'];

	// need to make sure marks in system aren't greater than or less than current mission dates
	// this will make sure user cannot switch langs and marks same mission twice
	if ($mark_date > $row['end_date']) {
		$mark_date = $row['end_date'];
	}
	if ($mark_date < $row['start_date']) {
		$mark_date = $row['start_date'];
	}

	// make sure mark doesn't already exist either in current lang or in different lang / level
	// bug on 11/27/2017 -> grid_id is ambiguos in the SQL satement, might be pulled from the wrong table
	if ($grid_id) {
		$sql = "select * from date_tasks_marks dtm
				join date_tasks dt using (date_task_id)
				where dt.grid_id = " . $grid_id . "
				and user_id = " . $user_id . "
				and mark_date = " . $mark_date;
	} else {
		$sql = "select * from date_tasks_marks
				where date_task_id = " . $date_task_id . "
				and user_id = " . $user_id . "
				and mark_date = " . $mark_date;
	}
	//echo $sql;
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		return json_encode(true); // it's already been marked
	}

	$sql = "INSERT INTO date_tasks_marks SET date_task_id=" . $date_task_id . ", user_id=" . $user_id . ", mark_date=" . $mark_date . ", mark_points=" . $points . ",done_qty=1";
	//if ($user_id == 51629) {
	//	echo $sql;
	//	exit;
	//}
	$query = mysql_query($sql);

	if ($query)
	{

		// ***** If the task is mandatory then we need to see if all of the daily tasks have been completed ***** //
		if ($mandatory && $update)
		{

			$sql = "SELECT * FROM date_tasks_marks WHERE user_id=" . $user_id . " AND date_task_id=" . $date_task_id;
			$query = mysql_query($sql);
			$num_rows = mysql_num_rows($query);
			//if ($user_id == 50689) echo "done - " . $num_rows . " needed - " . $needed . "<br />";

			// ***** If all of the daily tasks have been completed then we need to see if the mission has been completed ***** //
			if ($num_rows >= $needed)
			{
				$done = check_tasks($user_id, $date_tasks_mission_id);
				//if ($user_id == 50689) {
				//	echo "done - " . $num_rows . " needed - " . $needed . "<br />";
				//	echo "result of done var: " . $done;
				//}

				if ($done)
				{
					$sql = "SELECT dtm.subject_id, dtm.mission_value, dtm.mission_name FROM date_tasks_missions AS dtm WHERE date_tasks_mission_id=" . $date_tasks_mission_id;
					$query = mysql_query($sql);
					$row = mysql_fetch_assoc($query);

					$insert_sql = "INSERT INTO date_tasks_mission_marks SET user_id=" . $user_id . ", date_tasks_mission_id=" . $date_tasks_mission_id . ", subject_id=" . $row['subject_id'] . ", mission_value=" . $row['mission_value'] . ", mission_name='" . mysql_real_escape_string($row['mission_name']) . "', mark_date=" . $mark_date . ", mark_override=0";
					//if ($user_id == 50689) echo $insert_sql;
					$insert_query = mysql_query($insert_sql);
					/*
					if ($update) {
						require_once("classes/mission_marks_updater.php");
						require_once("classes/rank_updater.php");
						require_once("classes/medal_updater.php");

						$mm = new mission_marks_updater();
						$mm->mission_marks_update_by_subject_id($user_id, $row['subject_id']);

						$medal_updater = new medal_updater();
						$medal_updater->update_medal_two($user_id);

						$rank_updater = new rank_updater();
						$rank_updater->update_rank_two($user_id);
					}
					*/
				}

			}
			// ***** If all of the daily tasks have been completed then we need to see if the mission has been completed ***** //

		}
		// ***** If the task is mandatory then we need to see if all of the daily tasks have been completed ***** //

	}
	else
	{
		return json_encode(false);
	}
}
// ***** DAILY TASKS ***** //

function add_daily_task_mark2($parameters, $update = true)
{
	$user_id = $parameters[0];
	$date_task_id = $parameters[1];
    $mark_date = $parameters[2];

	require_once("classes/rank_updater.php");
	require_once("classes/medal_updater.php");

	$sql = "SELECT dt.*, dtm.start_date, dtm.end_date
			FROM date_tasks dt
			join date_tasks_missions dtm using (date_tasks_mission_id)
			WHERE date_task_id=" . $date_task_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$points = $row['points'];
	$mandatory = $row['mandatory_qty'];
	$needed = $row['needed'];
	//$mark_description = $row["name"];
	$date_tasks_mission_id = $row['date_tasks_mission_id'];
	$grid_id = $row['grid_id'];

	// need to make sure marks in system aren't greater than or less than current mission dates
	// this will make sure user cannot switch langs and marks same mission twice at different times
	if ($mark_date > $row['end_date']) {
		$mark_date = $row['end_date'];
	}
	if ($mark_date < $row['start_date']) {
		$mark_date = $row['start_date'];
	}

	// make sure mark doesn't already exist either in current lang or in different lang / level
	if ($grid_id) {
		$sql = "select * from date_tasks_marks dtm
				join date_tasks dt using (date_task_id)
				where dt.grid_id = " . $grid_id . "
				and dtm.user_id = " . $user_id . "
				and dtm.mark_date = " . $mark_date;
	} else {
		$sql = "select * from date_tasks_marks
				where date_task_id = " . $date_task_id . "
				and user_id = " . $user_id . "
				and mark_date = " . $mark_date;
	}
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		echo 0; // it's already been marked
		return;
	}

	$sql = "INSERT INTO date_tasks_marks SET date_task_id=" . $date_task_id . ", user_id=" . $user_id . ", mark_date=" . $mark_date . ", mark_points=" . $points . ",done_qty=1";
	//echo $sql;
	$query = mysql_query($sql);

	if ($query)
	{
		//print_r([$mandatory, $update]);
		// ***** If the task is mandatory then we need to see if all of the daily tasks have been completed ***** //
		if ($mandatory && $update)
		{

			$sql = "SELECT * FROM date_tasks_marks WHERE user_id=" . $user_id . " AND date_task_id=" . $date_task_id;
			$query = mysql_query($sql);
			$num_rows = mysql_num_rows($query);
			//echo "done - " . $num_rows . " needed - " . $needed; exit;

			// ***** If all of the daily tasks have been completed then we need to see if the mission has been completed ***** //
			if ($num_rows >= $needed)
			{
				$done = check_tasks($user_id, $date_tasks_mission_id);
				//echo "done - " . $num_rows . " needed - " . $needed; exit;
				//print_r([$done]);

				if ($done)
				{
					$sql = "SELECT dtm.subject_id, dtm.mission_value, dtm.mission_name FROM date_tasks_missions AS dtm WHERE date_tasks_mission_id=" . $date_tasks_mission_id;
					$query = mysql_query($sql);
					$row = mysql_fetch_assoc($query);

					$insert_sql = "INSERT INTO date_tasks_mission_marks SET user_id=" . $user_id . ", date_tasks_mission_id=" . $date_tasks_mission_id . ", subject_id=" . $row['subject_id'] . ", mission_value=" . $row['mission_value'] . ", mission_name='" . mysql_real_escape_string($row['mission_name']) . "', mark_date=" . $mark_date . ", mark_override=0";
					$insert_query = mysql_query($insert_sql);
					/*
					if ($update) {
						require_once("classes/mission_marks_updater.php");
						$mm = new mission_marks_updater();
						$mm->mission_marks_update_by_subject_id($user_id, $row['subject_id']);

						$medal_updater = new medal_updater();
						$medal_updater->update_medal_two($user_id);

						$rank_updater = new rank_updater();
						$rank_updater->update_rank_two($user_id);
					}
					*/
				}

			}
			// ***** If all of the daily tasks have been completed then we need to see if the mission has been completed ***** //

		}
        // ***** If the task is mandatory then we need to see if all of the daily tasks have been completed ***** //
        echo 0;
        
        // update the users information in the user_yearly_gift table
        TotalWeeklyTasks::updateUser( $user_id, $mark_date, false );
	}
	else
	{
		echo 1;
	}
}

function check_tasks($user_id, $date_tasks_mission_id) {
	$done = true;

	// ***** Check to see if all non daily tasks were all completed ***** //
	$sql = "SELECT dt.date_task_id, dt.quantity, dtm.done_qty, dtm.date_task_id, dt.name ";
	$sql = $sql . "FROM date_tasks AS dt ";
	$sql = $sql . "LEFT JOIN date_tasks_marks AS dtm ON (dt.date_task_id=dtm.date_task_id AND dtm.user_id=" . $user_id . ") ";
	$sql = $sql . "WHERE dt.date_tasks_mission_id=" . $date_tasks_mission_id . " ";
	$sql = $sql . "AND dt.mandatory_qty=1 ";
	$sql = $sql . "AND dt.daily_task=0 ";
	$sql = $sql . "AND (dtm.date_task_id IS NULL OR dtm.done_qty < dt.quantity) ";
	//if ($user_id == 50689) echo $sql;
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);
	// ***** Check to see if all non daily tasks were all completed ***** //

	if ($num_rows > 0)
	{
		$done = false;
	}
	else
	{
		// ***** Check to see if all daily tasks were all completed ***** //
		$sql = "SELECT * ";
		$sql = $sql . "FROM date_tasks AS dt ";
		$sql = $sql . "WHERE dt.date_tasks_mission_id=" . $date_tasks_mission_id . " ";
		$sql = $sql . "AND daily_task=1 ";
		$sql = $sql . "AND mandatory_qty=1 ";
		//if ($user_id == 50689) echo $sql;
		$query = mysql_query($sql);

		while ($row = mysql_fetch_assoc($query)) {
			$sql2 = "SELECT * FROM date_tasks_marks WHERE user_id=" . $user_id . " AND date_task_id=" . $row['date_task_id'];
			$query2 = mysql_query($sql2);
			$num_rows2 = mysql_num_rows($query2);

			if ($num_rows2 < $row['needed']) {
				$done = false;
				break;
			}
		}
		// ***** Check to see if all daily tasks were all completed ***** //
	}

	return $done;
}

function add_mark($parameters, $update = true)
{
	$user_id = $parameters[0];
	$date_task_id = $parameters[1];
	$mark_date = $parameters[2];
	$user_mark = $parameters[3];

	$inserted_or_updated = false;

	// ***** DATE TASK INFORMATION ***** //
	//$sql = "SELECT * FROM date_tasks WHERE date_task_id=" . $date_task_id;
	//$query = mysql_query($sql);
	//$row = mysql_fetch_assoc($query);
	//
	//$date_tasks_mission_id = mysql_real_escape_string($row['date_tasks_mission_id']);
	//$task_name = $row['name'];
	////$mark_description = mysql_real_escape_string($row['description']);
	//$mark_points = $row['points'];
	//$mark_quantity = $row['quantity'];
	//$grid_id = $row['grid_id'];
	//$mandatory = $row['mandatory_qty'];
	// ***** DATE TASK INFORMATION ***** //

	$sql = "SELECT dt.*, dtm.start_date, dtm.end_date
			FROM date_tasks dt
			join date_tasks_missions dtm using (date_tasks_mission_id)
			WHERE date_task_id=" . $date_task_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);

	$date_tasks_mission_id = mysql_real_escape_string($row['date_tasks_mission_id']);
	$task_name = $row['name'];
	//$mark_description = mysql_real_escape_string($row['description']);
	$mark_points = $row['points'];
	$mark_quantity = $row['quantity'];
	$grid_id = $row['grid_id'];
	$mandatory = $row['mandatory_qty'];

	// need to make sure marks in system aren't greater than or less than current mission dates
	if ($mark_date > $row['end_date']) {
		$mark_date = $row['end_date'];
	}
	if ($mark_date < $row['start_date']) {
		$mark_date = $row['start_date'];
	}

	// if tehillim kapitelach make sure not more than 150
	if ($grid_id == 8001) {
		if ($user_mark > 150) $user_mark = 150;
    }
    
    // if tehillim minutes make sure not more than 770
	if ($grid_id == 8002) {
		if ( $user_mark > 770 ) $user_mark = 770;
	}

	//// get start and end date for checking marks
	//$sql = "select start_date, end_date from date_tasks_missions where date_tasks_mission_id = " . $date_tasks_mission_id;
	//$result = mysql_query($sql);
	//$numRows = mysql_num_rows($result);

	// this will make sure user cannot switch langs and marks same mission twice
	if ($grid_id) {
		$start_date = $row['start_date'];
		$end_date = $row['end_date'];
		$sql = "select * from date_tasks_marks dtm
				join date_tasks dt using (date_task_id)
				where grid_id = " . $grid_id . "
				and mark_date >= " . $start_date . "
				and mark_date <= " . $end_date . "
				and user_id = " . $user_id;
	} else {
		$sql = "select * from date_tasks_marks
				where date_task_id = " . $date_task_id . "
				and user_id = " . $user_id;
	}

	//$sql = "SELECT * FROM date_tasks_marks WHERE date_task_id=" . $date_task_id . " AND user_id=" . $user_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	//print_r($sql);
	//print_r($row);
	//$oldMark = false;

	if ($user_mark == '') $user_mark = 0;

	if ($row)
	{
		// if done qty is set to 0, delete date task mark
		if ($user_mark == 0) {
			$sql = "delete from date_tasks_marks where user_id = " . $user_id . " and date_task_id = " . $date_task_id;
		} else {
			//$oldMark = $row['done_qty'];
			$sql = "UPDATE date_tasks_marks SET done_qty=" . $user_mark . " WHERE date_task_id=" . $date_task_id . " AND user_id=" . $user_id;
		}
		$query = mysql_query($sql);
		if ($query)
			$inserted_or_updated = true;
	}
	else
	{
		$sql = "INSERT INTO date_tasks_marks SET date_task_id=" . $date_task_id . ", user_id=" . $user_id . ", mark_date=" . $mark_date . ", done_qty=" . $user_mark . ", mark_points=" . $mark_points;
		$query = mysql_query($sql);
		//echo $query;
		if ($query)
			$inserted_or_updated = true;
	}

	if ($inserted_or_updated == true)
	{
		// check mission marks
		require_once ("classes/missions_updater.php");
		$mission_updater = new mission_updater();
		$mission_updater->mission_update($user_id, $date_tasks_mission_id);

		$tanyaGridIds = array(21001,21002,21003,21004,21005,21006,21007,21008,21013);

		// if it's a tanya mark update the yud alef nissan tables
		if (unixtojd() > 2458101 && isset($grid_id) && in_array($grid_id, $tanyaGridIds)) {

			require_once 'class.globalSettings.php';
			$year = GlobalSettings::getChidonYear();

			// get campaigns for current year
			$sql = "SELECT * FROM line_campaigns WHERE year = " . $year;
			$result = mysql_query( $sql );
			while ($row = mysql_fetch_assoc( $result )) {
				if (strtolower($row['type']) == 'tanya') $tanyaCampaign = $row['id'];
				else if (strtolower($row['type']) == 'mishna') $mishnaCampaign = $row['id'];
			}

			if (in_array($grid_id, $tanyaGridIds)) $campaign = $tanyaCampaign;

			$exists_query = mysql_query("SELECT mission_sheet_amount AS t FROM lines_learned WHERE campaign_id = " . $campaign . " AND user_id = " . $user_id);
			if (mysql_num_rows($exists_query) > 0) {
				$update_sql = "UPDATE lines_learned"
						." SET mission_sheet_amount = " . $user_mark
						." WHERE campaign_id = " . $campaign
						." AND user_id = " . $user_id;
				mysql_query($update_sql);
			} else {
				$user_info_query = mysql_query("SELECT school_id, class_id FROM users WHERE user_id = " . $user_id);
				$user_info = mysql_fetch_assoc($user_info_query);
				$insert_sql = "INSERT INTO lines_learned SET "
						."campaign_id = " . $campaign . ", "
						."user_id = " . $user_id . ", "
						."mission_sheet_amount = " . $user_mark . ", "
						."school_id = " . $user_info['school_id'] . ", "
						."class_id = " . $user_info['class_id'];
				mysql_query($insert_sql);
			}
		}

		return json_encode("1");
	}
	else
	{
		return json_encode("0");
	}
}

function update_missions($user_id, $date_task_id) {
	require_once("classes/rank_updater.php");
	require_once("classes/medal_updater.php");

	$sql = "SELECT * FROM date_tasks WHERE date_task_id=" . $date_task_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$mandatory = $row['mandatory_qty'];
	$date_tasks_mission_id = $row['date_tasks_mission_id'];

	if ($mandatory)
	{
		$done = check_tasks($user_id, $date_tasks_mission_id);

		if ($done)
		{
			$sql = "SELECT dtm.subject_id, dtm.mission_value, dtm.mission_name FROM date_tasks_missions AS dtm WHERE date_tasks_mission_id=" . $date_tasks_mission_id;
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);

			$insert_sql = "INSERT INTO date_tasks_mission_marks SET user_id=" . $user_id . ", date_tasks_mission_id=" . $date_tasks_mission_id . ", subject_id=" . $row['subject_id'] . ", mission_value=" . $row['mission_value'] . ", mission_name='" . mysql_real_escape_string($row['mission_name']) . "', mark_date=" . $mark_date . ", mark_override=0";
			$insert_query = mysql_query($insert_sql);
			/*
			require_once("classes/mission_marks_updater.php");
			$mm = new mission_marks_updater();
			$mm->mission_marks_update($user_id);

			$medal_updater = new medal_updater();
			$medal_updater->update_medal_two($user_id);

			$rank_updater = new rank_updater();
			$rank_updater->update_rank_two($user_id);
			*/
		}

	}
}
?>