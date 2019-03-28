<? 
$admin_auth = array('school','user'); 

require('header.php'); 
require_once('calendar.php');
include("classes/subject.php");

$class_id = 0;
$user_id = 0;
$subject_id = 0;
$date_list = "";
$start_date = 0; 
$end_date = 1; 
$school_id = 45;

$days_of_the_week = array("M", "T", "W", "T", "F", "S", "S");

$today = gregoriantojd(date("n"), date("j"), date("Y"));

$action = "";
if (isset($_POST['action'])) {
	$action = $_POST['action'];
	
	$class_id = $_POST['class_id'];
	$user_id = $_POST['user_id'];
	$subject_id = $_POST['subject_id'];
	$date_list = explode(":", $_POST['date_list']);
	$start_date = $date_list[0]; 
	$end_date = $date_list[1]; 

	if ($action == "produce_report") {		
		//$date_tasks = array();
		$daily_tasks = array();
		$weekly_tasks = array();
		$shabbos_tasks = array();
		$no_label_tasks = array();
		
		include("camps/includes/classes/user.php");
		include("camps/includes/classes/user_track.php");
		include("classes/date_tasks_mission.php");
		include("classes/daily_task.php");
		include("classes/weekly_task.php");
		include("classes/shabbos_task.php");
		include("classes/no_label_task.php");
		include("classes/task.php");
		include("classes/date_tasks_mark.php");
		
		$sql = "SELECT * FROM users WHERE user_id=" . $user_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$user = new user($row);
		$user->get_user_tracks($subject_id, $start_date, $end_date);

		// ***** DAILY SORTING BY LABEL ***** //
		$daily_labels = array();
		$daily_dates = array();
		for ($dtno = 0; $dtno < count($daily_tasks); $dtno++) {
			$daily_task = $daily_tasks[$dtno];			
			$label_name = $daily_task->label_name;
			$start_date = $daily_task->start_date;
			$end_date = $daily_task->end_date;
			
			if (!in_array($label_name, $daily_labels)) {
				array_push($daily_labels, $label_name);
				array_push($daily_dates, array($start_date, $end_date));
			}
		}
		// ***** DAILY SORTING BY LABEL ***** //
		
		// ***** WEEKLY SORTING BY LABEL AND SEQUENCE NUMBER ***** //
		$weekly_labels = array();
		$weekly_sequence_numbers = array();
		$weeklies = array();
		
		for ($wtno = 0; $wtno < count($weekly_tasks); $wtno++) {
			$weekly_task = $weekly_tasks[$wtno];
			
			$label_name = $weekly_task->label_name;
			$sequence_number = $weekly_task->sequence_number;
			$key = $label_name . ":" . $sequence_number;
			
			if (!in_array($label_name, $weekly_labels)) {
				array_push($weekly_labels, $label_name);
			}
			
			if (!in_array($key, $weekly_sequence_numbers)) {
				array_push($weekly_sequence_numbers, $key);
			}
			
			$weeklies[$key] = $weekly_task;
			
		}		
		sort($weekly_labels);
		sort($weekly_sequence_numbers);
		// ***** WEEKLY SORTING BY LABEL AND SEQUENCE NUMBER ***** //
		
		// ***** SHABBOS SORTING BY LABEL AND SEQUENCE NUMBER ***** //
		$shabbos_labels = array();
		$shabbos_sequence_numbers = array();
		$shabbos = array();
		
		for ($stno = 0; $stno < count($shabbos_tasks); $stno++) {
			$shabbos_task = $shabbos_tasks[$stno];
			
			$label_name = $shabbos_task->label_name;
			$sequence_number = $shabbos_task->sequence_number;
			$key = $label_name . ":" . $sequence_number;
			
			if (!in_array($label_name, $shabbos_labels)) {
				array_push($shabbos_labels, $label_name);
			}
			
			if (!in_array($key, $shabbos_sequence_numbers)) {
				array_push($shabbos_sequence_numbers, $key);
			}
			
			$shabbos[$key] = $shabbos_task;
			
		}		
		sort($shabbos_labels);
		sort($shabbos_sequence_numbers);
		// ***** SHABBOS SORTING BY LABEL AND SEQUENCE NUMBER ***** //

		// ***** NO LABELS SORTING BY SUBJECT AND MISSION NUMBER ***** //
		$no_label_subjects = array();
		$no_label_dates = array();
		$no_labels = array();
		
		for ($nltno = 0; $nltno < count($no_label_tasks); $nltno++) {
			$no_label_task = $no_label_tasks[$nltno];			
			$subject_name = $no_label_task->subject_name;
			$mission_name = $no_label_task->mission_name;
			$key = $subject_name . ":" . $mission_name;
			
			if (!in_array($key, $no_label_subjects)) {
				array_push($no_label_subjects, $key);
				array_push($no_label_dates, array($no_label_task->start_date, $no_label_task->end_date));
			}			
		}
		// ***** NO LABELS SORTING BY SUBJECT AND MISSION NUMBER ***** //
	
	}
}

