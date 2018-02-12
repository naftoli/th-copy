<?php
require_once ("../header.php");
require_once('../file_save.php');
require_once('../calendar.php');
require_once('classes/user_tls.php');

$current_date = unixtojd();
echo "<input type='hidden' name='current_date' value='" . $current_date . "'>\n";

$user_id = $user['user_id'];
$subject_id = gri('subject_id', -1);
$medal_ord = gri('medal_ord', -1);

// ***** GLOBALS ***** //
$completed_missions = 0;
$incomplete_missions = array();

//*******************************************************************************************//
//**************************************** FUNCTIONS ****************************************//
function getSubject($subject_id) {
	$sql = "SELECT * FROM subjects WHERE subject_id=" . $subject_id;
	$result = mysql_fetch_assoc(mq($sql));
	return $result;
}
		
function getMissionsTwo($user_tls, $user_id, $subject_id) {
	global $ending_date;
	$ending_date = 0;

	$missions_array = array();
		
	for ($cntr = 0; $cntr < count($user_tls); $cntr++) {
		getTLSMissionsTwo($missions_array, $user_id, $subject_id, $user_tls[$cntr]->track_id, $user_tls[$cntr]->level, $user_tls[$cntr]->school_type_id, $user_tls[$cntr]->cutoff_date);	
	}	
		
	return $missions_array;
}
		
function getTLSMissionsTwo(&$missions_array, $user_id, $subject_id, $track_id, $level, $school_type_id, $starting_date) {
	global $completed_missions;
	global $ending_date;
	global $birth_date;
	global $check_for_first_task;
	global $current_date;
	
	$sqlSelect = "SELECT dtm.* ";	
	//$sqlSelect = "SELECT dtm.*, dtmm.mark_date ";	
	$sqlFrom = " FROM date_tasks_missions AS dtm ";
	$sqlJoin = " ";
	//$sqlJoin = " LEFT JOIN date_tasks_mission_marks AS dtmm ON dtmm.user_id=" . $user_id . " AND dtm.date_tasks_mission_id=dtmm.date_tasks_mission_id ";
	$sqlWhere = " WHERE dtm.track_id=" . $track_id . " ";
	$sqlWhere = $sqlWhere . " AND dtm.level=" . $level . " ";
	$sqlWhere = $sqlWhere . " AND dtm.school_type_id=" . $school_type_id . " ";
	$sqlWhere = $sqlWhere . " AND dtm.subject_id=" . $subject_id . " ";
	$sqlWhere = $sqlWhere . " AND dtm.start_date > " . $current_date . " ";
			
	$sqlOrderBy = " ORDER BY start_date ";
	$sqlStatement = $sqlSelect . $sqlFrom . $sqlJoin . $sqlWhere . $sqlOrderBy;	
	
	echo "\n\n<input type='hidden' name='sqlStatement TWO' value='" . $sqlStatement . "'>\n";
	
	$results = mysql_query($sqlStatement);
	$num_rows = mysql_num_rows($results);
		
	while ($row = mysql_fetch_assoc($results)) {
		//echo "<input type='hidden' name='START DATE' value='" . $row['start_date'] . "'>\n";
		//echo "<input type='hidden' name='BIRTH DATE' value='" . $birth_date . "'>\n";
		
		if ($row['start_date'] < $birth_date) {
			$ending_date = $row['start_date'];
			$completed = false;
			
			array_push($missions_array, array($row['date_tasks_mission_id'], $row['mission_name'], $row['start_date'], $completed, false));
		}
	}
	
	echo "<input type='hidden' name='*** MISSIONS ARRAY COUNT ***' value='" . count($missions_array) . "'>\n\n";
}
		
