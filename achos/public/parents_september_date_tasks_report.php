<? 
$admin_auth = array('user'); 

$sep_first = 2455360;
$oct_third = 2455473;

$number_of_students = 0;
$change_school = false;
if (isset($_POST['change_school'])) {
	$change_school = $_POST['change_school'];
}

require('header.php'); 
require_once('calendar.php');
include("classes/subject.php");

$child_id = 0;

if (isset($_POST['child_id']))
	$child_id = $_POST['child_id'];
	
include("camps/includes/classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
$admin->get_children();

$class_id = 0;
$user_id = 0;
$subject_id = 0;
$date_list = "";
$start_date = 0; 
$end_date = 1; 

$days_of_the_week = array("M", "T", "W", "T", "F", "S", "S");

//$today = gregoriantojd(date("n"), date("j"), date("Y"));
$today = unixtojd();	
$day_of_the_week = date("N");
if ($day_of_the_week != 7)
	$sunday = $today - $day_of_the_week;
else
	$sunday = $today;
$report_start_date = $sunday + 7;


$action = "";
if (isset($_POST['action']) || count($admin->children) == 1) {

	if (isset($_POST['action']))
		$action = $_POST['action'];
	
	if ($action == "produce_report" || count($admin->children) == 1) {
	
		if (isset($_POST['child_id'])) 
			$user_id = $_POST['child_id'];		
		else
			$user_id = $admin->children[0]->user_id;	
			
		include("camps/includes/classes/user_track.php");
		include("classes/date_tasks_mission.php");
		include("classes/daily_task.php");
		include("classes/weekly_task.php");
		include("classes/shabbos_task.php");
		include("classes/no_label_task.php");
		include("classes/task.php");
		include("classes/date_tasks_mark.php");
		
		if (isset($_POST['date_list'])) {
			$date_list = explode(":", $_POST['date_list']);
			$start_date = $date_list[0]; 
			$end_date = $date_list[1]; 		
		}
		else {
			$sql = "SELECT * FROM reports WHERE report_type='mission_cover_sheet' AND visibility != 'none' AND start_date > " . $sep_first . " AND end_date < " . $oct_third . " ORDER BY start_date LIMIT 1";
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);
			$start_date = $row['start_date'];
			$end_date = $row['end_date'];
		}
		
		$sql = "SELECT * FROM users WHERE user_id=" . $user_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$user = new user($row);
		$user->get_school_class();
		$user->get_rank();
		$user->get_september_user_tracks($subject_id, $start_date, $end_date);
		//////////$user->get_user_tracks($subject_id, $start_date, $end_date);	
	}
}

// ***** REPORT DATES ***** //
include("classes/report.php");
$reports = array();
$sql = "SELECT * FROM reports WHERE report_type='mission_cover_sheet' AND visibility != 'none' AND start_date >= " . $sep_first . " AND end_date <= " . $oct_third . " ORDER BY start_date";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$report = new report($row);
	array_push($reports, $report);
}
// ***** REPORT DATES ***** //

