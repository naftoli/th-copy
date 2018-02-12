<?php
include ("db.php");

//**********************************//
// ***** Get the campers rank ***** //
$camp_id = $_GET['camp_id'];
$user_id = $_GET['user_id'];
$sql = "SELECT u.*, r.rank_name, c.camp_number, c.camp_name, c.camp_logo_id ";
$sql = $sql . "FROM users AS u ";
$sql = $sql . "JOIN rank_marks AS rm USING (user_id) ";
$sql = $sql . "JOIN ranks AS r USING (rank_ord) ";
$sql = $sql . "JOIN camps AS c USING (camp_id) ";
$sql = $sql . "WHERE user_id=" . $user_id;
$user = mysql_fetch_assoc((mysql_query($sql)));
// ***** Get the campers rank ***** //
//**********************************//

//****************************************//
// ***** Get desired weekly periods ***** //
$weekly_camp_tasks = array();
$camp_task_ids = array();

$todays_date = get_todays_julian_date();

$start_date = 0;
$todays_day_number = date("N");
if ($todays_day_number < 7)
	$start_date = $todays_date - $todays_day_number;
else
	$start_date = $todays_date;	
$start_date = $start_date - 14;

$weeks = array();
for ($cntr = 0; $cntr < 5; $cntr++) {	
	if ($cntr > 0)
		$start_date = $start_date + ($start + 7);	
	$end_date = $start_date + 6;
	$element = compact('start_date', 'end_date');
	array_push($weeks, $element);
}
// ***** Get desired weekly periods ***** //
//****************************************//

//$title = jdtogregorian($start_date) . " - " . jdtogregorian($end_date);

//***************************************************************//
// ***** Get any tasks that fall within the desired period ***** //
for ($wno = 0; $wno < count($weeks); $wno++) {
	$start_date = $weeks[$wno]['start_date'];
	$end_date = $weeks[$wno]['end_date'];
	$title = jdtogregorian($weeks[$wno]['start_date']) . " - " . jdtogregorian($weeks[$wno]['end_date']); 
	////echo $title . "\n";
	
	$sql = "SELECT mt.camp_task_id, ct.task_name, ct.points, cm.camp_mission_id, cm.mission_name, cc.camp_campaign_id, cc.campaign_name, cc.logo_id ";
	$sql = $sql . "FROM member_tasks AS mt ";
	$sql = $sql . "JOIN camp_tasks AS ct USING (camp_task_id) ";
	$sql = $sql . "JOIN camp_missions AS cm USING (camp_mission_id) ";
	$sql = $sql . "JOIN camp_campaigns AS cc USING (camp_campaign_id) ";
	$sql = $sql . "WHERE user_id=" . $user_id . " AND (task_date > " . ($weeks[$wno]['start_date'] - 1) . " AND task_date < " . ($weeks[$wno]['end_date'] + 1) . ") ";
	$sql = $sql . "GROUP BY mt.camp_task_id ";
	$sql = $sql . "ORDER BY cc.campaign_id, cm.mission_id, mt.camp_task_id ";
	////echo $sql . "\n";
	
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$camp_task_id = $row['camp_task_id'];
		$task_name = $row['task_name'];
		$points = $row['points'];
		$mission_name = $row['mission_name'];
		$campaign_name = $row['campaign_name'];
		$logo_id = $row['logo_id'];
			
		$element = compact('camp_task_id', 'task_name', 'points', 'mission_name', 'campaign_name', 'logo_id');
		array_push($camp_task_ids, $element);
	}
	
	//echo "1) START DATE:" . $start_date . " END DATE:" . $end_date . "\n";
	$element = compact('title', 'start_date', 'end_date', 'camp_task_ids');
	array_push($weekly_camp_tasks, $element);
	$camp_task_ids = array();
}
// ***** Get any tasks that fall within the desired period ***** //
//***************************************************************//

for ($i = 0; $i < count($weekly_camp_tasks); $i++) {
	$start_date = $weekly_camp_tasks[$i]['start_date'];
	$end_date = $weekly_camp_tasks[$i]['end_date'];

	//echo "START DATE:" . $start_date . " END DATE:" . $end_date . " " . $weekly_camp_tasks[$i]['title'] . "\n";
	$camp_task_ids = $weekly_camp_tasks[$i]['camp_task_ids'];
	for ($x = 0; $x < count($camp_task_ids); $x++) {
		//echo $camp_task_ids[$x]['camp_task_id'] . " " . $camp_task_ids[$x]['task_name'] . "\n";
	}
}
//echo "****************************************************************\n";