function getMissions($user_tls, $user_id, $subject_id) {
	global $ending_date;
	$ending_date = 0;

	$missions_array = array();
		
	$first_task_found = false;

	for ($cntr = 0; $cntr < count($user_tls); $cntr++) {
		echo "\n\n<input type='hidden' name='USER AND SUBJECT' value='" . $user_id . ":" . $subject_id . "'>\n";
		echo "<input type='hidden' name='USER TLS' value='" . $user_tls[$cntr]->track_id . ":" . $user_tls[$cntr]->level . ":" . $user_tls[$cntr]->school_type_id . ":" . $user_tls[$cntr]->cutoff_date . "'>\n"; 
		getTLSMissions($missions_array, $first_task_found, $user_id, $subject_id, $user_tls[$cntr]->track_id, $user_tls[$cntr]->level, $user_tls[$cntr]->school_type_id, $user_tls[$cntr]->cutoff_date);	
	}	
		
	return $missions_array;
}

function getTLSMissions(&$missions_array, &$first_task_found, $user_id, $subject_id, $track_id, $level, $school_type_id, $starting_date) {
	global $completed_missions;
	global $ending_date;
	global $birth_date;
	global $check_for_first_task;
			
	$sqlSelect = "SELECT dtm.*, dtmm.mark_date ";	
	$sqlFrom = " FROM date_tasks_missions AS dtm ";
	$sqlJoin = " LEFT JOIN date_tasks_mission_marks AS dtmm ON dtmm.user_id=" . $user_id . " AND dtm.date_tasks_mission_id=dtmm.date_tasks_mission_id ";
	$sqlWhere = " WHERE dtm.track_id=" . $track_id . " ";
	$sqlWhere = $sqlWhere . " AND dtm.level=" . $level . " ";
	$sqlWhere = $sqlWhere . " AND dtm.school_type_id=" . $school_type_id . " ";
	$sqlWhere = $sqlWhere . " AND dtm.subject_id=" . $subject_id . " ";
	$sqlWhere = $sqlWhere . " AND dtm.start_date < " . $starting_date . " ";
	
	if ($ending_date > 0)
		$sqlWhere = $sqlWhere . " AND dtm.start_date > " . $ending_date . " ";	
		
	$sqlOrderBy = " ORDER BY start_date ";
	$sqlStatement = $sqlSelect . $sqlFrom . $sqlJoin . $sqlWhere . $sqlOrderBy;	
	
	$results = mysql_query($sqlStatement);
	$num_rows = mysql_num_rows($results);
		
	while ($row = mysql_fetch_assoc($results)) {
		
		if ($row['start_date'] < $birth_date) {
			$ending_date = $row['start_date'];
				
			if ($first_task_found == false)
				$first_task_found = checkForFirstTask($user_id, $subject_id, $track_id, $level, $school_type_id, $row['date_tasks_mission_id']);
			
			if ($first_task_found == true) {
				if ($row['mark_date'] > 0) {
					$completed = true;
					$completed_missions++;				
				}
				else {
					$completed = false;
				}
				array_push($missions_array, array($row['date_tasks_mission_id'], $row['mission_name'], $row['start_date'], $completed, false));
			}						
		}
	}
	//////////echo "<input type='hidden' name='first_task_found' value='" . $first_task_found . "'>\n";
}

function checkForFirstTask($user_id, $subject_id, $track_id, $level, $school_type_id, $date_tasks_mission_id) {
	$found = false;
		
	$sqlSelect = " SELECT count(*) AS found ";
	$sqlFrom = " FROM date_tasks_marks AS dtm1 ";
	$sqlJoin = " JOIN date_tasks AS dt USING (date_task_id) ";
	$sqlJoin = $sqlJoin . " JOIN date_tasks_missions AS dtm2 USING (date_tasks_mission_id) ";
	$sqlWhere = " WHERE dtm1.user_id = " .  $user_id . " ";
	$sqlWhere = $sqlWhere . " AND dt.date_tasks_mission_id=" . $date_tasks_mission_id . " ";
	$sqlWhere = $sqlWhere . " AND dt.mandatory_qty > 0 ";
	$sqlWhere = $sqlWhere . " AND dtm2.subject_id=" . $subject_id . " ";
	$sqlWhere = $sqlWhere . " AND dtm2.level=" . $level . " ";
	$sqlWhere = $sqlWhere . " AND dtm2.track_id=" . $track_id . " ";
	$sqlWhere = $sqlWhere . " AND dtm1.mark_date > 0 ";
	$sqlStatement = $sqlSelect . $sqlFrom . $sqlJoin . $sqlWhere;
		
	$result = mysql_fetch_assoc(mq($sqlStatement));
		
	if ($result['found'] > 0)
		$found = true;
		
	return $found;
}

