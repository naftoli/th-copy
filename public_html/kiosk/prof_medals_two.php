<?php 
include_once ("../header.php");
require_once('../file_save.php');
require_once('../calendar.php');

session_start();

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

//***** GLOBALS *****//
$user_id = $user['user_id'];
$mizva_age = ($user_row['gender']=="M"?13:12);
$bar_mizva_year = getHebrewYear(dateToJD($user_row['dob'])+$user_row['dob_he_offset']) + $mizva_age;
$birth_date = jewishtojd ( 1, 1, $bar_mizva_year+1 );
$greg_birth_date = dateToGregorian($birth_date);
$current_date = unixtojd();
$completed_missions = 0;
$list_items = array();
$first_medal = 0;
$last_medal = 0;
//***** GLOBALS *****//

//***********************************************************************//
//****************************** FUNCTIONS ******************************//
function getHebrewYear($jdDate) {
    $startdateh_arr = cal_from_jd($jdDate, CAL_JEWISH);
    return $startdateh_arr["year"];
}

function dateToGregorian($date) {
	$dates = cal_from_jd($date, CAL_GREGORIAN);
	return $dates['date'];
}

function get_tlses($subject_id) {
	global $user_id;
	
	$tlses = array();
	
	$sql = "SELECT track_id, level, school_type_id, start_date ";
	$sql = $sql . " FROM date_tasks_mission_marks AS dtmm ";
	$sql = $sql . " JOIN date_tasks_missions AS dtm USING (date_tasks_mission_id) ";
	$sql = $sql . " WHERE dtmm.user_id=" . $user_id . "  ";
	$sql = $sql . " AND dtmm.subject_id=" . $subject_id . " "; 
	$sql = $sql . " ORDER BY start_date ";
		
	$rows = mysql_query($sql);
	while ($row = mysql_fetch_assoc($rows)) {
		$tls = new tls($user_id, $subject_id, $row['track_id'], $row['level'], $row['school_type_id'], $row['start_date']);
		array_push($tlses, $tls);
	}
	
	return $tlses;
}

function get_track_level_school_types($user_id, $subject_id, $tlses) {
	$track_level_school_types = array();
	$start_dates = array();
	$end_dates = array();

	$tls = "";
	$previous_tls = "";			
	$previous_track_id = "";
	$previous_level = "";
	$previous_school_type_id = "";
	$last_row = count($tlses) - 1;
				
	for ($cntr = 0; $cntr < count($tlses); $cntr++) {
		$tls = $tlses[$cntr]->track_id . $tlses[$cntr]->level . $tlses[$cntr]->school_type_id;
				
		if ($tls != $previous_tls) {
			array_push($start_dates, $tlses[$cntr]->start_date);
						
			if ($previous_tls != "") {	
				$track_level_school_type = new track_level_school_type($user_id, $subject_id, $previous_track_id, $previous_level, $previous_school_type_id);
				array_push($track_level_school_types, $track_level_school_type);

				array_push($end_dates, $tlses[$cntr]->start_date);
			}
		}			

		if ($cntr == $last_row) {
			array_push($end_dates, 9999999);
			$track_level_school_type = new track_level_school_type($user_id, $subject_id, $tlses[$cntr]->track_id, $tlses[$cntr]->level, $tlses[$cntr]->school_type_id);
			array_push($track_level_school_types, $track_level_school_type);
		}
					
		$previous_tls = $tls;
		$previous_track_id = $tlses[$cntr]->track_id;
		$previous_level = $tlses[$cntr]->level;
		$previous_school_type_id = $tlses[$cntr]->school_type_id;
	}

	for ($cntr = 0; $cntr < count($track_level_school_types); $cntr++) {			
		$track_level_school_types[$cntr]->set_dates($start_dates[$cntr], $end_dates[$cntr]);
	}

	return $track_level_school_types;
}

function get_missions($track_level_school_type) {
	global $missions;
	global $user_id;
	global $current_date;
	global $birth_date;
	
	$mission_count = 0;
	
	//$sql = "SELECT dtm.*, dtmm.mark_date ";
	$sql = "SELECT * ";
	$sql = $sql . " FROM date_tasks_missions AS dtm ";	
	//$sql = $sql . " LEFT JOIN date_tasks_mission_marks AS dtmm ON (dtmm.user_id=" . $user_id . " ";
	//$sql = $sql . " AND dtm.date_tasks_mission_id=dtmm.date_tasks_mission_id) ";
	$sql = $sql . " WHERE dtm.subject_id=" . $track_level_school_type->subject_id . " ";
	$sql = $sql . " AND dtm.track_id=" . $track_level_school_type->track_id . " ";
	$sql = $sql . " AND dtm.level=" . $track_level_school_type->level . " ";
	$sql = $sql . " AND dtm.school_type_id=" . $track_level_school_type->school_type_id . " ";	
	$sql = $sql . " ORDER BY dtm.start_date ";
	
	
	$rows = mysql_query($sql);
	while ($row = mysql_fetch_assoc($rows)) 
	{

		if ($row['start_date'] > $current_date)
		{
			$mission_count++;
		}
		
		//if ($row['mark_date'] > 0 || $row['start_date'] > $current_date)
		//	$mission_count++;
	}
	
	return $mission_count;
}