// ***** USERS ***** //
include_once("camps/includes/classes/user.php");
$users = array();
if ($class_id == 0) 
	$sql = "SELECT u.* FROM users AS u LEFT JOIN classes AS c USING (school_id, class_id) WHERE u.school_id=" . $school_id . " AND user_registered IS NOT NULL ORDER BY c.class_grade, c.class_sub,last, first"; 
else
	$sql = "SELECT u.* FROM users AS u LEFT JOIN classes AS c USING (school_id, class_id) WHERE u.school_id=" . $school_id . " AND user_registered IS NOT NULL AND u.class_id=" . $class_id . " ORDER BY c.class_grade, c.class_sub,last, first"; 
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$user_select = new user($row);
	$user_select->get_school_class();
	array_push($users, $user_select);
}
// ***** USERS ***** //

// ***** CLASSES ***** //
include_once("camps/includes/classes/school_class.php");
$classes = array();
$sql = "SELECT * FROM classes WHERE school_id=" . $school_id . " ORDER BY class_grade, class_sub";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$class = new school_class($row);
	array_push($classes, $class);
}
// ***** CLASSES ***** //


// ***** SUBJECTS ***** //
$subjects_select = array();
$sql = "SELECT DISTINCT s.* FROM schools JOIN subjects AS s USING (inst_id) JOIN school_subjects USING (school_id, subject_id) WHERE school_id=" . $school_id . " ORDER BY s.subject_ord, s.subject_name"; 
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$subject = new subject($row);
	array_push($subjects_select, $subject);
}
// ***** SUBJECTS ***** //	

// ***** REPORT DATES ***** //
$today = unixtojd();	
$day_of_the_week = date("N");
if ($day_of_the_week != 7)
	$sunday = $today - $day_of_the_week;
else
	$sunday = $today;
$report_start_date = $sunday + 7;

include("classes/report.php");
$reports = array();
//$sql = "SELECT * FROM reports WHERE report_type='mission_cover_sheet' AND visibility != 'none' AND start_date > " . $report_start_date . " ORDER BY start_date";
$sql = "SELECT * FROM reports WHERE report_type='mission_cover_sheet' AND visibility != 'none' AND start_date < " . $report_start_date . " ORDER BY start_date";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$report = new report($row);
	array_push($reports, $report);
}
// ***** REPORT DATES ***** //