function getStudentTrackLevelSchoolTypes($user_id, $subject_id) {
	$user_tls_array = array();
		
	$sqlSelect = "SELECT track_id, level, school_type_id, start_date ";
	$sqlFrom = " FROM date_tasks_mission_marks AS dtmm ";
	$sqlJoin = " JOIN date_tasks_missions AS dtm USING (date_tasks_mission_id) ";
	$sqlWhere = " WHERE dtmm.user_id=" . $user_id . "  ";
	$sqlWhere = $sqlWhere . " AND dtmm.subject_id=" . $subject_id . " "; 
	$sqlOrderBy = " ORDER BY track_id, level, school_type_id, start_date ";
	$sqlStatement = $sqlSelect . $sqlFrom . $sqlJoin . $sqlWhere . $sqlOrderBy;

	echo "<input type='hidden' name='sqlStatement' value = '" . $sqlStatement . "'>\n";
	
	$rows = mysql_query($sqlStatement);
	$num_rows = mysql_num_rows($rows);
	
	$row_num = 0;	
	$tls = "";
	$prev_tls = "";
	$prev_track_id = "";
	$prev_level = "";
	$prev_school_type_id = "";
	
	while ($row = mysql_fetch_assoc($rows)) {
		$row_num++;
		$tls = $row['track_id'] . $row['level'] . $row['school_type_id'];
		
				
		if ($prev_tls != "" && $prev_tls != $tls) {
			$user_tls = new user_tls($prev_track_id, $prev_level, $prev_school_type_id, $row['start_date']);
			array_push($user_tls_array, $user_tls);
		}
		
		if ($row_num == $num_rows) {
			$user_tls = new user_tls($row['track_id'], $row['level'], $row['school_type_id'], 9999999);
			array_push($user_tls_array, $user_tls);
		}
		
		$prev_tls = $tls;		
		$prev_track_id = $row['track_id'];
		$prev_level = $row['level'];
		$prev_school_type_id = $row['school_type_id'];		
	}	
	
	return $user_tls_array;
}



function getRequiredNumberOfTasks($subject_id) {
	$missions_required = array();
		
	$sql = "SELECT missions_required FROM medals_subjects WHERE subject_id=" . $subject_id;
	$rows = mysql_query($sql);

	while ($row = mysql_fetch_assoc($rows)) {
		array_push($missions_required, $row['missions_required']);
	}
		
	return $missions_required;
}

function dateToGregorian($date) {
	$dates = cal_from_jd($date, CAL_GREGORIAN);
	return $dates['date'];
}

function createCompletedMissionDiv($date_tasks_mission_id, $mission_name, $start_date, $completed_number) {
	$hebrew_start_date = dateToHebrewSplit($start_date);
	$greg_start_date = dateToGregorian($start_date);
	
	$echoString = "";
	
	$echoString = $echoString . "\n\t<div class='mission'>";
	$echoString = $echoString . "\n<a href='prof_medal_mission.php?mission=" . $date_tasks_mission_id . "'>";
	$echoString = $echoString . "\n\t\t<div class='number'>#" . $completed_number . "</div>";	
	$echoString = $echoString . "\n\t\t<div class='date'>" . $mission_name . "</div>";
	$echoString = $echoString . "\n\t\t<div class='date'>" . $hebrew_start_date[2] . "</div>";
	$echoString = $echoString . "\n\t\t<div class='meter' style='background-position:0% 0;'></div>";
	$echoString = $echoString . "\n</a>";
	$echoString = $echoString . "\n\t\t<div class='check_on'></div>";
	
	$echoString = $echoString . "\n\t</div>\n";
	
	echo $echoString;
		
}