function get_number_of_finished_missions($user_id, $subject_id)
{
	$sql = "SELECT count(*) as finished_missions ";
	$sql = $sql . "FROM date_tasks_mission_marks AS dtmm ";
	$sql = $sql . "WHERE dtmm.user_id=" . $user_id . " ";
	$sql = $sql . "AND dtmm.subject_id=" . $subject_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$finished_missions = $row['finished_missions'];
	
	return $finished_missions;
}


function get_medals($subject_id, $missions_count) {
	global $user_id;
	global $medal_ord;
	global $finished_missions;
	global $user_id;
	
	$medals = array();
	
	$missions_required = 0;
	
	$sql = "SELECT * FROM medals_subjects WHERE subject_id=" . $subject_id . " ORDER BY medal_ord ";
	
	$rows = mysql_query($sql);
	while ($row = mysql_fetch_assoc($rows)) {
		
		if ($missions_required <= $missions_count) {
			$medal = new medal($row['medal_ord'], $row['missions_required'], $row['profile_photo_id'], true);			
			$missions_required = $missions_required + $row['missions_required'];			
			
			$finished_missions = get_number_of_finished_missions($user_id, $subject_id);
			
			if ($finished_missions >= $missions_required) 
				$medal->set_completed(true);
			else
			
			$medal->set_completed(false);
			
			array_push($medals, $medal);
		}
		else {
			break;
		}
		
	}
		
	return $medals;
}

function get_all_medals($subject_id) {
	global $user_id;

	$medals = array();
	
	$sql = "SELECT ms.*, mm.date_awarded FROM medals_subjects AS ms LEFT JOIN medal_marks AS mm ON mm.medal_ord=ms.medal_ord AND mm.subject_id=ms.subject_id AND mm.user_id=" . $user_id . " WHERE ms.subject_id=" . $subject_id;	
	$rows = mysql_query($sql);

	while ($row = mysql_fetch_assoc($rows)) {
		$medal = new medal($row['medal_ord'], $row['missions_required'], $row['profile_photo_id'], false);

		if ($row['date_awarded'] > 0) 
			$medal->set_completed(true);
		else
			$medal->set_completed(false);

		array_push($medals, $medal);
	}

	return $medals;
}

function list_items($subject_id, $medals) {
	$first_medal = 0;
	$first_name = "first" . $subject_id;
	$last_medal = 0;
	$last_name = "last" . $subject_id;
	
	$return_string = "";
	$return_string = $return_string . "\t<div class='medals'>\n";
	
	for ($cntr = 0; $cntr < count($medals); $cntr++) {	
		if ($first_medal == 0)
			$first_medal = $medals[$cntr]->medal_ord ;
				
		$last_medal = $medals[$cntr]->medal_ord;
			
		if ($medals[$cntr]->completed == true) 
		{
		
			$return_string = $return_string . "\t\t<div class='active'>\n";
			$return_string = $return_string . "\t\t<div class='check_on'></div>\n";
		}
		else {
			$return_string = $return_string . "\t\t<div>\n";
		}	
			
		if ($medals[$cntr]->clickable == true) 
			$return_string = $return_string . "\t\t\t<a href='#' onclick='submitForm(" . $subject_id . ", " . $medals[$cntr]->medal_ord . ", \"" . $first_name . "\", \"" . $last_name . "\");'>\n";
				
		$return_string = $return_string . "\t\t\t\t" . linkImgFile($medals[$cntr]->profile_photo_id, 96, 100) . "\n";		
			
		if ($medals[$cntr]->clickable == true) 
			$return_string = $return_string . "\t\t\t</a>\n";
				
		$return_string = $return_string . "\t\t</div>\n";
		
	}
	
	$return_string = $return_string . "\t<input type='hidden' name='" . $first_name . "' id='" . $first_name . "' value='" . $first_medal . "'>\n";
	$return_string = $return_string . "\t<input type='hidden' name='" . $last_name . "' id='" . $last_name . "' value='" . $last_medal . "'>\n";
	
	$return_string = $return_string . "\t</div>\n";
	
	return $return_string;
}
//****************************** FUNCTIONS ******************************//
//***********************************************************************//

