<? 
//header("Location: under_construction.php");
$admin_auth = array('school','user'); 

$start = 2455499;
//$end = 2455522;

$d = unixtojd();
$day = date("N");
$end = $d;

switch ($day) {
	case 1:
	case 2:
	case 3:
	case 4:
		break;
	case 5:
		$end += 2;
		break;
	case 6:
		$end++;
		break;
	case 7:
		break;
	default:
		break;
}

$report_name = "";
$number_of_students = 0;
$change_school = false;
if (isset($_POST['change_school'])) {
	$change_school = $_POST['change_school'];
}

require('../header.php'); 
require_once('../calendar.php');
include("../classes/subject.php");

$school_id = 0;

if (isset($_POST['school_id']))
	$school_id = $_POST['school_id'];
	
include("../camps/includes/classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
if ($admin->auth != "super") {
	$admin->get_schools();
	if (count($admin->schools) == 1) {
		$school_id = $admin->schools[0]['school_id'];
	}
}

$class_id = 0;
$user_id = 0;
$subject_id = 0;
$date_list = "";
$start_date = 0; 
$end_date = 1; 

$days_of_the_week = array("M", "T", "W", "T", "F", "S", "S");

$today = unixtojd();	
$day_of_the_week = date("N");
if ($day_of_the_week != 7)
	$sunday = $today - $day_of_the_week;
else
	$sunday = $today;
$report_start_date = $sunday + 7;

$action = "";
if (isset($_POST['action'])) {
	$action = $_POST['action'];
	
	if ($action == "produce_report") {
		$class_id = $_POST['class_id'];		
		$hidden_user_id = $_POST['hidden_user_id'];
		if ($hidden_user_id > 0)
			$user_id = $hidden_user_id;
		else
			$user_id = $_POST['user_id'];		
		$subject_id = $_POST['subject_id'];
		
		if (isset($_POST['date_list'])) {
			$date_list = explode(":", $_POST['date_list']);
			$start_date = $date_list[0]; 
			$end_date = $date_list[1]; 		
		}
		else {
			$sql = "SELECT * FROM reports WHERE report_type='mission_cover_sheet' AND visibility != 'none' AND start_date > " . $start . " AND end_date <= " . $end . " ORDER BY start_date DESC LIMIT 1";				
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);
			$start_date = $row['start_date'];
			$end_date = $row['end_date'];
			$report_name = $row['report_name'];
		}
		
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
		$user->get_school_class();
		$user->get_rank();		
		$user->get_user_tracks($subject_id, $start_date, $end_date);	
	}
}

if ($school_id > 0) {
	// ***** USERS ***** //
	include_once("camps/includes/classes/user.php");
	$users = array();
	if ($class_id == 0) 
		$sql = "SELECT u.* FROM users AS u LEFT JOIN classes AS c USING (school_id, class_id) WHERE u.school_id=" . $school_id . " AND user_registered IS NOT NULL ORDER BY c.class_grade, c.class_sub,last, first"; 
	else
		$sql = "SELECT u.* FROM users AS u LEFT JOIN classes AS c USING (school_id, class_id) WHERE u.school_id=" . $school_id . " AND user_registered IS NOT NULL AND u.class_id=" . $class_id . " ORDER BY c.class_grade, c.class_sub,last, first"; 
	//////////echo $sql . "<br />";
	$query = mysql_query($sql);
	$number_of_students = mysql_num_rows($query);
	while ($row = mysql_fetch_assoc($query)) {
			$user_select = new user($row);
			$user_select->get_school_class();
			array_push($users, $user_select);
	}
	// ***** USERS ***** //

	// ***** CLASSES ***** //
	include_once("camps/includes/classes/school_class.php");
	$classes = array();
	$sql = "SELECT c.* ";
	$sql = $sql . "FROM classes AS c ";
	$sql = $sql . "JOIN users AS u ON (u.user_registered > 0 AND u.school_id=" . $school_id . " AND u.class_id=c.class_id) ";
	$sql = $sql . "WHERE c.school_id=" . $school_id . " ";
	$sql = $sql . "GROUP BY c.class_id ";
	$sql = $sql . "ORDER BY c.class_grade, c.class_sub";	
	
	//$sql = $sql . "GROUP BY c.class_grade, c.class_sub ";
	//$sql = $sql . "ORDER BY c.class_grade, c.class_sub";	
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
	include("classes/report.php");
	$reports = array();
	$sql = "SELECT * FROM reports WHERE report_type='mission_cover_sheet' AND visibility != 'none' AND start_date > " . $start . " AND end_date <= " . $end . " ORDER BY start_date";	
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$report = new report($row);
		array_push($reports, $report);
	}
	// ***** REPORT DATES ***** //
}

