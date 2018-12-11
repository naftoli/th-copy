<?php
$admin_auth = array('user');
require('header.php'); 

$_SESSION['program_name'] = 'children_tasks';

include("camps/includes/classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
$admin->get_markable_children();

include('classes/Reports.class.php');
$Reports = new Reports();


$child_id = 0;
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

if (isset($_POST['child_id'])) {
	$child_id = $_POST['child_id'];
	$sql = "SELECT school_id FROM users WHERE user_id=" . $child_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$school_id = $row['school_id'];
	$subject_id = -1;
}
elseif (count($admin->children) == 1) {
	$child_id = $admin->children[0]->user_id;
	$school_id = $admin->children[0]->school_id;
}	

$selected_dates = "";
$action = "";
$show = false;
if (isset($_POST['action']) || count($admin->children) == 1) {

	$show = true;
	
	if (isset($_POST['action']))
		$action = $_POST['action'];
	
	if ($action == "produce_report" || count($admin->children) == 1) {
				
		include("classes/user_track.php");
		include("classes/date_tasks_mission.php");
		include("classes/daily_task.php");
		include("classes/weekly_task.php");
		include("classes/shabbos_task.php");
		include("classes/no_label_task.php");
		include("classes/task.php");
		include("classes/date_tasks_mark.php");
		
		$child_info = explode(':', $_POST['children']);
		$period_info = explode(':', $_POST['periods']);

		$periods = array();
		foreach ($period_info as $period){
			$Reports->get_report_name($period);
			
			
			$children = array();
			foreach ($child_info as $child){
			
				$user_id = $child;
				
				$subject_id = -1;
				
				
				if (isset($_POST['date_list'])) {		
					$date_list = explode(":", $_POST['date_list']);
					$start_date = $date_list[0]; 
					$end_date = $date_list[1];
					$selected_dates = $start_date . ":" . $end_date;
				}
				else {
					$sql = "SELECT * ";
					$sql .= "FROM reports ";
					$sql .= "WHERE report_type='mission_cover_sheet' AND ";
					$sql .= "visibility != 'none' AND ";
					$sql .= "start_date >= " . ($Reports->start + 7) . " ";
					$sql .= "ORDER BY start_date ";
					$sql .= "LIMIT 1";	
					$query = mysql_query($sql);
					$row = mysql_fetch_assoc($query);
					$start_date = $row['start_date'];
					$end_date = $row['end_date'];
				}
				
				$sql = "SELECT * FROM users WHERE user_id=" . $user_id;
				$query = mysql_query($sql);
				$row = mysql_fetch_assoc($query);
				$user = new user($row);
				$user->get_school();
				$school_id = $user->school->school_id;
				$user->get_school_class();		
				$user->get_rank();
				$user->get_user_tracks($subject_id, $start_date, $end_date);
			
				array_push($children, $user);
					
			}		

			$periods[$Reports->report_name] = $children;
			
			//array_push($periods, $children);
			
		}
				
	}
	
}

if ($child_id > 0 || count($admin->children) == 1) {
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
	$sql = "SELECT * FROM reports WHERE report_type='mission_cover_sheet' AND visibility != 'none' AND start_date >= " . $start . " ORDER BY start_date";	
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$report = new report($row);
		//hide pesach and shavuos
		if ($report->start_date == 2455669 || $report->start_date == 2455718) continue;
		array_push($reports, $report);
		if ($selected_dates == "") {
			$selected_dates = $row['start_date'] . ":" . $row['end_date'];				
		}
	}
	// ***** REPORT DATES ***** //
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Childrens Tasks - Tzivos Hashem Management System</title>
		<link href="/admin_styles.css" rel="stylesheet" type="text/css">
		
		<script type="text/javascript" src="/js/jquery-1.8.1.min.js"></script>
		<script type="text/javascript" src="scripts/jquery.autocolumn.js"></script>
	</head>

	<body>

		<div id="info">
		</div>
		
		<? include('admin_header.php'); ?>			
		
		<div class="body left marking_missions" style="margin-top:50px;">
		
			<div class="infobox2 marking_list clearfix">
			
				<select multiple="multiple" name="child_id" id="child_id">	
					<? for ($cno = 0; $cno < count($admin->children); $cno++) : ?>
					<option value="<?=$admin->children[$cno]->user_id;?>">
						<?=$admin->children[$cno]->first;?> <?=$admin->children[$cno]->last;?>
					</option>
					<? endfor; ?>
				</select>

				<select multiple="multiple" name="report_period" id="report_period">
					<? foreach ($Reports->reports as $report) : ?>
					<option value="<?=$report['start_date'] . ';' . $report['end_date'];?>">
						<?=$report['report_name'];?> - <?=jdtogregorian($report['start_date']);?>
					</option>
					<? endforeach; ?>
				</select>
				
				<input type="button" id="get_tasks_button" value="GET TASKS">
				
			</div>
			
			<? if ($show == true) : ?>
			
			<? foreach ($periods as $report_name => $children) : ?>
			 
			<? foreach ($children as $user) : ?>
			<!-- ********** TASKS DIV ********** -->
			<div id="tasks_div">
			
				<!-- print_individual -->
				<div class="print_individual">						
				
					<!-- print_header -->
					<div class="print_header">
						<div class="marking module clearfix dontsplit">
																
							<div class="rank_image"><img height="70" src="/file_view.php?id=<?=$user->rank_image_id;?>"></div>
						<?
						if ($user->user_photo_id) { 
						?>
							<div class="user_image"><img height="70" src="/file_view.php?id=<?=$user->user_photo_id;?>"></div>
						<? } ?>
							<p class="print_name"><?=$user->rank_name;?> <?=$user->first;?> <?=$user->last;?></p>
							<p class="print_week">My Missions for the week of <?=$report_name;?></p>
							<p class="print_class">Grade: <?=$user->school_class->class_grade;?> - <?=$user->school_class->class_sub;?> <?=$user->school_class->class_teacher;?></p>
							<p class="print_sig">Parent's Signature<span></span></p>
						</div>
					</div>
					<!-- print_header -->
				
					<!-- DAILY TASKS -->
					<? include("daily_tasks.php"); ?>
					<!-- DAILY TASKS -->
			
					<div class="page_template" style="display:none;">
						<div style="clear:both; height:1px;"></div>
						<div class="print_header print_page_two">
						<div class="marking module clearfix">
							<p class="print_week"><?=$report_name;?></p>
							<p class="print_page">Page <span class="page_no">2</span> - &#1489;"&#1492;</p>
							<p class="print_name"><?=$user->rank_name;?> <?=$user->first;?> <?=$user->last;?></p>
						</div>
					</div>
					
					<div class="print_content"></div>
					
					<div class="footer_sticker print_only">
				<?
				$school_type = $user->school_type_id;
				if ($school_type == 2 || $school_type == 3) 
					echo "<img src='images/stickers/All.gif' height=60 />";
				else 
					echo "<img src='images/stickers/AllDaySchool.gif' height=60 />";
				?>		
					</div>
			
				</div>
				<!-- print_individual -->
			
				<div class="tasks_page_two">	
					<? include("weekly_tasks.php"); ?>
					<? include("no_label_tasks.php"); ?>
					<? include("shabbos_tasks.php"); ?>
				</div>
				</div>
	
			</div>
			<!-- ********** TASKS DIV ********** -->
			
			<div style="width:100%; height:1px; boder:1px solid black;"></div>
			
			<? endforeach; ?>
			
			<? endforeach; ?>
			
			<? endif; ?>
			
		</div>
		
		<form method="post" name="get_tasks_form" id="get_tasks_form" action="childrens_tasks2.php">
			<input type="hidden" name="action" value="produce_report" />
			<input type="hidden" name="children" id="children" value="" />
			<input type="hidden" name="periods" id="periods" value="" />
		</form>
		
		<script type="text/javascript">
		
			$(document).ready(function(){
				var content = $('#content');
				var nav = $('#nav');
				$(nav).css('height', $(content).height());
				
				$('.marking.module').addClass('dontsplit');
				$('.tasks_page_two').columnize({ 
					columns: 2, 
					lastNeverTallest: true
				});
			});	
			
			$('#get_tasks_button').click(function(){
				var children = '';
				$("#child_id option:selected").each(function(){
					children = children + $(this).val() + ':';
				});
				children = children.substr(0, children.length - 1)
				$('#children').val(children);
				
				var periods = '';
				$("#report_period option:selected").each(function(){
					periods = periods + $(this).val() + ':'
				});
				periods = periods.substr(0, periods.length - 1);				
				$('#periods').val(periods);
				
				if (children == '' || periods == '')
					alert('You must pick at least one child and one period.');
				else
					$('#get_tasks_form').submit();
			});
		</script>
	</body>	
	
</html>