//*********************************************************************//
//****************************** CLASSES ******************************//
class tls {
	var $user_id;
	var $subject_id;
	var $track_id;
	var $level;
	var $school_type_id;
	var $start_date;
	
	function tls($user_id, $subject_id, $track_id, $level, $school_type_id, $start_date) {
		$this->user_id = $user_id;
		$this->subject_id = $subject_id;
		$this->track_id = $track_id;
		$this->level = $level;
		$this->school_type_id = $school_type_id;
		$this->start_date = $start_date;
	}
	
}

class subject {
	var $subject_id;
	var $subject_name;
	var $subject_gold_image_id;
	
	function subject ($subject_id, $subject_name, $subject_gold_image_id) {
		$this->subject_id = $subject_id;
		$this->subject_name = $subject_name;
		$this->subject_gold_image_id = $subject_gold_image_id;
	}
	
}

class user_tls {
	var $subject_id;
	var $track_id;
	var $level;
	var $school_type_id;
	var $cutoff_date;
	var $greg_cutoff_date;
	
	function user_tls ($subject_id, $track_id, $level, $school_type_id, $cutoff_date) {
		$this->subject_id = $subject_id;
		$this->track_id = $track_id;
		$this->level = $level;
		$this->school_type_id = $school_type_id;
		$this->cutoff_date = $cutoff_date;
		$this->greg_cutoff_date = $this->gregorian_date($cutoff_date);
	}
	
	function gregorian_date($date) {
		$dates = cal_from_jd($date, CAL_GREGORIAN);
		return $dates['date'];
	}	
}

class list_item {
	var $subject_id; 
	var $subject_name;
	var $subject_gold_image_id;
	var $medal_ord;
	var $profile_photo_id;
	var $accomplished;
	var $clickable;
	
	function list_item($subject_id, $subject_name, $subject_gold_image_id, $medal_ord, $profile_photo_id, $accomplished, $clickable) {
		$this->subject_id = $subject_id; 
		$this->subject_name = $subject_name;
		$this->subject_gold_image_id = $subject_gold_image_id;
		$this->medal_ord = $medal_ord;
		$this->profile_photo_id = $profile_photo_id;
		$this->accomplished = $accomplished;
		$this->clickable = $clickable;
	}
	
}

class track_level_school_type {
	var $user_id;
	var $subject_id;
	var $track_id;
	var $level;
	var $school_type_id;
	var $start_date;
	var $end_date;
	
	function track_level_school_type($user_id, $subject_id, $track_id, $level, $school_type_id) {
		$this->user_id = $user_id;
		$this->subject_id = $subject_id;
		$this->track_id = $track_id;
		$this->level = $level;
		$this->school_type_id = $school_type_id;
	}
	
	function set_dates($start_date, $end_date) {
		$this->start_date = $start_date;	
		$this->end_date = $end_date;		
	}
	
}

class mission {	
	var $date_tasks_mission_id;
	var $school_type_id;
	var $subject_id;
	var $level;
	var $track_id;
	var $mission_name;
	var $mission_number;
	var $mission_group;
	var $mission_description;
	var $mission_value;
	var $start_date;
	var $end_date;
	
	var $mark_date;
	var $completed;
	var $future;
	
	var $page_number;
	
	var $inactive;
	
	function mission($row, $current_date) {
		$this->date_tasks_mission_id = $row['date_tasks_mission_id'];
		$this->school_type_id = $row['school_type_id'];
		$this->subject_id = $row['subject_id'];
		$this->level = $row['level'];
		$this->track_id = $row['track_id'];
		$this->mission_name = $row['mission_name'];
		$this->mission_number = $row['mission_number'];
		$this->mission_group = $row['mission_group'];
		$this->mission_description = $row['mission_description'];
		$this->mission_value = $row['mission_value'];
		$this->start_date = $row['start_date'];
		$this->end_date = $row['end_date'];
		
		$this->mark_date = $row['mark_date'];
		
		if ($row['mark_date'] > 0)
			$this->completed = true;
		else	
			$this->completed = false;
			
		if ($row['start_date'] > $current_date)
			$this->future = true;
		else	
			$this->future = false;	
			
		$this->page_number = 0;
		$this->inactive = false;
	}
	
	function set_page_number($page_number) {
		$this->page_number = $page_number;
	}
	
}

