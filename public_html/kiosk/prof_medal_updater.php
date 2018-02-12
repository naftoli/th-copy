<?php 
require_once ("../header.php");
require_once('../file_save.php');
require_once('../calendar.php');


$user_id = $_GET['user_id'];
$subject_id = $_GET['subject_id'];


$medal_ord = gri('medal_ord', -1);
$first_medal = gri('first_medal', -1);
$last_medal = gri('last_medal', -1);
$current_date = unixtojd();

// ***** GLOBALS ***** //
$completed_missions = 0;
$missions = array();
$previous_medal = 0;

if ($medal_ord > 1)
	$previous_medal = $medal_ord - 1;

$mizva_age = ($user_row['gender']=="M"?13:12);
$bar_mizva_year = getHebrewYear(dateToJD($user_row['dob'])+$user_row['dob_he_offset']) + $mizva_age;
$birth_date = jewishtojd ( 1, 1, $bar_mizva_year+1 );	
$greg_birth_date = dateToGregorian($birth_date);

$starting_mission = 0;



// *************************************************************************** //
// ********************************* CLASSES ********************************* //
class tls 
{
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
	
	function mission($sql, $row, $current_date) {
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

class mission_required {
	var $subject_id;
	var $medal_ord;
	var $missions_required;
	
	function mission_required ($subject_id, $medal_ord, $missions_required) {
		$this->subject_id = $subject_id;
		$this->medal_ord = $medal_ord;
		$this->missions_required = $missions_required ;
	
	}
}
// ********************************* CLASSES ********************************* //
// *************************************************************************** //

// ***************************************************************************** //
// ********************************* FUNCTIONS ********************************* //
function getHebrewYear($jdDate) {
    $startdateh_arr = cal_from_jd($jdDate, CAL_JEWISH);
    return $startdateh_arr["year"];
}

function get_tlses() {
	global $user_id;
	global $subject_id;
	
	$tlses = array();
		
	$sql = "SELECT dtm.track_id, dtm.level, school_type_id, start_date ";
	$sql = $sql . " FROM date_tasks_mission_marks AS dtmm ";
	$sql = $sql . " JOIN date_tasks_missions AS dtm USING (date_tasks_mission_id) ";
	$sql = $sql . " JOIN user_tracks AS ut ON ( ut.user_id = dtmm.user_id 
					AND ut.subject_id = dtmm.subject_id
					AND ut.track_id = dtm.track_id ) "; //added to stick to user defined ladder
	$sql = $sql . " WHERE dtmm.user_id=" . $user_id . "  ";
	$sql = $sql . " AND dtmm.subject_id=" . $subject_id . " "; 
	$sql = $sql . " GROUP BY dtm.track_id, dtm.level, school_type_id, start_date ";
	$sql = $sql . " ORDER BY start_date ";
	echo "<input type='hidden' name='TLS' value='" . $sql . "'>\n";
	
	$rows = mysql_query($sql);
	while ($row = mysql_fetch_assoc($rows)) {
		$tls = new tls($user_id, $subject_id, $row['track_id'], $row['level'], $row['school_type_id'], $row['start_date']);
		array_push($tlses, $tls);
	}
	
	return $tlses;
}

function dateToGregorian($date) {
	$dates = cal_from_jd($date, CAL_GREGORIAN);
	return $dates['date'];
}

function get_missions($track_level_school_type) {
	global $missions;
	global $user_id;
	global $current_date;
	global $birth_date;
	global $completed_missions;
	
	$sql = "SELECT distinct dtm.*, dtmm.mark_date "; //added distinct to get only mission numbers
	$sql = $sql . " FROM date_tasks_missions AS dtm ";	
	//$sql = $sql . " LEFT JOIN date_tasks_mission_marks AS dtmm ON dtmm.user_id=" . $user_id . " ";
	$sql = $sql . " JOIN date_tasks_mission_marks AS dtmm ON dtmm.user_id=" . $user_id . " ";
	$sql = $sql . " AND dtm.date_tasks_mission_id=dtmm.date_tasks_mission_id "; 
	$sql = $sql . " JOIN date_tasks AS dt ON dtmm.date_tasks_mission_id = dt.date_tasks_mission_id "; //added to get only missions with tasks
	$sql = $sql . " WHERE dtm.subject_id=" . $track_level_school_type->subject_id . " ";
	$sql = $sql . " AND dtm.track_id<=" . $track_level_school_type->track_id . " ";
	$sql = $sql . " AND dtm.level<=" . $track_level_school_type->level . " ";
	$sql = $sql . " AND dtm.school_type_id=" . $track_level_school_type->school_type_id . " ";
	//$sql = $sql . " AND dtm.start_date >= " . $track_level_school_type->start_date . " ";
	//$sql = $sql . " AND dtm.start_date < " . $track_level_school_type->end_date . " ";
	//$sql = $sql . " AND dtm.start_date <= " . $birth_date . " ";
	$sql = $sql . " ORDER BY dtm.start_date ";	
	echo "<input type='hidden' name='MISSIONS' value='" . $sql . "'>\n";
	
	/*
	$sql = "SELECT dtm.*, dtmm.mark_date 
		FROM  `date_tasks_mission_marks` AS dtmm
		LEFT JOIN date_tasks_missions AS dtm
		USING ( date_tasks_mission_id ) 
		WHERE dtmm.user_id = $user_id 
		AND dtmm.subject_id = $track_level_school_type->subject_id 
		ORDER BY dtmm.date_tasks_mission_id";
	*/
	
	$rows = mysql_query($sql);
	while ($row = mysql_fetch_assoc($rows)) {
		$mission = new mission($sql, $row, $current_date);
		if ($mission->completed == true)
			$completed_missions++;
		array_push($missions, $mission);
	}
}

function getSubject($subject_id) {
	$sql = "SELECT * FROM subjects WHERE subject_id=" . $subject_id;
	$result = mysql_fetch_assoc(mq($sql));
	return $result;
}

function get_missions_required() {
	global $subject_id;
	global $medal_ord;
	
	$missions_required = array();
	$sql = "SELECT * FROM medals_subjects WHERE subject_id=" . $subject_id;
	$rows = mysql_query($sql);
	while ($row = mysql_fetch_assoc($rows)) {
		$mission_required = new mission_required($subject_id, $medal_ord, $row['missions_required']);
		array_push($missions_required, $mission_required);
	}
		
	return $missions_required;
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

function createCompletedMissionDiv($date_tasks_mission_id, $mission_name, $start_date) {
	global $mission_number;
	
	$hebrew_start_date = dateToHebrewSplit($start_date);
	
	echo "\n\t<div class='mission'>";
	echo "\n<a href='prof_medal_mission.php?mission=" . $date_tasks_mission_id . "'>";
	echo "\n\t\t<div class='number'>#" . $mission_number . "</div>";	
	if (isset($mission_name)) echo "\n\t\t<div class='date'>" . $mission_name . "</div>";
	if (isset($hebrew_start_date[2])) echo "\n\t\t<div class='date'>" . $hebrew_start_date[2] . "</div>";
	echo "\n\t\t<div class='meter' style='background-position:0% 0;'></div>";
	echo "\n</a>";
	echo "\n\t\t<div class='check_on'></div>";
	
	echo "\n\t</div>\n";
		
}

function future_mission($date_tasks_mission_id, $mission_name, $start_date) {
	global $subject_id;
	global $mission_number;
	
	$hebrew_start_date = dateToHebrewSplit($start_date);
	$greg_start_date = dateToGregorian($start_date);
	
	echo "\n\t<div class='mission'>\n";	
	echo "\n\t\t<div class='number'>#" . $mission_number . "</div>";	
	if ($subject_id != 12 && $subject_id != 40)
		echo "\n\t\t<div class='date'>" . $mission_name . "</div>";
	else
		echo "\n\t\t<div class='date'>&nbsp;</div>";
	echo "\n\t\t<div class='date'>" . $hebrew_start_date[2] . "</div>";
	echo "\n\t\t<div class='meter' style='background-position:100% 0;'></div>";
		
	echo "\n\t</div>\n";
	
}

function tasks_started($mission_id) {
	global $user_id;
	
	$found = false;
	
	$sqlSelect = "SELECT * ";
	$sqlFrom = " FROM date_tasks ";
	$sqlJoin = " LEFT JOIN labels ON date_tasks.label_id = labels.label_id ";
	$sqlJoin = $sqlJoin . " LEFT JOIN date_tasks_dates USING (date_task_id) ";
	$sqlJoin = $sqlJoin . " LEFT JOIN (SELECT * FROM date_tasks_marks WHERE user_id = " . $user_id . " GROUP BY date_task_id) date_tasks_marks USING (date_task_id) ";  
	$sqlWhere = " WHERE date_tasks.date_tasks_mission_id = " . $mission_id . " ";
	//$sqlWhere = $sqlWhere . " AND date_tasks.mandatory_qty > 0 ";
	//$sqlWhere = $sqlWhere . " AND date_tasks_marks.mark_quantity > 0 ";
	$sqlWhere = $sqlWhere . " AND ((date_tasks.mandatory_qty > 0 AND date_tasks_marks.mark_quantity > 0) OR (date_tasks_marks.mark_date > 0)) ";
	$sqlGroupBy = " GROUP BY date_tasks.ord ";
	$sql = $sqlSelect . $sqlFrom . $sqlJoin . $sqlWhere . $sqlGroupBy;
	
	$rows = mysql_query($sql);
		
	while ($row = mysql_fetch_assoc($rows)) {
		$found = true;
	}

	return $found;
}

function incomplete_mission($date_tasks_mission_id, $mission_name, $start_date) {
	global $mission_number;
	global $subject_id;
	
	$hebrew_start_date = dateToHebrewSplit($start_date);
	$greg_start_date = dateToGregorian($start_date);
	
	echo "\n\t<div class='mission'>\n";	
	echo "\n<a href='prof_medal_mission.php?mission=" . $date_tasks_mission_id . "'>";
	echo "\n\t\t<div class='number'>&nbsp;</div>";
	echo "\n\t\t<div class='date'>" . $mission_name . "</div>";
	echo "\n\t\t<div class='date'>" . $hebrew_start_date[2] . "</div>";
	
	$started = tasks_started($date_tasks_mission_id);
	
	if ($started == true)
		echo "\n\t\t<div class='meter' style='background-position:50% 0;'></div>";
	else
		echo "\n\t\t<div class='meter' style='background-position:100% 0;'></div>";
	
	echo "\n</a>";
	
	echo "\n\t\t<div class='check_off'></div>";
		
	echo "\n\t</div>\n";
	
}

function inactive_mission($date_tasks_mission_id, $mission_name, $start_date) {	
	global $mission_number;

	$echo_string = "";
	
	$hebrew_start_date = dateToHebrewSplit($start_date);
	$greg_start_date = dateToGregorian($start_date);
	
	$echo_string = $echo_string . "\n<div class='mission inactive'>\n";	
	$echo_string = $echo_string . "\n<input type='hidden' name='MISSION ID' value='" . $date_tasks_mission_id . "'>\n";	
	$echo_string = $echo_string .  "\n\t\t<div class='number'>#" . $mission_number . "</div>";
	$echo_string = $echo_string .  "\t<div class='date'>" . $mission_name . "</div>\n";
	$echo_string = $echo_string .  "\t<div class='date'>" . $hebrew_start_date[2] . "</div>\n";
	//$echo_string = $echo_string .  "\t<div class='date'>" . $greg_start_date . "</div>\n";
	$echo_string = $echo_string .  "\n\t\t<div class='check_off'></div>";
	$echo_string = $echo_string .  "</div>\n";
	
	echo $echo_string;
	
}

// ********************************* FUNCTIONS ********************************* //
// ***************************************************************************** //

// ********** SUBJECT **********//
$subject = getSubject($subject_id);
// ********** SUBJECT **********//

// ********** MEDAL ********** //
$sql = "SELECT m.medal_name, ms.missions_required, ms.profile_photo_id FROM medals AS m JOIN medals_subjects AS ms USING (medal_ord) WHERE m.medal_ord=" . $medal_ord . " AND ms.subject_id=" . $subject_id;
$medal = mysql_fetch_assoc(mq($sql));
// ********** MEDAL ********** //

// ********** MISSIONS REQUIRED ********** //
$missions_required = get_missions_required();
// ********** MISSIONS REQUIRED ********** //

$tlses = get_tlses();
$track_level_school_types = get_track_level_school_types($user_id, $subject_id, $tlses);

for ($cntr1 = 0; $cntr1 < count($track_level_school_types); $cntr1++) {
	get_missions($track_level_school_types[$cntr1]);
}

$page_number = 0;
$completed = 0;
for ($cntr = 0; $cntr < count($missions); $cntr++) {
	if ($missions[$cntr]->completed == true || $missions[$cntr]->future == true) 
		$completed++;
		
	$missions[$cntr]->set_page_number(($page_number + 1));
	
	if ($completed == $missions_required[$page_number]->missions_required) {
		$page_number++;
		$completed = 0;
	}
		
	if ($page_number == $medal_ord)
		break;
}

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
		
<body class="blue">

	<div id="wrapper">
			
		<div id="header">
		
			<div class="org">
			
				<?php //include("includes/topbar.php"); ?>
				
				<div class="nav">
					<ul>
						<!--<li class="icon_back"><a href="#" onclick="javascript:history.back(); return false">Back</a></li>-->
						<li class="icon_home"><a href="../statement.php">Home</a></li>
						<li class="icon_logout"><a href="../logout.php?n=kiosk.php">Logout</a></li>
					</ul>
				</div>
				
				<div class="org_photo">
					<?=(!is_null($user_row['school_logo_id']) ? linkImgFile($user_row['school_logo_id'],100,100) : '');?>
				</div>
				
				Base: #<?=$user_row['school_number']?><br>
				<?=$user_row['school_name']?><br>
				<?=$user_row['rank_name'].' '.$user_row['first'].' '.$user_row['last']?>
				
			</div>		
			
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
			$mission_number = 1;
			
			echo "<input type='hidden' name='MISSION COUNT' value='" . count($missions) . "'>\n";
			echo "<input type='hidden' name='MEDAL ORD' value='" . $medal_ord . "'>\n";
			
			$completed_counter = 0;
			$incomplete_counter = 0;
			
			for ($cntr = 0; $cntr < count($missions); $cntr++) {				
				
				if ($missions[$cntr]->page_number == $medal_ord) {
				
					echo "<input type='hidden' name='COUNTER' value='" . $cntr . "'>\n";
					
					if ($missions[$cntr]->completed == true) 
					{
						$completed_counter++;
						echo "<input type='hidden' name='COMPLETED' value='COMPLETED'>\n";
						createCompletedMissionDiv($missions[$cntr]->date_tasks_mission_id, $missions[$cntr]->mission_name, $missions[$cntr]->start_date);
						$mission_number++;
					}
					else 
					{
						$incomplete_counter++;
						
						if ($missions[$cntr]->future == false) 
						{
							echo "<input type='hidden' name='INCOMPLETE' value='INCOMPLETE'>\n";
							incomplete_mission($missions[$cntr]->date_tasks_mission_id, $missions[$cntr]->mission_name, $missions[$cntr]->start_date);
						}
						else 
						{
							echo "<input type='hidden' name='FUTURE' value='FUTURE'>\n";
							future_mission($missions[$cntr]->date_tasks_mission_id, $missions[$cntr]->mission_name, $missions[$cntr]->start_date);
							$mission_number++;						
						}
					}
					
				}
			}
			
			if ($medal_ord == $last_medal) {
				for ($cntr = 0; $cntr < count($missions); $cntr++) {
					if ($missions[$cntr]->completed == false && $missions[$cntr]->future == false) {
						inactive_mission($missions[$cntr]->date_tasks_mission_id, $missions[$cntr]->mission_name, $missions[$cntr]->start_date);
						$mission_number++;
					}
				}
			}
		?>
	
											<? if ($medal_ord > $first_medal) { ?>
											<div class="mission button_back">
												<form name="previous_medal" method="post" action="prof_medal.php">
													<input type="hidden" name="subject_id" id="subject_id" value="<?=$subject_id;?>">
													<input type="hidden" name="medal_ord" id="medal_ord" value="<?=$previous_medal;?>">
													<input type="hidden" name="first_medal" id="first_medal" value="<?=$first_medal;?>">
													<input type="hidden" name="last_medal" id="last_medal" value="<?=$last_medal;?>">													
													<a href="#" onClick="document.previous_medal.submit();">Previous Medal</a>
												</form>
											</div>
											<? } ?>
											
											<? if ($medal_ord < $last_medal) { ?>
											<div class="mission button_back">
												<form name="netx_medal" method="post" action="prof_medal.php">												
													<input type="hidden" name="subject_id" id="subject_id" value="<?=$subject_id;?>">
													<input type="hidden" name="medal_ord" id="medal_ord" value="<?=($medal_ord + 1);?>">
													<input type="hidden" name="first_medal" id="first_medal" value="<?=$first_medal;?>">
													<input type="hidden" name="last_medal" id="last_medal" value="<?=$last_medal;?>">																										
													<a href="#" onClick="document.netx_medal.submit();">Next<br />Medal</a>
												</form>
											</div>
											<? } ?>

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
	
		<input type="hidden" name="completed_counter" value="<?=$completed_counter;?>">
		<input type="hidden" name="incomplete_counter" value="<?=$incomplete_counter;?>">
		
	</body>

</html>