// ***** SCHOOLS ***** //
if ($admin->auth == "super") {
	$schools_sql = "SELECT school_id, school_name FROM schools ORDER BY school_name";
	$schools_query = mysql_query($schools_sql);
}
elseif (count($admin->schools) > 0) {
	$schools_sql = "SELECT s.school_id, s.school_name FROM schools AS s JOIN admin_auths AS aa ON (aa.admin_id=" . $admin->admin_id . " AND aa.auth='school' AND aa.id=s.school_id) ORDER BY school_name";
	$schools_query = mysql_query($schools_sql);
}
// ***** SCHOOLS ***** //

require('auction.php');
$auctions = array();
//////////$sql = "SELECT * FROM auctions WHERE auction_ran=1 AND approved=1 ORDER BY auction_id DESC"; 
$sql = "SELECT * FROM auctions WHERE auction_id=26 ORDER BY auction_id DESC"; 
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query))
{
	$auction_select = new auction($row);
	array_push($auctions, $auction_select);
}
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Marking Missions - Tzivos Hashem Management System</title>
		<link href="../admin_styles.css" rel="stylesheet" type="text/css">
		
		<script type="text/javascript">
		</script>
		
		<style>
			@media print 
			{
				@page :left 
				{
					margin:.25in !important;
				}
			}
		</style>

	</head>

	<body>
		
		<? include('../admin_header.php'); ?>
				
		<SCRIPT type="text/javascript" src="../jquery.js"></SCRIPT>
		<SCRIPT type="text/javascript" src="../jquery-ui.js"></SCRIPT>
		<SCRIPT type="text/javascript" src="../scripts/jquery.tools.min.js"></SCRIPT>
		<script type="text/javascript" src="../scripts/jquery.styleselect.js"></script>

		<!--		
		<script type="text/javascript" src="../scripts/functions.js"></script>
		<script type="text/javascript" src="../scripts/jquery.styleselect.js"></script>
		<script type="text/javascript" src="../scripts/jquery.autocolumn.js"></script>
		-->
	
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
			var number_of_students = <?=$number_of_students;?>;
			
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
				
				$(".auction_id select").sSelect().change(function () {
				})
				
				// ***** School List Select ***** //
				$(".school_list select").sSelect().change(function () {
					var school_id = $(this).val();
					var function_name = "get_first_user_by_school_and_class";
					var parameters = [school_id];
					var url = "get_functions.php?function_name=" + function_name + "&parameters=" + parameters;
					
					$.getJSON(url, function(data) {	
						$('#action').val("produce_report");
						$('#hidden_user_id').val(data.user_id);
						$('#class_id').val(data.class_id);
						$('#subject_id').val("-1");
						
						$('#date_tasks_report').submit();
					});						
					
				})
				// ***** School List Select ***** //
				
				
				// ***** Class List Select ***** //
				$(".class_list select").sSelect().change(function () {
					if (number_of_students > 0) {
						var school_id = $('#school_id').val();
						var class_id = $(this).val();
						var function_name = "get_user_by_class";
						var parameters = [school_id, class_id];
						var url = "get_functions.php?function_name=" + function_name + "&parameters=" + parameters;
						
						$.getJSON(url, function(user_id) {	
							$('#hidden_user_id').val(user_id);
							$('#date_tasks_report').submit();
						});						
					}
				})
				// ***** Class List Select ***** //
				
				$(".user_list select, .campaign_list select").sSelect().change(function () {
					if (number_of_students > 0)
						$(this).closest('form').submit();
				})
				
				
				$(".date_list select").sSelect().change(function () {
					if (number_of_students > 0)
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
					check_all = false;
					
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
					check_all = false;
					
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
				
				$('.marking.module').addClass('dontsplit');
				$('#weekly_div,#tasks_page_two').columnize({ columns: 2 });
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
			
			function update_daily_date_task(span, date_task_id, mark_date) 
			{
				if ($(span).hasClass('checked')) 
				{
					if (check_all == true) 
					{
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
				else 
				{
					if (check_all == true) 
					{
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
				
				if (check_all == false) 
				{
					var parameters = [user_id, date_task_id, mark_date];
					url = url + "?function_name=" + function_name + "&parameters=" + parameters;	

					$.getJSON(url, function(success) 
					{
						if (success == false) 
						{
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
						
			function update_mark(txtbx, date_task_id, mark_date) 
			{
				var user_mark = txtbx.value;
				
				var function_name = "add_mark";
				var parameters = [user_id, date_task_id, mark_date, user_mark];
				var url = "add_functions.php?function_name=" + function_name + "&parameters=" + parameters;
						
				$.getJSON(url, function(success) 
				{
					if (success == 0) 
						alert("Update not performed. Please try again.");
					else
						$("#date_list_select").focus();
				});					
			}
		</script>
		
		<div class="body left marking_missions">
						
			<H1>Marking Missions</H1>
			
<? if ($school_id < 1) : ?>
			<form name="date_tasks_report" id="date_tasks_report" action="date_tasks_report.php" method="post" accept-charset="UTF-8">
				<input type="hidden" name="action" id="action" value="">	
				<input type="hidden" name="hidden_user_id" id="hidden_user_id" value="">
				<input type="hidden" name="class_id" id="class_id" value="">
				<input type="hidden" name="subject_id" id="subject_id" value="-1">
				
				<div class="infobox2 marking_list clearfix">
				
				
				
						<div class="auction_id select_box">
							<a class="prev button">
								<span class="icon"></span>
								<span class="label"><?=T_('Previous School')?></span>
							</a>
							
						<!-- ********** AUCTION ID ********** -->
						<SELECT name="auction_id" id="auction_id" class="sSelect">
							<? foreach ($auctions as $auction_select) : ?>
								<? if ($auction_id == $auction_select->auction_id) : ?>
								<OPTION selected value="<?=$auction_select->auction_id;?>"><?=$auction_select->auction_date;?> - <?=es(dateToHebrew($auction_select->auction_date))?></OPTION>
								<? else : ?>
								<OPTION value="<?=$auction_select->auction_id;?>"><?=$auction_select->auction_date;?> - <?=es(dateToHebrew($auction_select->auction_date))?></OPTION>
								<? endif; ?>						
							<? endforeach; ?>
						</SELECT> 
						<!-- ********** AUCTION ID ********** -->
						
							<a class="next button">
								<span class="icon"></span>
								<span class="label"><?=T_('Next School')?></span>
							</a>						
						
						</div>
				
					<div class="school_list select_box">
						<a class="prev button">
							<span class="icon"></span>
							<span class="label"><?=T_('Previous School')?></span>
						</a>
					
						<SELECT name="school_id" id="school_id">
							<OPTION value="-1">Please select a school</OPTION>
							<? while ($school = mysql_fetch_assoc($schools_query)) : ?>
							<OPTION value="<?=$school['school_id'];?>"><?=$school['school_name'];?></OPTION>
							<? endwhile; ?>
						</SELECT>
						
						<a class="next button">
							<span class="icon"></span>
							<span class="label"><?=T_('Next School')?></span>
						</a>						
					</div>
				
				</div>
				
			</form>
<? else : ?>			
			<form name="date_tasks_report" id="date_tasks_report" action="date_tasks_report.php" method="post" accept-charset="UTF-8">
				<input type="hidden" name="action" id="action" value="produce_report">
				<input type="hidden" name="school_id" id="school_id" value="<?=$school_id;?>">
				<input type="hidden" name="change_school" id="change_school" value="false">
				<input type="hidden" name="change_item" id="change_item" value="">
				
				<div class="infobox2 marking_list clearfix noprint">
				
					<!-- ***** SCHOOL ***** -->
					<? if ($admin->auth == "super" || count($admin->schools) > 0) : ?>
					<div class="school_list select_box">
						<a class="prev button">
							<span class="icon"></span>
							<span class="label"><?=T_('Previous School')?></span>
						</a>
					
						<SELECT name="school_id" id="school_id">
							<? while ($school = mysql_fetch_assoc($schools_query)) : ?>
								<? if ($school_id == $school['school_id']) : ?>
								<OPTION selected value="<?=$school['school_id'];?>"><?=$school['school_name'];?></OPTION>
								<? else : ?>
								<OPTION value="<?=$school['school_id'];?>"><?=$school['school_name'];?></OPTION>
								<? endif; ?>
							<? endwhile; ?>
						</SELECT>
						
						<a class="next button">
							<span class="icon"></span>
							<span class="label"><?=T_('Next School')?></span>
						</a>						
					</div>
					<? endif; ?>
					<!-- ***** SCHOOL ***** -->
				
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
								<option selected value="<?=$class->class_id;?>"><?=$class->class_grade;?>-<?=$class->class_sub;?></option>
								<? else : ?>
								<option value="<?=$class->class_id;?>"><?=$class->class_grade;?>-<?=$class->class_sub;?></option>
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
							
						<select name="user_id" id="user_id" class="sSelect">
							<? for ($uno = 0; $uno < count($users); $uno++) : ?>
								<? $user_select = $users[$uno]; ?>
								
									<? if ($user_select->user_id == $user_id) : ?>
									
										<?  if ($user_select->school_class->class_id > 0) : ?>
										<option selected value="<?=$user_select->user_id;?>"><?=$user_select->school_class->class_grade;?>-<?=$user_select->school_class->class_sub;?> <?=$user_select->first;?> <?=$user_select->last;?></option>
										<? else : ?>
										<option selected value="<?=$user_select->user_id;?>"><?=$user_select->first;?> <?=$user_select->last;?></option>
										<? endif; ?>
										
									<? else : ?>
									
										<?  if ($user_select->school_class->class_id > 0) : ?>
										<option value="<?=$user_select->user_id;?>"><?=$user_select->school_class->class_grade;?>-<?=$user_select->school_class->class_sub;?> <?=$user_select->first;?> <?=$user_select->last;?></option>
										<? else : ?>
										<option value="<?=$user_select->user_id;?>"><?=$user_select->first;?> <?=$user_select->last;?></option>
										<? endif; ?>
										
									<? endif; ?>

							<? endfor; ?>
						</select>
							
						<input type="hidden" name="hidden_user_id" id="hidden_user_id" value="0">
						
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
						</select>
						
						<a class="next button">
							<span class="icon"></span><span class="label"><?=T_('Next Campaign')?></span>
						</a>					
					</div>
					<!-- ***** SUBJECT ***** -->
					
					<!-- ***** WEEKLY PERIOD ***** -->
					<input type="hidden" name="DATES" value="<?=$start_date?>:<?=$end_date;?>">
					
					<div name="date_list_select" id="date_list_select" class="date_list select_box">					
						<a class="prev button">
							<span class="icon"></span>
							<span class="label"><?=T_('Previous Week')?></span>
						</a>
						
						<select name="date_list" class="sSelect">
							<? for ($rno = 0; $rno < count($reports); $rno++) : ?>
								<? $report = $reports[$rno]; ?>
								<? if (($rno == count($reports) - 1 && $action == "") || ($start_date == $report->start_date && $end_date ==$report->end_date)) : //$report->start_date == $start_date?>
								<? $report_name = $report->report_name; ?>
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
					
						<input type="hidden" name="number_of_students" id="number_of_students" value="<?=$number_of_students;?>">
						<? if ($number_of_students > 0 && 1==2) : ?>
						<input class="submit" type="submit" value="GO" onclick="check_number_of_students(<?=$number_of_students;?>);">
						<? endif; ?>
					

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
					
	<? if ($action == "produce_report") : 	?>			

	<div class="print_header">
		<div class="marking module clearfix">
			<div class="rank_image"><img src="/file_view.php?id=<?=$user->rank_image_id;?>" height="70" /></div>
			<div class="user_image"><img src="/file_view.php?id=<?=$user->user_photo_id;?>" height="70" /></div>
			<p class="print_page">Page 1 - &#1489;"&#1492;</p>
			<p class="print_name"><?=$user->rank_name;?> <?=$user->first;?> <?=$user->last;?></p>
			<p class="print_week">My Missions for the week of <?=$report_name;?></p>
			
			<? if ($user->school_class) : ?>
			<p class="print_class">Grade: <?=$user->school_class->class_grade;?> - <?=$user->school_class->class_teacher;?></p>
			<? endif; ?>
			
			<p class="print_sig">Parent's Signature<span></span></p>
			<p class="print_instructions">Fill out your mission sheet and review it with your commander, who will give you a campaign sticker for each mission you have completed.</p>
		</div>
	</div>
	
	<? include("daily_tasks.php"); ?>

	<div style="clear:both; height:1px;"></div>
	
	<div class="print_header print_page_two">
		<div class="marking module clearfix">
			<p class="print_week"><?=$report_name;?></p>
			<p class="print_page">Page 2 - &#1489;"&#1492;</p>
			<p class="print_name"><?=$user->rank_name;?> <?=$user->first;?> <?=$user->last;?></p>
		</div>
	</div>
	
	<div id="tasks_page_two">	
	
		<? include("weekly_tasks.php"); ?>

		<? include("no_label_tasks.php"); ?>
	
		<? include("shabbos_tasks.php"); ?>
			
	</div>
	
	</form>
	
</DIV>
	
	
	<? endif; ?> <!-- if ($action == "produce_report") -->
			
<? endif; ?> <!-- if ($school_id == 0) -->
			
		</div> <!-- <div class="body"> -->
		
		
	</body>	
</html>