class medal {
	var $medal_ord;
	var $missions_required;
	var $profile_photo_id;
	var $completed;
	var $clickable;
	
	function medal($medal_ord, $missions_required, $profile_photo_id, $clickable) {
		$this->medal_ord = $medal_ord;
		$this->missions_required = $missions_required;
		$this->profile_photo_id = $profile_photo_id;
		$this->clickable = $clickable;
	}
	
	function set_completed($completed) {
		$this->completed = $completed;
	}
}
//****************************** CLASSES ******************************//
//*********************************************************************//

//********** SUBJECTS **********//
$subjects = array();
$sql = "SELECT subjects.subject_id, subject_name, subject_type, subjects.subject_gold_image_id ";
$sql = $sql . " FROM subjects ";  
$sql = $sql . " JOIN school_subjects ON (subjects.subject_id = school_subjects.subject_id AND school_id=" . $user['school_id'] . ")  ";
$sql = $sql . " WHERE subject_type NOT IN ('school_points', 'home_points') ";
$sql = $sql . " ORDER BY subject_id ";
$rows = mysql_query($sql);
while ($row = mysql_fetch_assoc($rows)) {
	$subject = new subject($row['subject_id'], $row['subject_name'], $row['subject_gold_image_id']);
	array_push($subjects, $subject);		
}
//********** SUBJECTS **********//

$title = "Medals";
include("includes/header.php");
?>

<script type="text/javascript">
	function submitForm(subject_id, medal_ord, first_name, last_name) {
		document.getElementById("first_medal").value = document.getElementById(first_name).value;
		document.getElementById("last_medal").value = document.getElementById(last_name).value;
		document.getElementById("subject_id").value = subject_id;
		document.getElementById("medal_ord").value = medal_ord;
		document.forms["medal"].submit();
	}	
</script>

	<body class="blue">
	
		<form name="medal" id="medal" method="post" action="prof_medal.php">
			<input type="hidden" name="subject_id" id="subject_id">
			<input type="hidden" name="medal_ord" id="medal_ord">
			<input type="hidden" name="first_medal" id="first_medal">
			<input type="hidden" name="last_medal" id="last_medal">			
		</form>

		<div id="wrapper">
		
			<div id="header">
				<?php include("includes/topbar.php"); ?>
			</div>
			
			<div id="main">
				<div id="page_title">
					Medals
				</div>
				
				<?
				if (!$user_row['dob'] > 0) {				
				?>
				<div align='center'>
				<p><br />To access the medals page you need to have a valid birthdate.<br/>
				Please speak to your base commander.
				</div>
				<? } else { ?>
				
				<div class="three_column padding_top">
				
					<div class="content">
					
						<div id="slider">
						
							<ul>
							
								<?php
									$echo_string = "";
									
									for ($cntr = 0; $cntr < count($subjects); $cntr++) {
										$echo_string = $echo_string . "\n<li>\n";
										$echo_string = $echo_string . "\t<div class='slider_title'>" . $subjects[$cntr]->subject_name . "</div>\n";
										$echo_string = $echo_string . "\t<div class='medalImage'>" . linkImgFile($subjects[$cntr]->subject_gold_image_id, 200, 210) . "</div>\n";
										
										$first_medal = 0;
										$last_medal = 0;
										
										$tlses = get_tlses($subjects[$cntr]->subject_id);
										$track_level_school_types = get_track_level_school_types($user_id, $subjects[$cntr]->subject_id, $tlses);
										
										$missions = array();
										$missions_count = 0;

										for ($cntr1 = 0; $cntr1 < count($track_level_school_types); $cntr1++) {
											$missions_count = $missions_count + get_missions($track_level_school_types[$cntr1]);
										}

										if ($subjects[$cntr]->subject_id != 27) {
											$medals = get_medals($subjects[$cntr]->subject_id, $missions_count);										
										}
										else {
											$medals = get_all_medals($subjects[$cntr]->subject_id);
										}
										
										$echo_string = $echo_string . list_items($subjects[$cntr]->subject_id, $medals);
										
										$echo_string = $echo_string . "</li>\n";
									}
									
									echo $echo_string;
								?>
									
							</ul>
							
						</div> <!-- slider -->
						
					</div> <!-- content -->
					
				</div> <!-- three_column -->
				
			</div> <!-- main -->
			
			<? } ?>
			
			<div id="footer">
				<?php include("includes/bottombar.php"); ?>
			</div> <!-- footer -->
	  	  
		</div> <!-- wrapper -->

	</body>

<?php include("includes/footer.php"); ?>