$start_date_greg = jdtogregorian($start_date);
$end_date_greg = jdtogregorian($end_date);
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Marking Missions - Tzivos Hashem Management System</title>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		
		<script type="text/javascript">
		</script>

	</head>

	<body>
		
		<? include('admin_header.php'); ?>
		
		<input type="hidden" name="start_date" value="<?=$start_date_greg;?>">
		<input type="hidden" name="end_date" value="<?=$end_date_greg;?>">
		
		<script type="text/javascript" src="scripts/functions.js"></script>
		<script type="text/javascript" src="scripts/jquery.styleselect.js"></script>
		
		<script type="text/javascript">
			var school_id = <?=$school_id;?>;
			var start_date = <?=$start_date;?>;
			var end_date = <?=$end_date;?>;
			var check_all = false;
			var check = "";
			var date_task_ids = "";
			var mark_dates = "";
			var checked = false;
			var user_id = <?=$user_id;?>;
			var today = <?=$today;?>;
			var save = false;
						
			$(function(){
				$('.marking_list div select').each(function() {
					if (!$(this).find('option:selected').next().val()) $(this).siblings('a.next').addClass('disabled');
					if (!$(this).find('option:selected').prev().val()) $(this).siblings('a.prev').addClass('disabled');
				});
				
				$('.marking_list div a.next').click(function(){
					$(this).siblings('select').find('option:selected').next().attr('selected','selected').parent().change();
				});
				
				$('.marking_list div a.prev').click(function(){
					$(this).siblings('select').find('option:selected').prev().attr('selected','selected').parent().change();
				});
				
				$(".user_list select, .class_list select, .campaign_list select").sSelect().change(function () {
					$(this).closest('form').submit();
				})
				
				$(".date_list select").sSelect().change(function () {
					document.forms["date_tasks_report"].submit();
				})
				
				// ***** CHECK ALL ***** //
				$('.marking_list .check_all a').click(function() {
					date_task_ids = "";
					mark_dates = "";
					
					check_all = true;
					var tasks_div = document.getElementById("tasks_div");
					var unchecked_checkboxes = $(tasks_div).find('span.checkbox_span.unchecked');
					$(unchecked_checkboxes).trigger("click");					
					
					if (date_task_ids.length > 0) {
						date_task_ids = date_task_ids.substr(0, date_task_ids.length - 1);
						mark_dates = mark_dates.substr(0, mark_dates.length - 1);
						
						var function_name = "add_date_tasks_marks";					
						var parameters = [user_id, date_task_ids, mark_dates];
						var url = "add_functions.php?function_name=" + function_name + "&parameters=" + parameters;
						
						$.getJSON(url, function(success) {	
							if (success == false) {
								alert("Update not performed.");
							}
						});	
					}
				})
				// ***** CHECK ALL ***** //
				
				// ***** UNCHECK ALL ***** //
				$('.marking_list .uncheck_all a').click(function() {
					date_task_ids = "";
					mark_dates = "";
														
					check_all = true;					
					var tasks_div = document.getElementById("tasks_div");
					$(tasks_div).find('span.checkbox_span.checked').trigger("click");
					
					if (date_task_ids.length > 0) {
						date_task_ids = date_task_ids.substr(0, date_task_ids.length - 1);
						mark_dates = mark_dates.substr(0, mark_dates.length - 1);
						
						var function_name = "delete_date_tasks_marks";					
						var parameters = [user_id, date_task_ids, mark_dates];
						var url = "delete_functions.php?function_name=" + function_name + "&parameters=" + parameters;
						
						$.getJSON(url, function(success) {	
							if (success == false) {
								alert("Update not performed.");
							}
						});					
					}
				})
				// ***** UNCHECK ALL ***** //
								
				$(".marking_list #display_submit").hide();
			});
			
			function update_weekly_shabbos_date_task(span, date_task_id, mark_date) {
				if ($(span).hasClass('checked')) {
					if (check_all == true) {
						date_task_ids = date_task_ids + date_task_id + ":";
						mark_dates = mark_dates + mark_date + ":";
					}
					
					var function_name = "delete_task_mark";
					var url = "delete_functions.php";
					
					var parent_div = $(span).parent("div");
					$(parent_div).removeClass("cell checkbox checked");						
					$(parent_div).addClass("cell checkbox");
					$(parent_div).addClass("unchecked");
					$(span).removeClass('checked'); 
					$(span).addClass("unchecked");						
				}
				else {
					if (check_all == true) {
						date_task_ids = date_task_ids + date_task_id + ":";
						mark_dates = mark_dates + mark_date + ":";
					}
					
					var function_name = "add_task_mark";
					var url = "add_functions.php";
				
					var parent_div = $(span).parent("div");
					$(parent_div).removeClass("cell checkbox unchecked");						
					$(parent_div).addClass("cell checkbox checked");						
					$(span).removeClass('unchecked'); 
					$(span).addClass("checked");						
				}
				
				if (check_all == false) {
					var parameters = [user_id, date_task_id, mark_date];
					url = url + "?function_name=" + function_name + "&parameters=" + parameters;
					$.getJSON(url, function(success) {	
						if (success == false) {
							alert("Update not performed.");
						}
					});					
				}				
			}			
			
			function update_daily_date_task(span, date_task_id, mark_date) {
				if ($(span).hasClass('checked')) {
					if (check_all == true) {
						date_task_ids = date_task_ids + date_task_id + ":";
						mark_dates = mark_dates + mark_date + ":";
					}
					
					var function_name = "delete_daily_task_mark";
					var url = "delete_functions.php";
					
					var parent_div = $(span).parent("div");
					$(parent_div).removeClass("cell checkbox checked");						
					$(parent_div).addClass("cell checkbox");
					$(parent_div).addClass("unchecked");
					$(span).removeClass('checked'); 
					$(span).addClass("unchecked");						
				}
				else {
					if (check_all == true) {
						date_task_ids = date_task_ids + date_task_id + ":";
						mark_dates = mark_dates + mark_date + ":";
					}
					
					var function_name = "add_daily_task_mark";
					var url = "add_functions.php";
					
					var parent_div = $(span).parent("div");
					$(parent_div).removeClass("cell checkbox unchecked");						
					$(parent_div).addClass("cell checkbox checked");						
					$(span).removeClass('unchecked'); 
					$(span).addClass("checked");						
				}
				
				if (check_all == false) {
					var parameters = [user_id, date_task_id, mark_date];
					url = url + "?function_name=" + function_name + "&parameters=" + parameters;					
					alert(url);
					$.getJSON(url, function(success) {	
						if (success == false) {
							alert("Update not performed.");
						}
					});					
				}
			}
			
			function submit_form() {
				var form = document.getElementById("update_date_tasks_report");
				
				// ***** DAILY ***** //
				var daily_tasks = "";
				var daily_div = document.getElementById("daily_div");
				var daily_checked_checkboxes = $(daily_div).find('span.checkbox_span.checked');
				for (cbno = 0; cbno < daily_checked_checkboxes.length; cbno++) {
					var checkbox = daily_checked_checkboxes[cbno];
					daily_tasks = daily_tasks + checkbox.id + ":true;";
				}
				var daily_unchecked_checkboxes = $(daily_div).find('span.checkbox_span.unchecked');
				for (cbno = 0; cbno < daily_unchecked_checkboxes.length; cbno++) {
					var checkbox = daily_unchecked_checkboxes[cbno];
					daily_tasks = daily_tasks + checkbox.id + ":false;";
				}
				form.elements["daily_tasks"].value = daily_tasks;
				// ***** DAILY ***** //
				
				// ***** WEEKLY ***** //
				var weekly_tasks = "";
				var weekly_div = document.getElementById("weekly_div");
				var weekly_checked_checkboxes = $(weekly_div).find('span.checkbox_span.checked');
				for (cbno = 0; cbno < weekly_checked_checkboxes.length; cbno++) {
					var checkbox = weekly_checked_checkboxes[cbno];
					weekly_tasks = weekly_tasks + checkbox.id + ":true;";
				}				
				var weekly_unchecked_checkboxes = $(weekly_div).find('span.checkbox_span.unchecked');
				for (cbno = 0; cbno < weekly_unchecked_checkboxes.length; cbno++) {
					var checkbox = weekly_unchecked_checkboxes[cbno];
					weekly_tasks = weekly_tasks + checkbox.id + ":false;";
				}	
				form.elements["weekly_tasks"].value = weekly_tasks;
				// ***** WEEKLY ***** //
				
				// ***** SHABBOS ***** //
				var shabbos_tasks = "";
				var shabbos_div = document.getElementById("shabbos_div");
				var shabbos_checked_checkboxes = $(shabbos_div).find('span.checkbox_span.checked');
				for (cbno = 0; cbno < shabbos_checked_checkboxes.length; cbno++) {
					var checkbox = shabbos_checked_checkboxes[cbno];
					shabbos_tasks = shabbos_tasks + checkbox.id + ":true;";
				}								
				var shabbos_unchecked_checkboxes = $(shabbos_div).find('span.checkbox_span.unchecked');
				for (cbno = 0; cbno < shabbos_unchecked_checkboxes.length; cbno++) {
					var checkbox = shabbos_unchecked_checkboxes[cbno];
					shabbos_tasks = shabbos_tasks + checkbox.id + ":false;";
				}	
				form.elements["shabbos_tasks"].value = shabbos_tasks;
				// ***** SHABBOS ***** //
				
				// ***** NO LABELS ***** //
				var no_labels_tasks = "";
				var no_labels_div = document.getElementById("no_labels_div");
				var no_labels_checked_checkboxes = $(no_labels_div).find('span.checkbox_span.checked');
				for (cbno = 0; cbno < no_labels_checked_checkboxes.length; cbno++) {
					var checkbox = no_labels_checked_checkboxes[cbno];
					no_labels_tasks = no_labels_tasks + checkbox.id + ":true;";
				}												
				var no_labels_unchecked_checkboxes = $(no_labels_div).find('span.checkbox_span.unchecked');
				for (cbno = 0; cbno < no_labels_unchecked_checkboxes.length; cbno++) {
					var checkbox = no_labels_unchecked_checkboxes[cbno];
					no_labels_tasks = no_labels_tasks + checkbox.id + ":false;";
				}
				form.elements["no_labels_tasks"].value = no_labels_tasks;
				// ***** NO LABELS ***** //				
			}
		</script>
		
		<div class="body left">
						
			<H1>Marking Missions</H1>
				
			<form name="date_tasks_report" id="date_tasks_report" action="date_tasks_report.php" method="post" accept-charset="UTF-8">
				<input type="hidden" name="action" id="action" value="produce_report">
				
				<div class="infobox2 marking_list clearfix">
				
					<!-- ***** CLASS ***** -->
					<div class="class_list select_box">
						<a class="prev button">
							<span class="icon"></span>
							<span class="label"><?=T_('Previous Platoon')?></span>
						</a>
						
						<select name="class_id">
							<option value="0"><?=T_('All Platoons')?>
							<? for ($cno = 0; $cno < count($classes); $cno++) : ?>
								<? $class = $classes[$cno]; ?>
								<? if ($class->class_id == $class_id) : ?>
								<option selected value="<?=$class->class_id;?>"><?=$class->class_grade;?>-<?$class->class_sub;?></option>
								<? else : ?>
								<option value="<?=$class->class_id;?>"><?=$class->class_grade;?>-<?$class->class_sub;?></option>
								<? endif; ?>
							<? endfor; ?>
						</select>
						
						<a class="next button">
							<span class="icon"></span>
							<span class="label"><?=T_('Next Platoon')?></span>
						</a>						
					</div>	
					<!-- ***** CLASS ***** -->
					
					<!-- ***** USER ***** -->
					<div class="user_list select_box">	
						<a class="prev button">
							<span class="icon"></span><span class="label"><?=T_('Previous Student')?></span>
						</a>
							
						<select name="user_id" class="sSelect">
							<? for ($uno = 0; $uno < count($users); $uno++) : ?>
								<? $user_select = $users[$uno]; ?>
								<? if ($user_select->user_id == $user_id) : ?>
								<option selected value="<?=$user_select->user_id;?>"><?=$user_select->school_class->class_grade;?>-<?=$user_select->school_class->class_sub;?> <?=$user_select->first;?> <?=$user_select->last;?></option>
								<? else : ?>
								<option value="<?=$user_select->user_id;?>"><?=$user_select->school_class->class_grade;?>-<?=$user_select->school_class->class_sub;?> <?=$user_select->first;?> <?=$user_select->last;?></option>
								<? endif; ?>
							<? endfor; ?>
						</select>
							
						<a class="next button">
							<span class="icon"></span><span class="label"><?=T_('Next Student')?></span>
						</a>	
					</div>					
					<!-- ***** USER ***** -->
										
					<!-- ***** SUBJECT ***** -->
					<div class="campaign_list select_box">						
						<a class="prev button">
							<span class="icon"></span><span class="label"><?=T_('Previous Campaign')?></span>
						</a>
						
						<select name="subject_id" id="subject_id">
							<option value="-1"><?=T_('All Campaigns')?>
							<? for ($sno = 0; $sno < count($subjects_select); $sno++) : ?>
								<? $subject_select = $subjects_select[$sno]; ?>
								<? if ($subject_select->subject_id == $subject_id) : ?>
								<option selected value="<?=$subject_select->subject_id;?>"><?=$subject_select->subject_name;?></option>
								<? else : ?>
								<option value="<?=$subject_select->subject_id;?>"><?=$subject_select->subject_name;?></option>
								<? endif; ?>
							<? endfor; ?>
						</select>
						
						<a class="next button">
							<span class="icon"></span><span class="label"><?=T_('Next Campaign')?></span>
						</a>					
					</div>
					<!-- ***** SUBJECT ***** -->
					
					<!-- ***** WEEKLY PERIOD ***** -->
					<div class="date_list select_box">					
						<a class="prev button">
							<span class="icon"></span>
							<span class="label"><?=T_('Previous Week')?></span>
						</a>
						
						<select name="date_list" class="sSelect">
							<? for ($rno = 0; $rno < count($reports); $rno++) : ?>
								<? $report = $reports[$rno]; ?>
								<? if ($report->start_date == $start_date) : ?>
								<option selected value="<?=$report->start_date;?>:<?=$report->end_date;?>"><?=$report->report_name;?> - <?=jdtogregorian($report->start_date);?></option>
								<? else : ?>
								<option value="<?=$report->start_date;?>:<?=$report->end_date;?>"><?=$report->report_name;?> - <?=jdtogregorian($report->start_date);?></option>								
								<? endif; ?>
							<? endfor; ?>
						</select>
						
						<a class="next button">
							<span class="icon"></span><span class="label"><?=T_('Next Week')?></span>
						</a>
					</div>
					<!-- ***** WEEKLY PERIOD ***** -->
					
					<center>
						<input class="submit" type="submit" value="GO">
					</center>
					

					<div class="select_box check_all clearfix">
						<a class="button">
							<span class="icon"></span><?=T_('Check All')?>
						</a>
					</div>
					
					<div class="select_box uncheck_all clearfix">
						<a class="button">
							<span class="icon"></span><?=T_('Uncheck All')?>
						</a>						
					</div>

				</div>				

			</form>