//*******************************************************************//
// ***** Get all tasks that fall in between the desired period ***** //
$user_tasks = array();
$user_weekly_tasks = array();

for ($wtno = 0; $wtno < count($weekly_camp_tasks); $wtno++) {
	$title = $weekly_camp_tasks[$wtno]['title'];
	$start_date = $weekly_camp_tasks[$wtno]['start_date'];
	$end_date = $weekly_camp_tasks[$wtno]['end_date'];
	
	//echo "START DATE:" . $start_date . " END DATE:" . $end_date . "\n";
	
	$camp_task_ids = $weekly_camp_tasks[$wtno]['camp_task_ids'];
	////echo "# OF CAMP TASK IDS:" . count($weekly_camp_tasks[$wtno]['camp_task_ids']) . "\n";
	
	for ($ctno = 0; $ctno < count($camp_task_ids); $ctno++) {
		
		$camp_task_id = $camp_task_ids[$ctno]['camp_task_id'];
		$task_name = $camp_task_ids[$ctno]['task_name'];
		$points = $camp_task_ids[$ctno]['points'];
		$mission_name = $camp_task_ids[$ctno]['mission_name'];
		$campaign_name =$camp_task_ids[$ctno]['campaign_name'];
		$logo_id = $camp_task_ids[$ctno]['logo_id'];
		
		for ($dno = 0; $dno < 7; $dno++) {
			$task_date = $start_date + $dno;
			
			$sql = "SELECT mt.member_task_id, mt.completed ";
			$sql = $sql . "FROM member_tasks AS mt ";
			$sql = $sql . "WHERE mt.user_id=" . $user_id . " AND mt.camp_task_id=" . $camp_task_id . " AND mt.task_date=" . $task_date;
			////echo $sql . "\n";
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);
			$member_task_id = $row['member_task_id'];

			if ($member_task_id > 0) 
				$completed = $row['completed'];
			else 
				$completed = "2";
				
			$element = compact('campaign_name', 'logo_id', 'mission_name', 'task_name', 'points', 'task_date', 'completed');
			//print_r($element);
			array_push($user_tasks, $element);
		}	
		
		
	}

	$element = compact('title', 'user_tasks');
	array_push($user_weekly_tasks, $element);
	$user_tasks = array();
}

//print_r($user_weekly_tasks);
////echo "# OF USER WEEKLY TASKS:" . count($user_weekly_tasks) . "\n";

$user_campaigns = array();
for ($uno = 0; $uno <  count($user_weekly_tasks); $uno++) {
	$title = $user_weekly_tasks[$uno]['title'];
	$user_tasks = $user_weekly_tasks[$uno]['user_tasks'];
	
	$campaigns = array();
	$missions = array();
	$tasks = array();
	$days = array();

	$campaign_name = "";
	$mission_name = "";
	$task_name = "";

	$num_rows = count($user_tasks);
	$row_num = 0;
	$total_tasks = 0;
	$tasks_completed = 0;
	$mission_total_miles = 0;
	$mission_miles_completed = 0;
	$task_total_miles = 0;
	$task_miles_completed = 0;

	for ($tno = 0; $tno < $num_rows; $tno++) {
		$row_num++;
		
		$prev_campaign_name = $user_tasks[$tno]['campaign_name']; 
		$prev_mission_name = $user_tasks[$tno]['mission_name']; 
		$prev_task_name = $user_tasks[$tno]['task_name']; 
		
		// TASKS //
		if ($prev_task_name != $task_name && $task_name != "") {
			$element = compact('task_name', 'points', 'task_miles_completed', 'task_total_miles', 'days');
			array_push($tasks, $element);
			$days = array();
			$task_total_miles = 0;
			$task_miles_completed = 0;
		}	
		// TASKS //
		
		// MISSIONS //
		if ($prev_mission_name != $mission_name && $mission_name != "") {
			$element = compact('mission_name', 'total_tasks', 'tasks_completed', 'mission_total_miles', 'mission_miles_completed', 'tasks');
			array_push($missions, $element);
			$tasks = array();
			$tasks_completed = 0;
			$total_tasks = 0;
			$mission_total_miles = 0;
			$mission_miles_completed = 0;
		}
		// MISSIONS //
		
		// CAMPAIGNS //
		if ($prev_campaign_name != $campaign_name && $campaign_name != "") {
			$element = compact('campaign_name', 'logo_id', 'missions');
			array_push($campaigns, $element);
			$missions = array();
		}	
		// CAMPAIGNS //
		
		$points = $user_tasks[$tno]['points'];
			
		// DAYS //	
		$completed = $user_tasks[$tno]['completed'];	
		if ($completed == "1") {
			$tasks_completed++;
			$mission_miles_completed = $mission_miles_completed + $points;
			$task_miles_completed = $task_miles_completed + $points;
			
		}
		if ($completed == "0" || $completed == "1") {
			$total_tasks++;
			$mission_total_miles = $mission_total_miles + $points;
			$task_total_miles = $task_total_miles + $points;
		}
		////echo "1) prev_campaign_name:" . $prev_campaign_name . " points:" , $points . " mission_miles_completed:" . $mission_miles_completed . "\n";

		$element = compact('completed');
		array_push($days, $element);
		// DAYS //
			
		$logo_id = $user_tasks[$tno]['logo_id'];
		$campaign_name = $prev_campaign_name;	
		$mission_name = $prev_mission_name;	
		$task_name = $prev_task_name;	
		
		
		if ($row_num == $num_rows) {
			$element = compact('task_name', 'points', 'task_miles_completed', 'task_total_miles', 'days');
			array_push($tasks, $element);	
			$element = compact('mission_name', 'total_tasks', 'tasks_completed', 'mission_total_miles', 'mission_miles_completed', 'tasks');
			array_push($missions, $element);
			$element = compact('campaign_name', 'missions');
			array_push($campaigns, $element);	
		}
		
	}

	$element = compact('title', 'campaigns');
	array_push($user_campaigns, $element);
}



