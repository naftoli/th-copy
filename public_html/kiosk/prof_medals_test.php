<?php 
include_once ("../header.php");
require_once('../file_save.php');
require_once('../calendar.php');
require_once('classes/user_tls.php');

session_start();

$current_date = unixtojd();
		
function get_medals($subject_id) {
	$echo_string = "<li>";
	
	$mission_accomplished = get_missions_accomplished($subject_id);
	
	$sql = "SELECT ms.subject_id, ms.medal_ord, profile_photo_id, medal_name, subject_name, subject_gold_image_id ";
	$sql = $sql . " FROM medals_subjects AS ms, subjects AS s, medals AS m ";
	$sql = $sql . " WHERE ms.subject_id=" . $subject_id . " ";
	$sql = $sql . " AND	ms.subject_id=s.subject_id "; 
	$sql = $sql . " AND ms.medal_ord=m.medal_ord ";
	$sql = $sql . " ORDER BY subject_id, medal_ord ";
	$rows = mysql_query($sql);
	$num_rows = mysql_num_rows($rows);
	
	$row_number = 0;
	while ($row = mysql_fetch_assoc($rows)) {		
		$row_number++;
				
		if ($row_number == 1) {
			$echo_string = $echo_string . "<div class='slider_title'>" . $row['subject_name'] . "</div>";
			$echo_string = $echo_string . "<div class='medalImage'>" . linkImgFile($row['subject_gold_image_id'], 200, 210) . "</div>";
			$echo_string = $echo_string . "<div class='medals'>";
		}		
		
		$missions_required = get_missions_required($subject_id, $row['medal_ord']);		
		
		if ($mission_accomplished >= $missions_required) {
			$echo_string = $echo_string . "<div class='active'>";
			$echo_string = $echo_string . "<div class='check_on'></div>";
		}
		else {
			$echo_string = $echo_string . "<div>";
		}
		
		$echo_string = $echo_string . "<a href='#' onclick='submitForm(" . $row['subject_id'] . "," . $row['medal_ord'] . ");'>";			
		$echo_string = $echo_string . linkImgFile($row['profile_photo_id'], 96, 100);		
		$echo_string = $echo_string . "</a>";
		$echo_string = $echo_string . "</div>";
				
		if ($row_number == $num_rows) {
			$echo_string = $echo_string . "</div>"; 
		}
	}

	$echo_string = $echo_string . "</li>";
	
	echo $echo_string;
}

function get_missions_required($subject_id, $medal_ord) {
	$missions_required = 0;
	
	$sql = "SELECT missions_required FROM medals_subjects WHERE subject_id=" . $subject_id . " AND medal_ord <=" . $medal_ord;
	$rows = mysql_query($sql);
	while ($row = mysql_fetch_assoc($rows)) {
		$missions_required = $missions_required + $row['missions_required'];
	}
	
	return $missions_required;	
}

function get_missions_accomplished($subject_id) {
	global $user_id;
	global $birth_date;
	
	$missions_accomplished = 0;
	
	$sql = "SELECT dtm.*, dtmm.mark_date ";	
	$sql = $sql . " FROM date_tasks_missions AS dtm ";
	$sql = $sql . " LEFT JOIN date_tasks_mission_marks AS dtmm ON dtmm.user_id=" . $user_id . " AND dtm.date_tasks_mission_id=dtmm.date_tasks_mission_id ";
	$sql = $sql . " WHERE dtm.subject_id=" . $subject_id . " ";
	$sql = $sql . " AND dtmm.mark_date > 0 ";
	
	$rows = mysql_query($sql);
	
	while ($row = mysql_fetch_assoc($rows)) {
		
		//if ($row['start_date'] > $birth_date) {
		//	echo "<input type='hidden' name='FOUND' value='*** FOUND ***'>\n";
		//}
		
		//echo "<input type='hidden' name='start_date' value='" . $row['start_date'] . "'>\n";
		//echo "<input type='hidden' name='birth_date' value='" . $birth_date . "'>\n";
			
		$missions_accomplished++;
	}
	
	return $missions_accomplished;
}

function getHebrewYear($jdDate) {
    $startdateh_arr = cal_from_jd($jdDate, CAL_JEWISH);
    return $startdateh_arr["year"];
}