function createInCompletedMissionDiv($date_tasks_mission_id, $mission_name, $start_date, $greg_start_date, $future_mission, $started_task) {
	global $completed_number;
	global $subject_id;
	global $break_flag;
	global $required_tasks;
	
	$hebrew_start_date = dateToHebrewSplit($start_date);
	$greg_start_date = dateToGregorian($start_date);
		
	$echoString = "";
	$echoString = $echoString . "\n\t<div class='mission'>\n";	
	
	if ($future_mission == true) {
		$completed_number++;
		//$echoString = $echoString . "\n<a href='prof_medal_mission.php?mission=" . $date_tasks_mission_id . "'>";
		$echoString = $echoString . "\n\t\t<div class='number'>#" . $completed_number . "</div>";	
		if ($subject_id != 12 && $subject_id != 40)
			$echoString = $echoString .  "\n\t\t<div class='date'>" . $mission_name . "</div>";
		else
			$echoString = $echoString . "\n\t\t<div class='date'>&nbsp;</div>";
		$echoString = $echoString . "\n\t\t<div class='date'>" . $hebrew_start_date[2] . "</div>";
		$echoString = $echoString . "\n\t\t<div class='meter' style='background-position:100% 0;'></div>";
		//$echoString = $echoString . "\n</a>";
		
		if ($completed_number == $required_tasks) 
			$break_flag = true;
	}
	else {
		$echoString = $echoString . "\n<a href='prof_medal_mission.php?mission=" . $date_tasks_mission_id . "'>";
		$echoString = $echoString . "\n\t\t<div class='number'>&nbsp;</div>";
		$echoString = $echoString . "\n\t\t<div class='date'>" . $mission_name . "</div>";
		$echoString = $echoString . "\n\t\t<div class='date'>" . $hebrew_start_date[2] . "</div>";
		if ($started_task == true)
			$echoString = $echoString . "\n\t\t<div class='meter' style='background-position:50% 0;'></div>";
		else
			$echoString = $echoString . "\n\t\t<div class='meter' style='background-position:100% 0;'></div>";
		$echoString = $echoString . "\n</a>";
		$echoString = $echoString . "\n\t\t<div class='check_off'></div>";
	}
		
	$echoString = $echoString . "\n\t</div>\n";
	
	echo $echoString;
}

function createInActiveMissionDiv($date_tasks_mission_id, $mission_name, $start_date, $page_number) {
	global $last_page;
	global $last_line;
	global $medal_ord;
	
	if ($medal_ord > $last_page) {
		$last_page = $medal_ord;
		$last_line = 1;
	}
	else {
		$last_line++;
	}
	
	$hebrew_start_date = dateToHebrewSplit($start_date);

	$echoString = "";
	$echoString = $echoString . "\n<div class='mission inactive'>\n";	
	$echoString = $echoString . "\n\t\t<div class='number'>#" . $last_line . "</div>";
	$echoString = $echoString . "\t<div class='date'>" . $mission_name . "</div>\n";
	$echoString = $echoString . "\t<div class='date'>" . $hebrew_start_date[2] . "</div>\n";
	$echoString = $echoString . "\n\t\t<div class='check_off'></div>";
	$echoString = $echoString . "</div>\n";
	echo $echoString;
}

function getHebrewYear($jdDate) {
    $startdateh_arr = cal_from_jd($jdDate, CAL_JEWISH);
    return $startdateh_arr["year"];
}

