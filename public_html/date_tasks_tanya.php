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
		$user->get_user_tracks(-1, $start_date, $end_date);	
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
		<title>Print Mission Sheets - Tzivos Hashem Management System</title>
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
		<script type="text/javascript" src="scripts/jquery.autocolumn.js"></script>
		
		<script type="text/javascript">
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
						//$('#action').val("produce_report");
						//$('#hidden_user_id').val(data.user_id);
						//$('#class_id').val(data.class_id);
						
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
				
				$('.marking.module').addClass('dontsplit');
				$('#weekly_div,#tasks_page_two').columnize({ columns: 2 });

				$('.slider:last .list_expand li h3').nextAll().hide();
				$('.slider:last .list_expand li h3').click(function(){
					$(this).nextAll().slideToggle('fast');
					$(this).parents('li').toggleClass('open');
				});

			});			
		</script>
		
		<div class="body left marking_missions">
						
			<H1>Print Tanya Schedules</H1>
			
<? if ($school_id < 1) : ?>
			<form name="date_tasks_report" id="date_tasks_report" action="date_tasks_tanya.php" method="post" accept-charset="UTF-8">
				<input type="hidden" name="action" id="action" value="">	
				<input type="hidden" name="hidden_user_id" id="hidden_user_id" value="">
				<input type="hidden" name="class_id" id="class_id" value="">
				
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
			<form name="date_tasks_report" id="date_tasks_report" action="date_tasks_tanya.php" method="post" accept-charset="UTF-8">
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
							<option><?=T_('Choose a Platoon')?></option>
							<option value="0"><?=T_('All Platoons')?></option>
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
							<option value="-1">All students</option>
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
										
					
				</div>				

			</form>

	<div class="noprint">
		<div class="module clearfix generate">
			<p>Generate Mission Sheets by choosing an option from all the fields above.</p>
		</div>
		<div class="module clearfix">
			<div class="list_expand">
				<ul>
					<li>
						<h3><span class="icon"></span>Print Instructions</h3>
						<p><img src="images/Print-Dialog-Small-2.jpg" align="right" /><img src="images/Print-Dialog-Small-1.jpg" align="right" />
							In your browser click 'File' then 'Page Setup...'</p>
							<p>Step 1: Set the Orientation to Portrait</p>
							<p>Step 2: Check 'Shrink to fit Page Width'</p>
							<p>Step 3: In Options check 'Print Background (colors & images)'</p>
							<p>Step 4: In the second tab set all Margins to 0.5 inches (All Sides)</p>
							<p>Step 5: Set all Headers & Footers to Blank</p>
							<p>Note: The browser will save these preferences for later use.</p>
					</li>
				</ul>
			</div>
		</div>
		<div class="module clearfix generate">
			<h3>Mission Sheets were generated<br/>for the whole school<br/>(52 Sheets - 104 Pages)</h3>
			<p><a href="javascript:window.print()" class="button">Print</a></p>
		</div>
	</div>

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
					
	<? if ($action == "produce_report" || 1==1) : ?>			

	<div class="print_header">
		<div class="marking module clearfix">
			<div class="rank_image"><img src="/file_view.php?id=3933070802" height="70" /></div>
			<div class="user_image"><img src="/file_view.php?id=640806900" height="70" /></div>
			<p class="print_page">&#1489;"&#1492;</p>
			<p class="print_name">Private Malka Sima Bortman</p>
			<p class="print_week">My Tanya Schedule for 5771</p>
			<p class="print_class">My Weekly Quota: 1.01 lines</p>
			<p class="print_sig">End of the year: 9 lines</p>
			<!--<p class="print_instructions">Fill out your mission sheet and review it with your commander, who will give you a campaign sticker for each mission you have completed.</p>-->
		</div>
	</div>
	

	<div class="tanya module clearfix">
		<div class="clearfix">
		<div class="mission">
			<div class="mission_number">#1</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-1</div>
		</div>
		<div class="mission">
			<div class="mission_number">#2</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-2</div>
		</div>
		<div class="mission">
			<div class="mission_number">#3</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-3</div>
		</div>
		<div class="mission">
			<div class="mission_number">#4</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-4</div>
		</div>
		<div class="mission">
			<div class="mission_number">#5</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-5</div>
		</div>
		<div class="mission">
			<div class="mission_number">#6</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-6</div>
		</div>
		<div class="mission">
			<div class="mission_number">#7</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-7</div>
		</div>
		<div class="mission">
			<div class="mission_number">#8</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-8</div>
		</div>
		<div class="mission">
			<div class="mission_number">#9</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-9</div>
		</div>
		<div class="mission">
			<div class="mission_number">#10</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-10</div>
		</div>
		<div class="mission">
			<div class="mission_number">#1</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-11</div>
		</div>
		<div class="mission">
			<div class="mission_number">#12</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-12</div>
		</div>
		<div class="mission">
			<div class="mission_number">#13</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-13</div>
		</div>
		<div class="mission">
			<div class="mission_number">#14</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-14</div>
		</div>
		<div class="mission">
			<div class="mission_number">#15</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-15</div>
		</div>
		</div>
		<div class="tanya tanya_medal clearfix">
			<div class="box">15 Missions
			</div>
			<div class="box medal"><img src="file_view.php?id=1172604632" width="72" height="75" />
			</div>
			<div class="box">White Medal
			</div>
		</div>	
	</div>	
	<div class="tanya module clearfix">
		<div class="clearfix">
		<div class="mission">
			<div class="mission_number">#1</div>
			<div class="mission_date">22 </div>
			<div class="mission_lines">Lines 1-16</div>
		</div>
		<div class="mission">
			<div class="mission_number">#2</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-17</div>
		</div>
		<div class="mission">
			<div class="mission_number">#3</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-18</div>
		</div>
		<div class="mission">
			<div class="mission_number">#4</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-19</div>
		</div>
		<div class="mission">
			<div class="mission_number">#5</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-20</div>
		</div>
		<div class="mission">
			<div class="mission_number">#6</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-21</div>
		</div>
		<div class="mission">
			<div class="mission_number">#7</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-22</div>
		</div>
		<div class="mission">
			<div class="mission_number">#8</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-23</div>
		</div>
		<div class="mission">
			<div class="mission_number">#9</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-24</div>
		</div>
		<div class="mission">
			<div class="mission_number">#10</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-25</div>
		</div>
		<div class="mission">
			<div class="mission_number">#11</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-26</div>
		</div>
		<div class="mission">
			<div class="mission_number">#12</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-27</div>
		</div>
		<div class="mission">
			<div class="mission_number">#13</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-28</div>
		</div>
		<div class="mission">
			<div class="mission_number">#14</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-29</div>
		</div>
		<div class="mission">
			<div class="mission_number">#15</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-30</div>
		</div>
		<div class="mission">
			<div class="mission_number">#16</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-31</div>
		</div>
		<div class="mission">
			<div class="mission_number">#17</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-32</div>
		</div>
		<div class="mission">
			<div class="mission_number">#18</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-33</div>
		</div>
		<div class="mission">
			<div class="mission_number">#19</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-34</div>
		</div>
		<div class="mission">
			<div class="mission_number">#20</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-35</div>
		</div>
		</div>
		<div class="tanya tanya_medal clearfix">
			<div class="box">20 Missions
			</div>
			<div class="box medal"><img src="file_view.php?id=382230352" width="72" height="75" />
			</div>
			<div class="box">Red Medal
			</div>
		</div>	
	</div>	
	
	<div class="tanya module clearfix">
		<div class="clearfix">
		<div class="mission">
			<div class="mission_number">#1</div>
			<div class="mission_date">22 </div>
			<div class="mission_lines">Lines 1-36</div>
		</div>
		<div class="mission">
			<div class="mission_number">#2</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-37</div>
		</div>
		<div class="mission">
			<div class="mission_number">#3</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-38</div>
		</div>
		<div class="mission">
			<div class="mission_number">#4</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-39</div>
		</div>
		<div class="mission">
			<div class="mission_number">#5</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-40</div>
		</div>
		<div class="mission">
			<div class="mission_number">#6</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-41</div>
		</div>
		<div class="mission">
			<div class="mission_number">#7</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-42</div>
		</div>
		<div class="mission">
			<div class="mission_number">#8</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-43</div>
		</div>
		<div class="mission">
			<div class="mission_number">#9</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-44</div>
		</div>
		<div class="mission">
			<div class="mission_number">#10</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-45</div>
		</div>
		<div class="mission">
			<div class="mission_number">#11</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-46</div>
		</div>
		<div class="mission">
			<div class="mission_number">#12</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-47</div>
		</div>
		<div class="mission">
			<div class="mission_number">#13</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-48</div>
		</div>
		<div class="mission">
			<div class="mission_number">#14</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-49</div>
		</div>
		<div class="mission">
			<div class="mission_number">#15</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-50</div>
		</div>
		<div class="mission">
			<div class="mission_number">#16</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-51</div>
		</div>
		<div class="mission">
			<div class="mission_number">#17</div>
			<div class="mission_date">Date</div>
			<div class="mission_lines">Lines 1-52</div>
		</div>
		</div>
	</div>	
	
	</form>
	
</DIV>
	
	
	<? endif; ?> <!-- if ($action == "produce_report") -->
			
<? endif; ?> <!-- if ($school_id == 0) -->
			
		</div> <!-- <div class="body"> -->
		
		
	</body>	
</html>