<DIV id="tasks_div">
	<form name="update_date_tasks_report" id="update_date_tasks_report" action="date_tasks_report.php" method="post" accept-charset="UTF-8">
		<input type="hidden" name="action" id="action" value="update_tasks">
				
		<input type="hidden" name="daily_tasks" id="daily_tasks" value="">
		<input type="hidden" name="weekly_tasks" id="weekly_tasks" value="">
		<input type="hidden" name="shabbos_tasks" id="shabbos_tasks" value="">
		<input type="hidden" name="no_labels_tasks" id="no_labels_tasks" value="">
					
		<input type="hidden" name="daily_tasks" id="daily_tasks" value="">
		<input type="hidden" name="weekly_tasks" id="weekly_tasks" value="">
		<input type="hidden" name="shabbos_tasks" id="shabbos_tasks" value="">
		<input type="hidden" name="no_labels_tasks" id="no_labels_tasks" value="">
					
<? if ($action == "produce_report") : ?>			

			<H2><?=$user->first;?> <?=$user->last;?></H2>
			
			<br />
					
	<!-- ****************************** DAILY TASKS ****************************** -->		
	<DIV name="daily_div" id="daily_div">
	<? if (count($daily_labels) > 0) : ?>
		<? for ($dlno = 0; $dlno < count($daily_labels); $dlno++) : ?>
		<? $label_name_1 = $daily_labels[$dlno]; $start_date = $daily_dates[$dlno][0]; $end_date = $daily_dates[$dlno][1]; ?>
		<div class="marking module">
			<div class="row top_row">
				<div class="days">
					<? for ($dno = $start_date; $dno <= $end_date; $dno++) : ?>
					<? if ($dno % 7 == 5) $special = " special"; else $special = ""; ?>
					
					<div class="cell<?=$special;?>"><?=$days_of_the_week[$dno % 7];?></div>
					<? endfor; ?>
				</div>			
				<div class="cell">
					<?=$daily_labels[$dlno];?>
				</div>			
			</div>
			
			<? for ($dtno = 0; $dtno < count($daily_tasks); $dtno++) : ?>
				<? $daily_task = $daily_tasks[$dtno]; $label_name_2 = $daily_task->label_name; ?>
				
				<? if ($label_name_1 == $label_name_2) : ?>
				
					<? if ($daily_task->mandatory_qty > 0) : ?>
			<div class="row tasks mission">	
					<? else : ?>
			<div class="row tasks bonus">					
					<? endif; ?>
					
				<!-- ***** DATE TASKS MARKS ***** -->
				<div class="days">
				<? for ($dtmno = 0; $dtmno < count($daily_task->date_task_marks); $dtmno++) : ?>
					<? $date_task_mark = $daily_task->date_task_marks[$dtmno]; ?>
					<? if ($date_task_mark->mark_points > 0) $checked = "checked"; else $checked = "unchecked";?>
					
					<? if (is_null($daily_task->quantity)) : ?>						
					<div class="cell checkbox <?=$checked;?>">
						<span id="<?=$date_task_mark->date_task_id;?>:<?=$date_task_mark->mark_date;?>" class="checkbox_span <?=$checked;?>" onclick="update_daily_date_task(this, <?=$date_task_mark->date_task_id;?>, <?=$date_task_mark->mark_date;?>);">
							<input type="checkbox">
						</span>
					</div>	
					<? else :?>
					<div class="cell text_input">
						<input type="text" onkeypress="return number_validation(event);" maxlength="3">
					</div>
					<? endif; ?>
					
				<? endfor; ?>
				</div>
				<!-- ***** DATE TASKS MARKS ***** -->
				
				<div class="campaign_logo">
					<img alt="Father-Son-BW.png" src="/file_view.php?id=<?=$daily_task->subject_image_id;?>">
				</div>
			
				<div class="cell">
					<?=$daily_task->task_name;?>
				</div>			
			</div>
				<? endif; ?>
				
			<? endfor; ?>
			
		</div>
		<? endfor; ?>
	<? endif; ?>
	</DIV>	
	<!-- ****************************** DAILY TASKS ****************************** -->		
	
	
	<!-- ****************************** WEEKLY TASKS ****************************** -->		
	<DIV name="weekly_div" id="weekly_div">
		<? if (count($weekly_labels) > 0) : ?>
		<div class="marking module">
		
			<? for ($lno = 0; $lno < count($weekly_labels); $lno++) : ?>
			<div class="row top_row">
				<div class="days">
					<div class="cell">&nbsp;</div>
				</div>
			
				<div class="cell">
					<?=$weekly_labels[$lno];?>
				</div>
			</div>

			<? for ($sqno = 0; $sqno < count($weekly_sequence_numbers); $sqno++) : ?>			
				<? $info = explode(":", $weekly_sequence_numbers[$sqno]); ?>
				
				<? if ($info[0] == $weekly_labels[$lno]) : ?>
				
					<? $weekly_task = $weeklies[$weekly_sequence_numbers[$sqno]]; ?>
					<? if ($weekly_task->mandatory_qty > 0) : ?>
			<div class="row tasks mission">	
					<? else :?>
			<div class="row tasks bonus">
					<? endif; ?>

					
					<div class="days">	
						<? $date_task_mark = $weekly_task->date_task_mark; ?>
							
						<? if ($date_task_mark->marked == true) $checked = "checked"; else $checked = "unchecked"; ?>
						<? if ($date_task_mark->date_task_id > 0) $mark_date = $date_task_mark->mark_date; else $mark_date = 0; ?>
						
						<? if (is_null($weekly_task->quantity)) : ?>						
						<div class="cell checkbox <?=$checked;?>">
							<span id="<?=$date_task_mark->date_task_id;?>:<?=$date_task_mark->mark_date;?>" class="checkbox_span <?=$checked;?>" onclick="update_weekly_shabbos_date_task(this, <?=$weekly_task->date_task_id;?>, <?=$mark_date;?>);">
								<input type="checkbox">
							</span>
						</div>	
						<? else :?>
						<div class="cell text_input">
							<input type="text" onkeypress="return number_validation(event);" maxlength="3">
						</div>
						<? endif; ?>
					</div>
					
					<div class="campaign_logo">
						<img alt="" src="/file_view.php?id=<?=$weekly_task->subject_image_id;?>">
					</div>
					
					<div class="cell">
						<?=$weekly_task->task_name;?>
					</div>
					
					
			</div>
			
				
				<? endif; ?>
				
			<? endfor; ?>
		
			<? endfor; ?>
		
			
		</div>
		<!-- <div class="marking module"> -->
		<? endif; ?>
	</DIV>
	<!-- ****************************** WEEKLY TASKS ****************************** -->		
	
	
	<!-- ****************************** SHABBOS TASKS ****************************** -->		
	<DIV name="shabbos_div" id="shabbos_div">
		<? if (count($shabbos_labels) > 0) : ?>
		<div class="marking module">
		
			<? for ($lno = 0; $lno < count($shabbos_labels); $lno++) : ?>
			<div class="row top_row">
				<div class="days">
					<div class="cell">&nbsp;</div>
				</div>
			
				<div class="cell">
					<?=$shabbos_labels[$lno];?>
				</div>
			</div>

			<? for ($sqno = 0; $sqno < count($shabbos_sequence_numbers); $sqno++) : ?>			
				<? $info = explode(":", $shabbos_sequence_numbers[$sqno]); ?>
				
				<? if ($info[0] == $shabbos_labels[$lno]) : ?>
				
					<? $shabbos_task = $shabbos[$shabbos_sequence_numbers[$sqno]]; ?>
					<? if ($shabbos_task->mandatory_qty > 0) : ?>
			<div class="row tasks mission">	
					<? else :?>
			<div class="row tasks bonus">
					<? endif; ?>

					
					<div class="days">	
						<? $date_task_mark = $shabbos_task->date_task_mark; ?>
							
						<? if ($date_task_mark->mark_points > 0) $checked = "checked"; else $checked = "unchecked";?>
						<? if ($date_task_mark->date_task_id > 0) $mark_date = $date_task_mark->mark_date; else $mark_date = 0; ?>
						
						<? if (is_null($shabbos_task->quantity)) : ?>						
						<div class="cell checkbox <?=$checked;?>">
							<span id="<?=$date_task_mark->date_task_id;?>:<?=$date_task_mark->mark_date;?>" class="checkbox_span <?=$checked;?>" onclick="update_weekly_shabbos_date_task(this, <?=$shabbos_task->date_task_id;?>, <?=$mark_date;?>);">
								<input type="checkbox">
							</span>
						</div>	
						<? else :?>
						<div class="cell text_input">
							<input type="text" onkeypress="return number_validation(event);" maxlength="3">
						</div>
						<? endif; ?>
					</div>
					
					<div class="campaign_logo">
						<img alt="" src="/file_view.php?id=<?=$shabbos_task->subject_image_id;?>">
					</div>
					
					<div class="cell">
						<?=$shabbos_task->task_name;?>
					</div>
					
					
			</div>
			
				
				<? endif; ?>
				
			<? endfor; ?>
		
			<? endfor; ?>
		
			
		</div>
		<!-- <div class="marking module"> -->
		<? endif; ?>
	</DIV>
	<!-- ****************************** SHABBOS TASKS ****************************** -->		
	

	
	<!-- ****************************** NO LABEL TASKS ****************************** -->
	<DIV name="no_labels_div" id="no_labels_div">
	<? if (count($no_label_subjects) > 0) : ?>
		<? for ($nlno = 0; $nlno < count($no_label_subjects); $nlno++) : ?>
