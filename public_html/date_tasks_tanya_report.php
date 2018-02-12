<? 
$admin_auth = array('school','user'); 

$report_name = "";
$number_of_students = 0;
$change_school = false;
if (isset($_POST['change_school'])) {
	$change_school = $_POST['change_school'];
}

require('header.php'); 
require_once('calendar.php');
include("classes/subject.php");

$school_id = 0;

if (isset($_POST['school_id']))
	$school_id = $_POST['school_id'];
	
include("camps/includes/classes/admin.php");
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
			$sql = "SELECT * FROM reports WHERE report_type='mission_cover_sheet' AND visibility != 'none' ORDER BY start_date LIMIT 1";			
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);
			$start_date = $row['start_date'];
			$end_date = $row['end_date'];
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
	$sql = "SELECT * FROM reports WHERE report_type='mission_cover_sheet' AND visibility != 'none' ORDER BY start_date";	
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$report = new report($row);
		array_push($reports, $report);
	}
	// ***** REPORT DATES ***** //

	$start_date_greg = jdtogregorian($start_date);
	$end_date_greg = jdtogregorian($end_date);
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
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Marking Tanya - Tzivos Hashem Management System</title>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		
	</head>

	<body>
		
		<? include('admin_header.php'); ?>
		
		<input type="hidden" name="start_date" value="<?=$start_date_greg;?>">
		<input type="hidden" name="end_date" value="<?=$end_date_greg;?>">
		
		<script type="text/javascript" src="scripts/functions.js"></script>
		<script type="text/javascript" src="scripts/jquery.styleselect.js"></script>
		<script type="text/javascript" src="scripts/jquery.autocolumn.js"></script>
		
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
				
								
				$(".marking_list #display_submit").hide();
				
			});
			
		</script>
		
		<div class="body left">
						
			<H1>Marking Tanya</H1>
			
<? if ($school_id < 1) : ?>
			<form name="date_tasks_report" id="date_tasks_report" action="date_tasks_tanya_report.php" method="post" accept-charset="UTF-8">
				<input type="hidden" name="action" id="action" value="">	
				<input type="hidden" name="hidden_user_id" id="hidden_user_id" value="">
				<input type="hidden" name="class_id" id="class_id" value="">
				<input type="hidden" name="subject_id" id="subject_id" value="-1">
				
				<div class="infobox2 marking_list clearfix">
				
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
			<form name="date_tasks_report" id="date_tasks_report" action="date_tasks_tanya_report.php" method="post" accept-charset="UTF-8">
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
								<option selected value="<?=$user_select->user_id;?>"><?=$user_select->school_class->class_grade;?>-<?=$user_select->school_class->class_sub;?> <?=$user_select->first;?> <?=$user_select->last;?></option>
								<? else : ?>
								<option value="<?=$user_select->user_id;?>"><?=$user_select->school_class->class_grade;?>-<?=$user_select->school_class->class_sub;?> <?=$user_select->first;?> <?=$user_select->last;?></option>
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
					
	<? if ($action == "produce_report") : ?>			

	<h2>Marking Tanya</h2>
	
		<div class="marking tanya_mark module">
			<div class="row top_row">
				<div class="cell name">Student</div>
				<div class="cell">Weekly Quota</div>
				<div class="cell">Completed</div>
				<div class="cell lines">Lines Due</div>
				<div class="cell">Status</div>
				<div class="cell">Tested</div>
			</div>
							
			<div class="row">	
				<div class="cell name">Menachem Mendel Horodoker</div>
				<div class="cell">0.3 lines</div>
				<div class="cell">0 lines</div>
				<div class="cell lines">0 - 1</div>
				<div class="cell">1 weeks behind</div>
				<div class="cell">0 - <input type="text" size="4" value="" name="item_2876"></div>
			</div>
			<div class="row">	
				<div class="cell name">Miriam Rivka Karelin</div>
				<div class="cell">0.3 lines</div>
				<div class="cell">0 lines</div>
				<div class="cell lines">0 - 1</div>
				<div class="cell">1 weeks behind</div>
				<div class="cell">0 - <input type="text" size="4" value="" name="item_2876"></div>
			</div>
			<div class="row">	
				<div class="cell name">Miriam Rivka Karelin</div>
				<div class="cell">0.3 lines</div>
				<div class="cell">0 lines</div>
				<div class="cell lines">0 - 1</div>
				<div class="cell">1 weeks behind</div>
				<div class="cell">0 - <input type="text" size="4" value="" name="item_2876"></div>
			</div>

		</div>

	<div style="clear:both; height:1px;"></div>
	
	</form>
	
</DIV>
	
	
	<? endif; ?> <!-- if ($action == "produce_report") -->
			
<? endif; ?> <!-- if ($school_id == 0) -->
			
		</div> <!-- <div class="body"> -->
		
		
	</body>	
</html>
