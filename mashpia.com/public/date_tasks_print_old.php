<? 
$admin_auth = array('school','user'); 
require('header.php'); 
require_once('calendar.php');

$days_of_the_week = array("M", "T", "W", "T", "F", "S", "S");

// ***** CHANGE OF SCHOOL ***** //
$change_school = "false";
if (isset($_POST['change_school'])) {
	$change_school = $_POST['change_school'];
}
// ***** CHANGE OF SCHOOL ***** //

// ***** ADMIN ***** //
include("camps/includes/classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new \camps\classes\admin($row);
if ($admin->auth != "super") {
	$admin->get_school_id();
	if ($admin->school_id > 0) {
		$school_id = $admin->school_id;
	}
}
// ***** ADMIN ***** //

$school_id = 0;
if (isset($_POST['school_id']))
	$school_id = $_POST['school_id'];

$class_id = 0;
$user_id = 0;
$subject_id = -1;
$date_list = "";
$start_date = 0; 
$end_date = 1; 
$number_of_students = 0;
$users = array();

$action = "";
if (isset($_POST['action'])) {
	$action = $_POST['action'];
	
	if ($action == "print_report" && $change_school == "false") {
		$class_id = $_POST['class_id'];
		$user_id = $_POST['user_id'];
		$date_list = explode(":", $_POST['date_list']);
		$start_date = $date_list[0]; 
		$end_date = $date_list[1]; 
		
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

		if ($user_id > 0) {
			$sql = "SELECT * FROM users WHERE user_id=" . $user_id;
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);
			$user = new user($row);
			$user->get_user_tracks($subject_id, $start_date, $end_date);
			array_push($users, $user);
		}
		else {
			if ($class_id == -1) 
				$sql = "SELECT * FROM users WHERE school_id=" . $school_id;
			else
				$sql = "SELECT * FROM users WHERE school_id=" . $school_id . " AND class_id=" . $class_id;
			$query = mysql_query($sql);
			while ($row = mysql_fetch_assoc($query)) {
				$user = new user($row);
				$user->get_user_tracks($subject_id, $start_date, $end_date);
				array_push($users, $user);
			}
		}
		
	}
	
}
	
if ($school_id > 0) {
	// ***** USERS ***** //
	include_once("camps/includes/classes/user.php");
	$users_select = array();
	
	if ($class_id > 0) 
		$sql = "SELECT u.* FROM users AS u LEFT JOIN classes AS c USING (school_id, class_id) WHERE u.school_id=" . $school_id . " AND user_registered IS NOT NULL AND u.class_id=" . $class_id . " ORDER BY c.class_grade, c.class_sub,last, first"; 	
	else
		$sql = "SELECT u.* FROM users AS u LEFT JOIN classes AS c USING (school_id, class_id) WHERE u.school_id=" . $school_id . " AND user_registered IS NOT NULL ORDER BY c.class_grade, c.class_sub,last, first"; 		
	
	$query = mysql_query($sql);
	$number_of_students = mysql_num_rows($query);
	while ($row = mysql_fetch_assoc($query)) {
			$user_select = new user($row);
			$user_select->get_school_class();
			array_push($users_select, $user_select);
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
	$sql = "SELECT * FROM reports WHERE report_type='mission_cover_sheet' AND visibility != 'none' AND start_date < " . $report_start_date . " ORDER BY start_date";
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
// ***** SCHOOLS ***** //
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Print Missions - Tzivos Hashem Management System</title>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		
		<script type="text/javascript">
			function check_number_of_students(number_of_students, school_id, class_id) {
				if (number_of_students == 0 && school_id > 0) {
					if (class_id > 0)
						alert("There are no registered students for this school/platoon.");						
					else
						alert("There are no registered students for this school.");
				}
			}
		</script>

	</head>

	<body onload="check_number_of_students(<?=$number_of_students;?>, <?=$school_id;?>, <?=$class_id;?>);">
		
		<? include('admin_header.php'); ?>
		
		<input type="hidden" name="start_date" value="<?=$start_date_greg;?>">
		<input type="hidden" name="end_date" value="<?=$end_date_greg;?>">
		
		<script type="text/javascript" src="scripts/functions.js"></script>
		<script type="text/javascript" src="scripts/jquery.styleselect.js"></script>
		
		<script type="text/javascript">
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
				
				$(".user_list select, .campaign_list select").sSelect().change(function () {
					if (number_of_students > 0)
						$(this).closest('form').submit();
				})
				$(".class_list select").sSelect().change(function () {
					$(this).closest('form').submit();
				})				
				$(".school_list select").sSelect().change(function () {
					document.getElementById("change_school").value = "true";
					$(this).closest('form').submit();
				})
				
				$(".date_list select").sSelect().change(function () {
					if (number_of_students > 0)
						document.forms["date_tasks_print"].submit();
				})
												
				$(".marking_list #display_submit").hide();
			});
		</script>
		
		<div class="body left">
						
			<H1>Print Missions</H1>

<? if ($school_id < 1) : ?>
			<form name="date_tasks_print" id="date_tasks_print" action="date_tasks_print.php" method="post" accept-charset="UTF-8">
				<input type="hidden" name="action" id="action" value="produce_selects">	
				<input type="hidden" name="change_school" id="change_school" value="false">
				
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
			<form name="date_tasks_print" id="date_tasks_print" action="date_tasks_print.php" method="post" accept-charset="UTF-8">
				<input type="hidden" name="action" id="action" value="print_report">
				<input type="hidden" name="school_id" id="school_id" value="<?=$school_id;?>">
				<input type="hidden" name="change_school" id="change_school" value="false">
				
				<div class="infobox2 marking_list clearfix">
				
					<!-- ***** SCHOOL ***** -->
					<? if ($admin->auth == "super") : ?>
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
							<option value="-1"><?=T_('All Platoons')?>
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
							<option value="-1">All students</option>
							<? for ($uno = 0; $uno < count($users_select); $uno++) : ?>
								<? $user_select = $users_select[$uno]; ?>
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
						<input type="hidden" name="number_of_students" id="number_of_students" value="<?=$number_of_students;?>">
						<? if ($number_of_students > 0) : ?>
						<input class="submit" type="submit" value="PRINT" onclick="check_number_of_students(<?=$number_of_students;?>);">
						<? endif; ?>
					</center>
					

				</div>				

			</form>

			
			
			
			
			
<? if ($action == "print_report" && $change_school == "false") : ?>				
<DIV id="tasks_div">
	
	<? for ($uno = 0; $uno < count($users); $uno++) : ?>
	
		<? $user = $users[$uno]; ?>
		
		<H2><?=$user->first;?> <?=$user->last;?></H2>
				
		<br />
						
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

	
	<? endfor; ?> <!-- for ($uno = 0; $uno < count(); $uno++) -->
	
</DIV>
<? endif; ?> <!-- if ($action == "print_report" && $change_school == "false") -->
			
<? endif; ?> <!-- if ($school_id == 0) -->
			
		</div> <!-- <div class="body"> -->
		
		
	</body>	
</html>