function dateToGregorian($date) {
	$dates = cal_from_jd($date, CAL_GREGORIAN);
	return $dates['date'];
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

function getMissions($user_tls, $user_id, $subject_id) {
	global $ending_date;
	$ending_date = 0;

	$missions_array = array();
		
	$first_task_found = false;

	for ($cntr = 0; $cntr < count($user_tls); $cntr++) { 		
		getTLSMissions($missions_array, $first_task_found, $user_id, $subject_id, $user_tls[$cntr]->track_id, $user_tls[$cntr]->level, $user_tls[$cntr]->school_type_id, $user_tls[$cntr]->cutoff_date);	
	}
		
	return $missions_array;
}

function getTLSMissions(&$missions_array, &$first_task_found, $user_id, $subject_id, $track_id, $level, $school_type_id, $starting_date) {
	//echo "<input type='hidden' name='starting_date' value='" . $starting_date . "'>\n";
	
	global $completed_missions;
	global $ending_date;
	global $birth_date;
		
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
	
	echo "<input type='hidden' name='sqlStatement' value='" . $sqlStatement . "'>\n";
	
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
	
$user_id = $user['user_id'];

$user_row = mysql_fetch_assoc(mq("
SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state,dob,dob_he_offset, user_postal, user_country, user_phone,
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

echo "<input type='hidden' name='school_type_id' value='" . $user['school_type_id'] .  "'>\n";

$age = calcAge(dateToJD($user_row['dob']) + $user_row['dob_he_offset']);
$mizva_age = ($user_row['gender']=="M"?13:12);
$bar_mizva_year = getHebrewYear(dateToJD($user_row['dob'])+$user_row['dob_he_offset']) + $mizva_age;
$birth_date = jewishtojd(1, 1, $bar_mizva_year + 1);
$greg_birth_date = dateToGregorian($birth_date);

//echo "<input type='hidden' name='bar_mizva_year' value='" . $bar_mizva_year . "'>\n";
//echo "<input type='hidden' name='birth_date' value='" . $birth_date . "'>\n";
//echo "<input type='hidden' name='greg_birth_date' value='" . $greg_birth_date . "'>\n";

if (!isset($user_row['dob']) || $user_row['dob']==NULL || $user_row['dob']=='' || $user_row['dob']=='NULL') {
    $title = "Medals";
    include("includes/header.php");
    ?>
	
    <body class="blue">
    	<div id="wrapper">
        	<div id="header">
              <?php include("includes/topbar.php"); ?>
      		</div>
        	<div id="main">
            	<div id="page_title">Medals</div>
            	<div class="three_column padding_top">
              		<div class="content">
                    	<div id="slider">
                    		<ul>
                    			<li style="height:100%;vertical-align:middle;">
    								<P style="width:100%;vertical-align:middle;height:100%;text-align:center;margin-top:100px;"><?=T_('Please update your date of birth for access to this page.')?></P>
    							</li>
    						</ul>
    					</div>
    				</div>
    			</div>
    		</div>
    	</div>
    </body>
    </html>
    <?
    exit();
}
else {
	// ********** GLOBALS **********//
	$ending_date = 0;
	$check_for_first_task = true;
	
	// ***** Get all the subjects for a given user ***** //
	$subjects = array();
	$sql = "SELECT subjects.subject_id, subject_name, subject_type, subjects.subject_black_image_id ";
	$sql = $sql . " FROM subjects ";  
	$sql = $sql . " JOIN school_subjects ON (subjects.subject_id = school_subjects.subject_id AND school_id=" . $user['school_id'] . ")  ";
	$sql = $sql . " WHERE subject_type NOT IN ('school_points', 'home_points') ";
	$sql = $sql . " ORDER BY subject_id ";
	$rows = mysql_query($sql);
	while ($row = mysql_fetch_assoc($rows)) {
		array_push($subjects, $row['subject_id']);		
	}
	// ***** Get all the subjects for a given user ***** //
}

$title = "Medals";
include("includes/header.php");
?>

<script type="text/javascript">
	function submitForm(subject_id, medal_ord) {
		document.getElementById("subject_id").value = subject_id;
		document.getElementById("medal_ord").value = medal_ord;
		document.forms["medals"].submit();
	}	
</script>

	<body class="blue">
	
		<div id="wrapper">
		
			<div id="header">
				<?php include("includes/topbar.php"); ?>
			</div>
			
			<div id="main">
				<div id="page_title">
					Medals
				</div>
				
				<div class="three_column padding_top">
				
					<div class="content">
					
						<div id="slider">
							<ul>
							
							<?php
								// ***** Get all the medals for a given subject ***** //
								for ($cntr = 0; $cntr < count($subjects); $cntr++) {

									// ********** All the TRACKS, LEVELS, and SCHOOL TYPE IDs for a given student and subject ********** //
									$user_tls = getStudentTrackLevelSchoolTypes($user_id, $subjects[$cntr]);
									
									echo "\n<input type='hidden' name='SUBJECT ID' value='" . $subjects[$cntr] . "'>\n";
									echo "<input type='hidden' name='USER TLS COUNT' value='" . count($user_tls) . "'>\n";
									
									// ********** Get all the missions associated with the subject ********** //
									if (count($user_tls) == 0) {
										$check_for_first_task = false;
										$user_tls = getDefaultTLS($user_id, $subject_id, $default_school_type_id);
		
										$missions_array = getMissionsTwo($user_tls, $user_id, $subject_id);
									}
									else {
										$missions_array = getMissions($user_tls, $user_id, $subject_id);
									}									
									echo "<input type='hidden' name='missions_array count' value='" . count($missions_array) . "'>\n";
									
									get_medals($subjects[$cntr]);
								}
								// ***** Get all the medals for a given subject ***** //
							?>
								
							</ul>
						</div> <!-- slider -->
						
					</div> <!-- content -->
					
				</div> <!-- three_column -->
				
			</div> <!-- main -->
			
			<div id="footer">
				<?php include("includes/bottombar.php"); ?>
			</div> <!-- footer -->
	  
			<form name="medals" id="medals" method="post" action="prof_medal.php">
				<input type="hidden" name="subject_id" id="subject_id">
				<input type="hidden" name="medal_ord" id="medal_ord">
			</form>
	  
		</div> <!-- wrapper -->

	</body>

<?php include("includes/footer.php"); ?>