function checkForStartedTasks($user_id, $mission_id) {
	$found = false;
	
	$sqlSelect = "SELECT * ";
	$sqlFrom = " FROM date_tasks ";
	$sqlJoin = " LEFT JOIN labels ON date_tasks.label_id = labels.label_id ";
	$sqlJoin = $sqlJoin . " LEFT JOIN date_tasks_dates USING (date_task_id) ";
	$sqlJoin = $sqlJoin . " LEFT JOIN (SELECT * FROM date_tasks_marks WHERE user_id = " . $user_id . " GROUP BY date_task_id) date_tasks_marks USING (date_task_id) ";  
	$sqlWhere = " WHERE date_tasks.date_tasks_mission_id = " . $mission_id . " ";
	$sqlWhere = $sqlWhere . " AND date_tasks.mandatory_qty > 0 ";
	$sqlWhere = $sqlWhere . " AND date_tasks_marks.mark_quantity > 0 ";
	$sqlGroupBy = " GROUP BY date_tasks.ord ";
	$sql = $sqlSelect . $sqlFrom . $sqlJoin . $sqlWhere . $sqlGroupBy;
	$rows = mysql_query($sql);
		
	while ($row = mysql_fetch_assoc($rows)) {
		$found = true;
	}

	return $found;
}

function getAllMissions() {
	global $missions_array;
	global $current_date;
	global $missions_required;
	global $completed_missions;
	global $incomplete_missions;
	
	$medal_number = 0;
	$page_number = 1;
	$completed_count = 0;
	$total_completed = 0;
	$no_more_tasks_flag = false;
	
	$missions = array();
	for ($cntr = 0; $cntr < count($missions_array); $cntr++) {
		$no_of_missions_required = $missions_required[$medal_number];
		
		$mission = new mission($missions_array[$cntr], $current_date, $page_number, false, false);
		array_push($missions, $mission);
		
		if ($mission->completed == true) {
			$completed_count++;
			$total_completed++;
		}
		else {
			if ($mission->future_mission == false) {
				$incomplete_mission = new incomplete_mission($missions_array[$cntr]);
				array_push($incomplete_missions, $incomplete_mission);
			}
		}
		
		if ($mission->future_mission == false) {
			if ($total_completed <= $completed_missions) {			
				if ($completed_count == $no_of_missions_required) {
					$medal_number++;
					$page_number++;
					$completed_count = 0;
				}
			}
			else {
				if ($no_more_tasks_flag == false) {
					$no_more_tasks_flag = true;
				}
			}
		
		}
		else {
			$completed_count++;
			
			if ($completed_count == $no_of_missions_required) {
				$completed_count = 0;
				$page_number++;
				$medal_number++;
			}
		}
				
	}

	for ($cntr = 0; $cntr < count($incomplete_missions); $cntr++) {
		$temp_array = array($incomplete_missions[$cntr]->mission_id, $incomplete_missions[$cntr]->mission_name, $incomplete_missions[$cntr]->start_date, false);
		$mission = new mission($temp_array, $current_date, $page_number, true, true);
		array_push($missions, $mission);
	}
	
	return $missions;
}

function getDefaultTLS($user_id, $subject_id, $school_type_id) {
	$user_tls = array();
	
	$sql = "SELECT * FROM user_tracks WHERE user_id=" . $user_id . " AND subject_id=" . $subject_id;
	$rows = mysql_query($sql);
		
	while ($row = mysql_fetch_assoc($rows)) {
		$user_tls_array = new user_tls($row['track_id'], $row['level'], $school_type_id, 9999999);
		array_push($user_tls, $user_tls_array);
	}
	
	return $user_tls;
}
//**************************************** FUNCTIONS ****************************************//
//*******************************************************************************************//

//*****************************************************************************************//
//**************************************** CLASSES ****************************************//
class mission {	
	var $mission_id;
	var $mission_name;
	var $start_date;
	var $greg_start_dates;
	var $greg_start_date;
	var $completed;
	var $future_mission;
	var $page_number;
	var $completed_number;
	var $inactive;
	var $started_task;
	
	function mission ($mission_array, $current_date, $page_number, $inactive, $started_task) {
		$this->mission_id = $mission_array[0];
		$this->mission_name = $mission_array[1];
		$this->start_date = $mission_array[2];
		$this->greg_start_dates = cal_from_jd($mission_array[2], CAL_GREGORIAN);
		$this->greg_start_date = $this->greg_start_dates['date'];
		$this->completed = $mission_array[3];
		$this->page_number = $page_number;
		$this->inactive = $inactive;
		$this->started_task = $started_task;
		
		if ($this->start_date > $current_date)
			$this->future_mission = true;
		else
			$this->future_mission = false;
	}
		
