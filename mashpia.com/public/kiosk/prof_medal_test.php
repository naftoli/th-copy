<?php 
require_once ("../header.php");
require_once('../file_save.php');
require_once('../calendar.php');

$redirect = "false";
$subject_id = gri('subject_id', -1);
$medal_ord = gri('medal', -1);

$user_id = gri('user_id', -1);
$subject_id = gri('subject_id', -1);
$medal_ord = gri('medal_ord', -1);

$tlsArray = array();
$completed_tasks = countCompletedTasks($user_id, $subject_id);

if ($subject_id > 0 && $medal_ord > 0) {

	// ********** USER INFORMATION ********** //
	$sqlStatement = "SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
		   user_city, user_state, user_postal, user_country, user_phone,
		   user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id, team_name, user_start_date,
		   school_name, school_number, school_city, school_state, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, school_id,
		   rank_name, rank_image_id, rank_color, track_id , level
	FROM users
		 LEFT JOIN schools USING (school_id)
		 LEFT JOIN institutions USING (inst_id)
		 LEFT JOIN classes USING (school_id, class_id)
		 LEFT JOIN teams USING (school_id, team_id)
		 LEFT JOIN (SELECT user_id, MAX(rank_ord) rank_ord FROM rank_marks WHERE user_id = " . $user['user_id'] . " GROUP BY user_id) rank USING (user_id)
		 LEFT JOIN ranks USING (rank_ord)
		 LEFT JOIN (SELECT * FROM user_tracks WHERE user_id = " . $user['user_id'] . " AND subject_id=" . $subject_id . ") user_tracks USING (user_id)
	WHERE user_id = " . $user['user_id'] . " ";
	
	$user_row = mysql_fetch_assoc(mq($sqlStatement));
	// ********** USER INFORMATION ********** //

	// ********** SUBJECT **********//
	$sql = "SELECT * FROM subjects WHERE subject_id=" . $subject_id;
	$subject = mysql_fetch_assoc(mq($sql));
	$subject_name = $subject['subject_name'];
	// ********** SUBJECT **********//
	
	// ********** MISSIONS REQUIRED (THIS MEDAL) ********** //	
	$sql = "SELECT * FROM medals_subjects JOIN medals USING (medal_ord) WHERE subject_id=" . $subject_id . " AND medal_ord=" . $medal_ord;	
	$medal = mysql_fetch_assoc(mq($sql));
	$medal_name = $medal['medal_name'];
	//$req_missions = $medal['missions_required'];
	//$profile_photo_id = $medal['missions_required'];
	// ********** MISSIONS REQUIRED (THIS MEDAL) ********** //	

	$first_task_number = 0;
	$last_task_number = 0;	
	$tasks_completed = 0;
	$break_flag = false;	
	
	// ********** MISSIONS REQUIRED (ALL) ********** //	
	$sql = "SELECT * FROM medals_subjects JOIN medals USING (medal_ord) WHERE subject_id=" . $subject_id;
	$missions_required = mysql_query($sql);
	$required_missions = array();
	$row_number = 0;
	while ($row = mysql_fetch_assoc($missions_required)) {
		$row_number++;
		
		if ($row_number < $medal_ord) 
			$first_task_number = $first_task_number + $row['missions_required'];
		elseif ($row_number == $medal_ord) 
			$last_task_number = $row['missions_required'];
			
		$required_missions[] = $row['missions_required'];
	}
	
	$last_task_number = $last_task_number + $first_task_number;
	// ********** MISSIONS REQUIRED (ALL) ********** //		
	
	
	// ********** Get the different TRACKS, LEVELS, and SCHOOL TYPES for a given student **********
	//$sqlSelect = "SELECT track_id, level, school_type_id, start_date ";
	//$sqlFrom = " FROM date_tasks_mission_marks AS dtmm ";
	//$sqlJoin = " JOIN date_tasks_missions AS dtm USING (date_tasks_mission_id) ";
	//$sqlWhere = " WHERE dtmm.user_id=" . $user_id . "  ";
	//$sqlWhere = $sqlWhere . " AND dtmm.subject_id=" . $subject_id . " "; 
	//$sqlGroupBy = " GROUP BY track_id, level, school_type_id ";
	//$sqlOrderBy = " ORDER BY start_date ";
	//$sqlStatement = $sqlSelect . $sqlFrom . $sqlJoin . $sqlWhere . $sqlGroupBy . $sqlOrderBy;
	//$results1 = mysql_query($sqlStatement);
	//$results2 = mysql_query($sqlStatement);
	//$total_results = mysql_num_rows($results2);
	// ********** Get the different TRACKS, LEVELS, and SCHOOL TYPES for a given student **********
	
}
else {
	$redirect = "true";
}