//echo "# OF REPORTS:" . count($reports) . "<br />";
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Marking Missions - Tzivos Hashem Management System</title>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		
		<script type="text/javascript">
		</script>
		<style>
				@media print {
					@page :left {
						margin:.25in !important;
					}
				}
		</style>

	</head>

	<body>
		
		<? include('admin_header.php'); ?>
		
		<input type="hidden" name="start_date" value="<?=$start_date_greg;?>">
		<input type="hidden" name="end_date" value="<?=$end_date_greg;?>">
		
		<script type="text/javascript" src="scripts/functions.js"></script>
		<script type="text/javascript" src="scripts/jquery.styleselect.js"></script>
		
		<script type="text/javascript">
			var user_id = <?=$user_id;?>;
			var check_all = false;
			
			//var school_id = <?=$school_id;?>;
			//var start_date = <?=$start_date;?>;
			//var end_date = <?=$end_date;?>;
			//var check_all = false;
			//var check = "";
			//var date_task_ids = "";
			//var mark_dates = "";
			//var checked = false;
			//var user_id = <?=$user_id;?>;
			//var today = <?=$today;?>;
			//var save = false;
			//var number_of_students = <?=$number_of_students;?>;
			
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
				
				// ***** Child List Select ***** //
				$(".child_list select").sSelect().change(function () {
					var child_id = $(this).val();
					
					if (child_id > 0)
						document.forms["parents_september_date_tasks_report"].submit();
					
					/*var school_id = $(this).val();
					var function_name = "get_first_user_by_school_and_class";
					var parameters = [school_id];
					var url = "get_functions.php?function_name=" + function_name + "&parameters=" + parameters;
					
					$.getJSON(url, function(data) {	
						//alert(data.user_id + " " + data.class_id);
						$('#action').val("produce_report");
						$('#hidden_user_id').val(data.user_id);
						$('#class_id').val(data.class_id);
						$('#subject_id').val("-1");
						
						$('#date_tasks_report').submit();
					});						*/
					
					//document.getElementById("change_school").value = "true";
					//$(this).closest('form').submit();
				})
				// ***** Child List Select ***** //
												
				$(".date_list select").sSelect().change(function () {
						document.forms["parents_september_date_tasks_report"].submit();
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
							else {
								check_all = false;
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
							else {
								check_all = false;
							}
						});					
					}
				})
				// ***** UNCHECK ALL ***** //
								
				$(".marking_list #display_submit").hide();
			});
			
			function update_weekly_shabbos_date_task(span, date_task_id, mark_date) {
				//alert("1) update_weekly_shabbos_date_task");
				
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
				
				//alert("2) update_weekly_shabbos_date_task");
				
				if (check_all == false) {
					var parameters = [user_id, date_task_id, mark_date];
					url = url + "?function_name=" + function_name + "&parameters=" + parameters;
					//alert(url);
					$.getJSON(url, function(success) {	
						alert("success:" + success);
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
			
			function check_number_of_students(number_of_students) {
				if (number_of_students == 0) {
					alert("There are no students currently registered in this school. Please choose another school.");
					return false;
				}
				else {
					return true;
				}
			}
		</script>
		
		<div class="body left marking_missions">
						
			<H1>Marking Missions</H1>

<? if ($child_id < 1 && count($admin->children) > 1) : ?>
			<form name="parents_september_date_tasks_report" id="parents_september_date_tasks_report" action="parents_september_date_tasks_report.php" method="post" accept-charset="UTF-8">
				<input type="hidden" name="action" id="action" value="produce_report">	
				
				<!-- ***** CHILDREN ***** -->
				<div class="infobox2 marking_list clearfix">
								
					<div class="child_list select_box">
						<a class="prev button">
							<span class="icon"></span>
							<span class="label"><?=T_('Previous School')?></span>
						</a>
					
						<SELECT name="child_id" id="child_id">
							<OPTION value="-1">Please select a child</OPTION>		
							<? for ($cno = 0; $cno < count($admin->children); $cno++) : ?>
							<OPTION value="<?=$admin->children[$cno]->user_id;?>"><?=$admin->children[$cno]->first;?> <?=$admin->children[$cno]->last;?></OPTION>
							<? endfor; ?>
						</SELECT>
						
						<a class="next button">
							<span class="icon"></span>
							<span class="label"><?=T_('Next School')?></span>
						</a>						
					</div>
				
				</div>
				<!-- ***** CHILDREN ***** -->
				
			</form>
<? else : ?>			
			<form name="parents_september_date_tasks_report" id="parents_september_date_tasks_report" action="parents_september_date_tasks_report.php" method="post" accept-charset="UTF-8">
				<input type="hidden" name="action" id="action" value="produce_report">
				
				<div class="infobox2 marking_list clearfix noprint">

					<div class="infobox2 marking_list clearfix">
									
						<? if (count($admin->children) > 1) : ?>
						<!-- ***** CHILDREN ***** -->			
						<div class="child_list select_box">
							<a class="prev button">
								<span class="icon"></span>
								<span class="label"><?=T_('Previous School')?></span>
							</a>
						
							<SELECT name="child_id" id="child_id">
								<OPTION value="-1">Please select a child</OPTION>		
								<? for ($cno = 0; $cno < count($admin->children); $cno++) : ?>
									<? if ($child_id == $admin->children[$cno]->user_id) : ?>
									<OPTION selected value="<?=$admin->children[$cno]->user_id;?>"><?=$admin->children[$cno]->first;?> <?=$admin->children[$cno]->last;?></OPTION>
									<? else : ?>
									<OPTION value="<?=$admin->children[$cno]->user_id;?>"><?=$admin->children[$cno]->first;?> <?=$admin->children[$cno]->last;?></OPTION>
									<? endif; ?>
								<? endfor; ?>
							</SELECT>
							
							<a class="next button">
								<span class="icon"></span>
								<span class="label"><?=T_('Next School')?></span>
							</a>						
						</div>
						<!-- ***** CHILDREN ***** -->
						<? else : ?>
						<input type="hidden" name="child_id" id="child_id" value="<?=$admin->children[0]->user_id;?>">
						<? endif; ?>
					
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
									


				</div>				

			</form>

<DIV id="tasks_div">
	<form name="update_date_tasks_report" id="update_date_tasks_report" action="summer.php" method="post" accept-charset="UTF-8">
		<input type="hidden" name="action" id="action" value="update_tasks">
				
		<input type="hidden" name="daily_tasks" id="daily_tasks" value="">
		<input type="hidden" name="weekly_tasks" id="weekly_tasks" value="">
		<input type="hidden" name="shabbos_tasks" id="shabbos_tasks" value="">
		<input type="hidden" name="no_labels_tasks" id="no_labels_tasks" value="">
					
		<input type="hidden" name="daily_tasks" id="daily_tasks" value="">
		<input type="hidden" name="weekly_tasks" id="weekly_tasks" value="">
		<input type="hidden" name="shabbos_tasks" id="shabbos_tasks" value="">
		<input type="hidden" name="no_labels_tasks" id="no_labels_tasks" value="">
					
	<? if ($action == "produce_report" || count($admin->children) == 1) : ?>			

	<div class="print_header">
		<div class="marking module clearfix dontsplit">
													
			<div class="rank_image"><img height="70" src="/file_view.php?id=<?=$user->rank_image_id;?>"></div>
			<div class="user_image"><img height="70" src="/file_view.php?id=<?=$user->user_photo_id;?>"></div>
			<p>My Missions for the week of Week</p>
			<p><?=$user->rank_name;?> <?=$user->first;?> <?=$user->last;?></p>
			<p>Grade: <?=$user->school_class->class_grade;?> - <?=$user->school_class->class_sub;?> Teacher: <?=$user->school_class->class_teacher;?></p>
			<p>Parents Signature</p>
		</div>
	</div>
	
							
	<!-- ****************************** DAILY TASKS ****************************** -->		
	<DIV name="daily_div" id="daily_div">
	
	<? if (count($user->daily_labels) > 0) : ?>
		<? for ($dlno = 0; $dlno < count($user->daily_labels); $dlno++) : ?>
		
<? 
			$key1 = $user->daily_labels[$dlno]; 
			$info = explode(":", $key1); 
			$label = $info[0]; 
			$start_date = $info[1]; 
			$end_date = $info[2]; 
?>
			
		<div class="marking module">
			<div class="row top_row">
				<div class="days">
					<? for ($dno = $start_date; $dno <= $end_date; $dno++) : ?>
					<? if ($dno % 7 == 5) $special = " special"; else $special = ""; ?>					
					<div class="cell<?=$special;?>"><?=$days_of_the_week[$dno % 7];?></div>
					<? endfor; ?>				
				</div>
				
				<div class="cell">
					<?=$label; ?>
				</div>
			</div>
			
			<? for ($dtno = 0; $dtno < count($user->daily_tasks); $dtno++) : ?>
				<? $key2 = str_replace(":", " ", $user->daily_tasks[$dtno]->label_name) . ":" . $user->daily_tasks[$dtno]->start_date . ":" . $user->daily_tasks[$dtno]->end_date; ?>
					
				<? if ($key1 == $key2) : ?>
					<? $daily_task = $user->daily_tasks[$dtno]; ?>
					
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
							<input type="hidden" name="">
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
			
		<? endfor; ?> <!-- for ($dlno = 0; $dlno < count($user->daily_labels); $dlno++) -->
		
	<? endif; ?> <!-- if (count($user->daily_labels) > 0) -->
	
	</DIV>	
	<!-- ****************************** DAILY TASKS ****************************** -->		
	

	<!-- ****************************** WEEKLY TASKS ****************************** -->	
	<DIV name="weekly_div" id="weekly_div">
		<? if (count($user->weekly_labels) > 0) : ?>
		<div class="marking module">
		
			<? for ($lno = 0; $lno < count($user->weekly_labels); $lno++) : ?>
			<div class="row top_row">
				<div class="days">
					<div class="cell">&nbsp;</div>
				</div>
			
				<div class="cell">
					<?=$user->weekly_labels[$lno];?>
				</div>
			</div>		
		
				<? for ($wtno = 0; $wtno < count($user->weekly_tasks); $wtno++) : ?>
					<? $label_name = $user->weekly_tasks[$wtno]->label_name; ?>
					
					<? if ($label_name == $user->weekly_labels[$lno]) : ?>
						<? $weekly_task = $user->weekly_tasks[$wtno]; ?>
						
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
		<? endif; ?>
	</DIV>
	<!-- ****************************** WEEKLY TASKS ****************************** -->		

	
	<!-- ****************************** SHABBOS TASKS ****************************** -->	
	<DIV name="weekly_div" id="weekly_div">
		<? if (count($user->shabbos_labels) > 0) : ?>
		<div class="marking module">
		
			<? for ($lno = 0; $lno < count($user->shabbos_labels); $lno++) : ?>
			<div class="row top_row">
				<div class="days">
					<div class="cell">&nbsp;</div>
				</div>
			
				<div class="cell">
					<?=$user->shabbos_labels[$lno];?>
				</div>
			</div>		
		
				<? for ($stno = 0; $stno < count($user->shabbos_tasks); $stno++) : ?>
					<? $label_name = $user->shabbos_tasks[$stno]->label_name; ?>
					
					<? if ($label_name == $user->shabbos_labels[$lno]) : ?>
						<? $shabbos_task = $user->shabbos_tasks[$stno]; ?>
						
						<? if ($shabbos_task->mandatory_qty > 0) : ?>
			<div class="row tasks mission">	
						<? else :?>
			<div class="row tasks bonus">
						<? endif; ?>

					
					<div class="days">	
						<? $date_task_mark = $shabbos_task->date_task_mark; ?>
							
						<? if ($date_task_mark->marked == true) $checked = "checked"; else $checked = "unchecked"; ?>
						<? if ($date_task_mark->date_task_id > 0) $mark_date = $date_task_mark->mark_date; else $mark_date = 0; ?>
						
						<? if (is_null($shabbos_task->quantity)) : ?>						
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
		<? endif; ?>
	</DIV>
	<!-- ****************************** SHABBOS TASKS ****************************** -->		
	
	<!-- ****************************** NO LABEL TASKS ****************************** -->
	<DIV name="no_labels_div" id="no_labels_div">
	<? if (count($user->no_label_subjects) > 0) : ?>
		<? for ($nlno = 0; $nlno < count($user->no_label_subjects); $nlno++) : ?>
<? 
			$key1 = $user->no_label_subjects[$nlno];
			$info = explode(":", $key1); 
			$subject_name = $info[0]; 
			$mission_name = $info[1];
?>
		
		<div class="marking module">
			<div class="row top_row">
				<div class="days">
					<div class="cell">
					</div>
				</div>
				<div class="cell">
					<?=$subject_name;?> - <?=$mission_name;?>
				</div>
			</div>
			
			<? for ($nltno = 0; $nltno < count($user->no_label_tasks); $nltno++) : ?>
			
<?
				$no_label_task = $user->no_label_tasks[$nltno];			
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
					<? if ($date_task_mark->marked == true) $mark_date = $date_task_mark->mark_date; else $mark_date = $no_label_task->end_date; ?>

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

			
				<? endif; ?>
				
			<? endfor; ?>
			
		</div> <!-- <div class="marking module"> -->
		
		<? endfor; ?>
		
	<? endif; ?>
	</DIV>
	<!-- ****************************** NO LABEL TASKS ****************************** -->

	
	</form>
	
</DIV>
	
	
	<? endif; ?> <!-- if ($action == "produce_report") -->
			
<? endif; ?> <!-- if ($school_id == 0) -->
			
		</div> <!-- <div class="body"> -->
		
		
	</body>	
</html>