	public function setPageNumber($page_number) {
		$this->page_number = $page_number;
	}

	public function setCompletedNumber($completed_number) {
		$this->completed_number = $completed_number;
	}	
	
}

class incomplete_mission {
	var $mission_id;
	var $mission_name;
	var $start_date;

	function incomplete_mission ($mission_array) {
		$this->mission_id = $mission_array[0];
		$this->mission_name = $mission_array[1];
		$this->start_date = $mission_array[2];
	}
	
}
//**************************************** CLASSES ****************************************//
//*****************************************************************************************//
	
if ($subject_id > 0 && $medal_ord > 0) {

	// ********** USER **********//
	$user_row = mysql_fetch_assoc(mq("
	SELECT user_id, first, last, dob, dob_he_offset, first_he, last_he, username, gender, user_address1, user_address2,
		   user_city, user_state, user_postal, user_country, user_phone,
		   user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id, team_name, 
		   school_name, school_number, school_city, school_state, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, school_id,
		   rank_name, rank_image_id, rank_color 
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
	// ********** USER **********//

	// ********** GLOBALS **********//
	$ending_date = 0;	
	$last_page = 0;
	$mizva_age = ($user_row['gender']=="M"?13:12);
	$bar_mizva_year = getHebrewYear(dateToJD($user_row['dob'])+$user_row['dob_he_offset']) + $mizva_age;
	$birth_date = jewishtojd ( 1, 1, $bar_mizva_year+1 );
	//////////echo "<input type='hidden' name='birth_date' value='" . $birth_date . "'>\n";	
	$greg_birth_date = dateToGregorian($birth_date);
	//////////echo "<input type='hidden' name='greg_birth_date' value='" . $greg_birth_date . "'>\n";	
	$break_flag = false;
	$default_school_type_id = $user_row['school_type_id'];
	$check_for_first_task = true;
	
	echo "<input type='hidden' name='default_school_type_id' value='" . $default_school_type_id . "'>\n";	
	
	// ********** SUBJECT **********//
	$subject = getSubject($subject_id);

	// ********** MEDAL ********** //
	$sql = "SELECT m.medal_name, ms.missions_required, ms.profile_photo_id FROM medals AS m JOIN medals_subjects AS ms USING (medal_ord) WHERE m.medal_ord=" . $medal_ord . " AND ms.subject_id=" . $subject_id;
	$medal = mysql_fetch_assoc(mq($sql));		

	// ********** REQUIRED NUMBER OF TASKS FOR EACH MEDAL ********** //	
	$missions_required = getRequiredNumberOfTasks($subject_id);	
	 
	// ********** All the TRACKS, LEVELS, and SCHOOL TYPE IDs for a given student and subject ********** //
	$user_tls = getStudentTrackLevelSchoolTypes($user_id, $subject_id);
	echo "<input type='hidden' name='USER TLS COUNT 1' value='" . count($user_tls) . "'>\n";
	
	if (count($user_tls) == 0) {
		$check_for_first_task = false;
		$user_tls = getDefaultTLS($user_id, $subject_id, $default_school_type_id);
		echo "<input type='hidden' name='USER TLS COUNT 2' value='" . count($user_tls) . "'>\n";
		
		$missions_array = getMissionsTwo($user_tls, $user_id, $subject_id);
	}
	else {
		// ********** Get all the missions associated with the subject ********** //
		$missions_array = getMissions($user_tls, $user_id, $subject_id);
	}
	

	// ********** GET ALL THE MISSIONS FOR ALL THE MEDALS FOR A GIVEN SUBJECT ********** //
	$missions = getAllMissions();
	
	$title = "Missions";
	include("includes/header.php");
	?>

		<script>
			$(document).ready(function() {
				var itemHeight = 88;
				var itemCol = 7
				var currentTop = 0;
				var containerHeight = Math.ceil($("#slider_inside > div > div").length / itemCol) * itemHeight;
				
				$("a#button_up").click(function () {
					
					if (Math.abs(currentTop) > 0) {
						$("#slider_inside > div").animate({"top":currentTop + itemHeight},{queue:false});
						currentTop += itemHeight;
					} 
					else {
						$("#slider_inside > div").animate({"top":currentTop + (itemHeight/2)},"fast").animate({"top":currentTop},"fast");
					}
					
				});
				
				$("a#button_dn").click(function () {
					
					if (Math.abs(currentTop) < (containerHeight - itemHeight)) {
						$("#slider_inside > div").stop().animate({"top":currentTop - itemHeight},{queue:false});
						currentTop -= itemHeight;
					} 
					else {
						$("#slider_inside > div").stop().animate({"top":currentTop - (itemHeight/2)},"fast").animate({"top":currentTop},"fast");
					}
					
				});	
				
			 });
		</script>

		<body class="blue" onload="checkForRedirect();">
		
			<div id="wrapper">
			
				<div id="header">
					<?php include("includes/topbar.php"); ?>
				</div>
				
				<div id="main">
				
					<div id="page_title">
						Missions
					</div>			
					
					<div class="three_column padding_top">
					
						<div class="content">
						
					
							<div class="slider_box">
						
								<ul class="missions">					
								
									<li>
								
										<div class="slider_title">
											<?=$subject['subject_name'] . ' - ' . $medal['medal_name'];?>
										</div>
										
										<div class="mission_side">
											<div class="medalImage" style='background: transparent url(<?='/file_view.php?id=' . $medal['profile_photo_id']; ?>);'>
												<span class="badge"><?=(int)$medal['missions_required'];?></span>
											</div>
											<a id="button_up">Up</a>
											<a id="button_dn">Down</a>
										</div>
										
										<div id="slider_inside" class="mission_boxes">
										
											<div id="missions_container">
			
			<?php						
				$completed_number = 0;
				for ($cntr = 0; $cntr < count($missions); $cntr++) {
				
					if ($missions[$cntr]->page_number == $medal_ord) {
					
						//////////echo "<input type='hidden' name='MISSION ID' value='" . $missions[$cntr]->mission_id . "'>\n";
						
						if ($missions[$cntr]->completed == true) {
							$completed_number++;
							createCompletedMissionDiv($missions[$cntr]->mission_id, $missions[$cntr]->mission_name, $missions[$cntr]->start_date, $completed_number);
						}
						else {
							if ($missions[$cntr]->inactive == false) 
								createInCompletedMissionDiv($missions[$cntr]->mission_id, $missions[$cntr]->mission_name, $missions[$cntr]->start_date, $missions[$cntr]->greg_start_date, $missions[$cntr]->future_mission, $missions[$cntr]->started_task); 
							else
								createInActiveMissionDiv($missions[$cntr]->mission_id, $missions[$cntr]->mission_name, $missions[$cntr]->start_date, $missions[$cntr]->page_number);
						}
					}
					
				}
			?>
			
												 <div class="mission button_back">
													<form name="back_to_medals" method="post" action="prof_medals.php">
														<input type="hidden" name="subject_id" id="subject_id" value="<?=$subject_id;?>">
														<a href="#" onClick="document.back_to_medals.submit();">Back to Medals</a>
													</form>
												</div>

											</div> <!-- missions_container -->
											
										</div> <!-- mission_boxes -->
										
									</li>
									
								</ul>
								
							</div> <!-- slider_box -->
							
						</div> <!-- content -->
						
					</div> <!-- three_column padding_top -->
					
				</div> <!-- main -->
				
				<div id="footer">
					<div class="footer_logo"></div>
					<div class="footer_logout"></div>
				</div> <!-- footer -->
			
			</div> <!-- wrapper -->
			
		</body>

	</html>
<?
}
else {
    header('Location: prof_medals.php');
    exit("<script>window.location = 'prof_medals.php';</script>");
}
?>