//*******************************************************************************************//
//**************************************** FUNCTIONS ****************************************//
function getDateTasksMissions($user_id, $subject_id, $track_id, $level, $school_type_id, $starting_date) {
	global $first_task_number;
	global $last_task_number;	
	global $tasks_completed;
	global $completed_tasks;
	global $break_flag;
	
	$sqlSelect = "SELECT dtm.*, dtmm.mark_date ";	
	$sqlFrom = " FROM date_tasks_missions AS dtm ";
	$sqlJoin = " LEFT JOIN date_tasks_mission_marks AS dtmm ON dtmm.user_id=" . $user_id . " AND dtm.date_tasks_mission_id=dtmm.date_tasks_mission_id ";
	$sqlWhere = " WHERE dtm.track_id=" . $track_id . " ";
	$sqlWhere = $sqlWhere . " AND dtm.level=" . $level . " ";
	$sqlWhere = $sqlWhere . " AND dtm.school_type_id=" . $school_type_id . " ";
	$sqlWhere = $sqlWhere . " AND dtm.subject_id=" . $subject_id . " ";
	$sqlWhere = $sqlWhere . " AND dtm.start_date < " . $starting_date . " ";
	$sqlOrderBy = " ORDER BY start_date ";
	$sqlStatement = $sqlSelect . $sqlFrom . $sqlJoin . $sqlWhere . $sqlOrderBy;	
	
	$results = mysql_query($sqlStatement);
	
	//<input type="hidden" name="COMPLETED TASKS" id="COMPLETED TASKS" value="8">
	//<input type="hidden" name="FIRST TASK NUMBER" value="5">
	//<input type="hidden" name="LAST TASK NUMBER" value="11">
	
	$row_num = 0;
	
	while ($row = mysql_fetch_assoc($results)) {
		$row_num++;
		
		
		if ($last_task_number > $completed_tasks && $row_num >= $last_task_number) 
			break;
		
		if ($tasks_completed >= $last_task_number ) {
			$break_flag = true;
			break;
		}
		
		if ($row['mark_date']) {
			$tasks_completed++;
					
			if ($tasks_completed > $first_task_number)
				createCompletedMissionDiv($row['date_tasks_mission_id'], $row['mission_name'], $tasks_completed);
		}
		else {				
			if ($tasks_completed > $first_task_number)
				createInCompletedMissionDiv($row['date_tasks_mission_id'], $row['mission_name']);
				
		}
		
	}
	
}

function createCompletedMissionDiv($date_tasks_mission_id, $mission_name, $completed_number) {
	echo "<a href='prof_medal_mission.php?mission=" . $date_tasks_mission_id . "'>";
	echo "<div class='mission'>";
	echo "<div class='number'>#" . $completed_number . "</div>";	
	echo "<div class='date'>" . $mission_name . "</div>";
	echo "<div class='meter' style='background-position:100% 0;'></div>";
	echo "<div class='check_on'></div>";
	echo "</div>";
	echo "</a>";
}

function createInCompletedMissionDiv($date_tasks_mission_id, $mission_name) {
	echo "<a href='prof_medal_mission.php?mission=" . $date_tasks_mission_id . "'>";
	echo "<div class='mission'>";
	echo "<div class='number'>&nbsp;</div>";	
	echo "<div class='date'>" . $mission_name . "</div>";
	echo "<div class='meter' style='background-position:100% 0;'></div>";
	echo "<div class='check_off'></div>";
	echo "</div>";
	echo "</a>";
}