//print_r($user_campaigns);

function get_todays_julian_date() {
	$todays_day = date("j"); 
	$todays_month = date("n"); 
	$todays_year = date("Y"); 
	$today_jd = cal_to_jd  (CAL_GREGORIAN, $todays_month,  $todays_day, $todays_year);

	return $today_jd;
}

$prev_task_name = "";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		
		<title>Hachayal Kiosk</title>
		
		<link rel="alternate" media="print" href="index.php">
		<link href="styles/shadowbox.css" rel="stylesheet" type="text/css" >
		<link href="styles/reset.css" rel="stylesheet" type="text/css" />
		<link href="styles/style.css" rel="stylesheet" type="text/css" />
		<link href="styles/print.css" rel="stylesheet" type="text/css" media="print" />
		
		<script src="http://www.google.com/jsapi"></script>
		
		<script>
			// Load jQuery
			google.load("jquery", "1.3.2");
		</script>
	</head>


	<script type="text/javascript" src="scripts/easySlider1.7.js"></script>
	
	<script type="text/javascript">
		$(document).ready(function(){
			$("#slider").easySlider({
				numeric: true, 
				controlsBefore:	'<div class="page_dots">',
				controlsAfter:	'</div>'
				});
		});	
		
		$(window).load(function(){
			sliderPage();
		});	
		
		function sliderPage() {
					return false;
		}
	</script>
	
	<script type="text/javascript" src="scripts/jquery.scroll.js"></script>
	
	<script type="text/javascript">
		$(function() {
			$('.scroll-pane').jScrollPane({showArrows:true, scrollbarWidth: 42, arrowSize: 42});
		});
	</script>
	
	<link href="styles/jquery.scroll.css" rel="stylesheet" type="text/css" />
	
	<script src="scripts/jquery.tools.tabs.min.js">
	</script>
	
	<script>
		$(function(){
			$(".campaign_list").tabs(".progress_list",{effect:'fade'});
		})
	</script>
	
	<body class="blue">
	
		<div id="wrapper">
		
			<? include ("camp_header.php"); ?>
		
			<div id="main">
			
				<div id="page_title">
					Medals
				</div>
			
				<div class="three_column padding_top">
				
					<div class="content">
					
						<div id="slider">
						
							<ul>
							
							<? for ($uno = 0; $uno < count($user_campaigns); $uno++) : ?>
								<? $campaigns = $user_campaigns[$uno]['campaigns']; ?>
								
								<li class="mission_week">
								
									<div class="slider_title">
										<?=$user_campaigns[$uno]['title'];?>
									</div>
									
									<? for ($cno = 0; $cno < count($campaigns); $cno++) : ?>
									<input type="hidden" value="<?=$campaigns[$cno]['campaign_name'];?>">
									<? endfor; ?>
									
									<!-- ***** MEDALS ***** -->
									
									<div class="campaign_list">
																			
										<div class="campaign">
											<!--<div class="icon icon_tefilla"></div>-->
											<img src="images/Teffilah.jpg" width="45" height="45">
										</div>
										
										<div class="campaign">
											<!--<div class="icon icon_tehillim"></div>-->
											<img src="images/LearningClasses.png" width="45" height="45">
										</div>
										
										<div class="campaign">										
											<!--<div class="icon icon_yom"></div>-->
											<img src="images/ballpeh.jpg" width="45" height="45">
										</div>
										
										
									</div>
									
									<!-- ***** MEDALS ***** -->
									
									<? for ($cno = 0; $cno < count($campaigns); $cno++) : ?>
									<div class="progress_list">
									
										<div class="scroll-pane">
										
											<!-- ********** MISSIONS ********** -->
											<? $missions = $campaigns[$cno]['missions']; ?>								
											<? for ($mno = 0; $mno < count($missions); $mno++) : ?>										
											<div class="mission">
											
												<div class="header clearfix">												
												
													<div class="title clearfix">
														<div class="name">
															<?=$campaigns[$cno]['campaign_name'];?> - <?=$missions[$mno]['mission_name'];?>															
														</div>
														<div class="info">
															<?=$missions[$mno]['tasks_completed'];?>/<?=$missions[$mno]['total_tasks'];?> Completed<br />
															<?=$missions[$mno]['mission_miles_completed'];?>/<?=$missions[$mno]['mission_total_miles'];?> Miles
														</div>
													</div>
													
													<div class="meter">
														<?
															if ($missions[$mno]['tasks_completed'] > 0)
																$width = ($missions[$mno]['tasks_completed'] / $missions[$mno]['total_tasks']) * 100; 
															else
																$width = 0; 
														?>
														<input type="hidden" value="<?=$width;?>">
														<div class="back" style="width:<?=$width;?>%">
														</div>
														<div class="front">
														</div>											
													</div> 
												
													<div class="week">
														SMTWTFS
													</div>						
													
												</div> <!-- header clearfix -->
											
												<!-- ********** TASKS ********** -->
												<? $tasks = $missions[$mno]['tasks']; ?>
												<? for ($tno = 0; $tno < count($tasks); $tno++) : ?>
												
												<div class="task clearfix">	
																											
													<div class="title">
														<div class="name">
															<?=$tasks[$tno]['task_name'];?>
														</div>													
														<div class="miles">
															Mission Task - <?= $tasks[$tno]['points'];?> Mile(s) per day
														</div>						
													</div>
																																										
													<div class="day_progress">
													<? $days = $tasks[$tno]['days']; ?>
													<? for ($dno = 0; $dno < count($days); $dno++) : ?>
														<? if ($days[$dno]['completed'] == 1) : ?>
														<div class="day checked"></div>
														<? elseif ($days[$dno]['completed'] == 0) : ?>
														<div class="day"></div>
														<? else : ?>
														<div class="noday"></div>
														<? endif; ?>
													<? endfor; ?>
													</div>
													
													<div class="miles_progress">
														<?=$tasks[$tno]['task_miles_completed'];?>/<?=$tasks[$tno]['task_total_miles'];?> Miles
													</div>
													
												</div>
												<? endfor; ?>
												
												<!-- ********** TASKS ********** -->
														
											</div> <!-- MISSION -->
											<? endfor; ?>
											<!-- ********** MISSIONS ********** -->
											
										</div> <!-- SCROLL PANE -->
										
									</div> <!-- PROGRESS LIST -->									
									<? endfor; ?>
									
								</li>
								
							<? endfor; ?>
							
							</ul>
							
						</div> <!-- SLIDER -->
												
					</div> <!-- CONTENT -->
					
				</div> <!-- three_column padding_top -->
				
			</div> <!-- MAIN -->
			
			<div id="footer">
				<div class="footer_logo">
				</div>
				
				<div class="footer_logout">
				</div>      		
			</div> <!-- FOOTER -->
			
			
		</div> <!-- WRAPPER -->
		
		<script>
			$(function(){
				var middle_li = document.getElementById("controls3");
				$(middle_li).find("a").click();
			})			
		</script>
		
	</body>


</html>