<? 
			$key1 = $no_label_subjects[$nlno];
			$info = explode(":", $key1); 
			$subject_name = $info[0]; 
			$mission_name = $info[1];
			$start_date = $no_label_dates[$nlno][0];
			$end_date = $no_label_dates[$nlno][1];
?>
		
		<div class="marking module">
			<div class="row top_row">
				<div class="days">
					<div class="cell">
					</div>
				
					<? //for ($date = $start_date; $date <= $end_date; $date++) : ?>
						<? //if ($date % 7 == 5) $special = " special"; else $special = ""; ?>
					<!--<div class="cell<?//=$special;?>">
						<?//=$days_of_the_week[$date % 7];?>
					</div>-->
					<? //endfor; ?>
				</div>
				<div class="cell">
					<?=$subject_name;?> - <?=$mission_name;?>
				</div>
			</div>
			
			<? for ($nltno = 0; $nltno < count($no_label_tasks); $nltno++) : ?>
<?
				$no_label_task = $no_label_tasks[$nltno];			
				$subject_name = $no_label_task->subject_name;
				$mission_name = $no_label_task->mission_name;
				$key2 = $subject_name . ":" . $mission_name;
?>
				<? if ($key1 == $key2) : ?>
					<? if ($no_label_task->mandatory_qty > 0) : ?>
			<div class="row tasks mission">	
					<? else : ?>
			<div class="row tasks bonus">
					<? endif; ?>
				<div class="days">
					<? $date_task_mark = $no_label_task->date_task_mark; ?>	
					<? if ($date_task_mark->marked == true) $mark_date = $date_task_mark->mark_date; else $mark_date = 0; ?>

					<? if ($date_task_mark->marked == true) $checked = "checked"; else $checked = "unchecked";?>
						
					<? if (is_null($no_label_task->quantity)) : ?>						
					<div class="cell checkbox <?=$checked;?>">
						<span class="checkbox_span <?=$checked;?>" onclick="update_weekly_shabbos_date_task(this, <?=$date_task_mark->date_task_id;?>, <?=$mark_date;?>);">
							<input type="checkbox">
						</span>
					</div>	
					<? else :?>
					<div class="cell text_input">
						<input type="text" onkeypress="return number_validation(event);" maxlength="3">
					</div>
					<? endif; ?>
				</div>
				<div class="campaign_logo">
					<img src="/file_view.php?id=<?=$no_label_task->subject_image_id;?>">
				</div>
				<div class="cell">
					<?=$no_label_task->task_name;?>
				</div>
			</div>
				<?endif; ?>
				
			<? endfor; ?>
			
		</div>
		<!-- <div class="marking module"> -->
		<? endfor; ?>
	<? endif; ?>
	</DIV>
	<!-- ****************************** NO LABEL TASKS ****************************** -->

			<!--$key1 = $no_label_subjects[$nlno];
			echo $key1 . "<br />";
			
			for ($nltno = 0; $nltno < count($no_label_tasks); $nltno++) {
				$no_label_task = $no_label_tasks[$nltno];			
				$subject_name = $no_label_task->subject_name;
				$mission_name = $no_label_task->mission_name;
				$key2 = $subject_name . ":" . $mission_name;
			
				if ($key1 == $key2) {
					echo $no_label_task->task_name . "<br />";
				}
				
			}-->
	
	<!--<div>
		<a class="button" onclick="submit_form();">
			<span class="icon"></span><?//=T_('Save and edit next Soldier')?>
		</a>						
	</div>-->
	
	</form>
	
</DIV>
	
	
<? endif; ?>			
<!-- if ($action != "") -->
			
		</div>
		<!-- <div class="body"> -->
		
		
	</body>	
</html>