function countCompletedTasks($user_id, $subject_id) {
	global $tlsArray;
	
	$completed_tasks = 0;
	
	$sqlSelect = "SELECT track_id, level, school_type_id, start_date ";
	$sqlFrom = " FROM date_tasks_mission_marks AS dtmm ";
	$sqlJoin = " JOIN date_tasks_missions AS dtm USING (date_tasks_mission_id) ";
	$sqlWhere = " WHERE dtmm.user_id=" . $user_id . "  ";
	$sqlWhere = $sqlWhere . " AND dtmm.subject_id=" . $subject_id . " "; 
	$sqlGroupBy = " GROUP BY track_id, level, school_type_id ";
	$sqlOrderBy = " ORDER BY start_date ";
	$sqlStatement = $sqlSelect . $sqlFrom . $sqlJoin . $sqlWhere . $sqlGroupBy . $sqlOrderBy;	
	$rows = mysql_query($sqlStatement);
	
	while ($row = mysql_fetch_assoc($rows)) {
		$tempArray = array($row['track_id'], $row['level'], $row['school_type_id'], $row['start_date']);
		$tlsArray[] = $tempArray;
	}

	for ($cntr = 0; $cntr < count($tlsArray); $cntr++) {
		$completed_tasks = $completed_tasks + getCompletedTasks($user_id, $subject_id, $tlsArray[$cntr][0], $tlsArray[$cntr][1], $tlsArray[$cntr][2]);
	}
		
	return $completed_tasks;
}

function getCompletedTasks($user_id, $subject_id, $track_id, $level, $school_type_id) {
	$completed_tasks = 0;
	
	$sqlSelect = "SELECT dtm.*, dtmm.mark_date ";	
	$sqlFrom = " FROM date_tasks_missions AS dtm ";
	$sqlJoin = " JOIN date_tasks_mission_marks AS dtmm ON dtmm.user_id=" . $user_id . " AND dtm.date_tasks_mission_id=dtmm.date_tasks_mission_id ";
	$sqlWhere = " WHERE dtm.track_id=" . $track_id . " ";
	$sqlWhere = $sqlWhere . " AND dtm.level=" . $level . " ";
	$sqlWhere = $sqlWhere . " AND dtm.school_type_id=" . $school_type_id . " ";
	$sqlWhere = $sqlWhere . " AND dtm.subject_id=" . $subject_id . " ";
	$sqlStatement = $sqlSelect . $sqlFrom . $sqlJoin . $sqlWhere;	
	
	$rows = mysql_query($sqlStatement);
	
	while ($row = mysql_fetch_assoc($rows)) {
		$completed_tasks++;
	}
	
	return $completed_tasks;
}

//**************************************** FUNCTIONS ****************************************//
//*******************************************************************************************//


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
				} else {
					$("#slider_inside > div").animate({"top":currentTop + (itemHeight/2)},"fast").animate({"top":currentTop},"fast");
				}
			});
			$("a#button_dn").click(function () {
				if (Math.abs(currentTop) < (containerHeight-itemHeight)) {
					$("#slider_inside > div").stop().animate({"top":currentTop - itemHeight},{queue:false});
					currentTop -= itemHeight;
				} else {
					$("#slider_inside > div").stop().animate({"top":currentTop - (itemHeight/2)},"fast").animate({"top":currentTop},"fast");
				}
			});	
		 });
		
		function checkForRedirect() {
			var redirect = "<?=$redirect;?>";
			
			if (redirect == "true") 
				window.location = "prof_medals.php";
		}
	</script>

	<body class="blue" onload="checkForRedirect();">
		<input type="hidden" name="COMPLETED TASKS" id="COMPLETED TASKS" value="<?=$completed_tasks;?>">
		<input type="hidden" name="FIRST TASK NUMBER" value="<?=$first_task_number;?>">
		<input type="hidden" name="LAST TASK NUMBER" value="<?=$last_task_number;?>">
		
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
										<?=$subject_name . ' - ' . $medal_name;?>
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
											for ($cntr = 0; $cntr < count($tlsArray); $cntr++) {
												
												// *** Get the date of the next track, level, school type if there is one *** //
												if ($cntr < (count($tlsArray) - 1)) 
													$starting_date = $tlsArray[$cntr + 1][3];														
												else
													$starting_date = 999999999;
													
												getDateTasksMissions($user_id, $subject_id, $tlsArray[$cntr][0], $tlsArray[$cntr][1], $tlsArray[$cntr][2], $starting_date);
											}
											
											//$starting_dates = array();
											//while ($row = mysql_fetch_assoc($results1)) {
											//	$starting_dates[] = $row['start_date'];
											//}
											
											//$row_number = 0;
											//while ($row = mysql_fetch_assoc($results2)) {
											//	$row_number++;
												
											//	if ($row_number < $total_results) 
											//		$starting_date = $starting_dates[$row_number];
											//	else 
											//		$starting_date = 999999999;

											//	echo getDateTasksMissions($user_id, $subject_id, $row['track_id'], $row['level'], $row['school_type_id'], $starting_date);					
												
											//	if ($break_flag == true)
											//		break;
												
											//}			
											
?>

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
