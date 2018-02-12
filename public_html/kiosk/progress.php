<?php
require_once ("../header.php");
require_once('../calendar.php');
include('../classes/user.php');
include('../classes/week.php');
include ("../classes/user_track.php");
include ("../classes/date_tasks_mission.php");
include ("../classes/date_task.php");

echo "<input type='hidden' name='USER ID' value='" . $user['user_id'] . "'>\n"; 

$sql = "SELECT * FROM users WHERE user_id=" . $user['user_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$user = new user($row);

// ***** Get the 5 weekly periods for the page (monday through sunday for ***** //
// ***** the previous two weeks, this week, and the next two weeks        ***** //
$todays_date = cal_to_jd(CAL_GREGORIAN, date("n"),  date("j"), date("Y"));

$start_date = 0;
$todays_day_number = date("N");

if ($todays_day_number < 7)
	$start_date = $todays_date - $todays_day_number;
else
	$start_date = $todays_date;	
$start_date = $start_date - 14;

$weeks = array();
for ($cntr = 0; $cntr < 5; $cntr++) 
{	
	if ($cntr > 0)
		$start_date = $start_date + 7;	
		
	$end_date = $start_date + 6;
	
	$week = new week($start_date, $end_date);
	array_push($weeks, $week);
}
// ***** Get the 5 weekly periods for the page (monday through sunday for ***** //
// ***** the previous two weeks, this week, and the next two weeks        ***** //

// ***** Get all the missions that fall during the two week period ***** //
$missions = array();
foreach ($weeks as $week)
{
	$sql = "SELECT ut.*, s.subject_name, s.subject_image_id ";
	$sql = $sql . "FROM user_tracks AS ut ";
	$sql = $sql . "JOIN subjects AS s USING (subject_id) ";	
	$sql = $sql . "WHERE ut.user_id=" . $user->user_id;
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query))
	{
		$user_track = new user_track($row);
		$user_track->get_subject_info();
		$user_track->get_missions($user->school_type_id, $week->start_date, $week->end_date);
		array_push($week->subjects, $user_track);
	}
	
	//$sql = $sql . "JOIN date_tasks_missions AS dtm ON (";
	//$sql = $sql . "dtm.school_type_id=" . $user->school_type_id . " AND ";
	//$sql = $sql . "dtm.subject_id=ut.subject_id AND ";
	//$sql = $sql . "dtm.track_id=ut.track_id AND ";
	//$sql = $sql . "dtm.level=ut.level AND ";
	//$sql = $sql . "dtm.start_date >= " . $week->start_date . " AND ";
	//$sql = $sql . "dtm.end_date <= " . $week->end_date . ") ";
	//$sql = $sql . "WHERE ut.user_id=" . $user->user_id;
}

echo "<input type='hidden' name='MISSIONS' value='" . count($missions) . "'>\n";

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
		<link href="styles/camp_style.css" rel="stylesheet" type="text/css" />
		<link href="styles/print.css" rel="stylesheet" type="text/css" media="print" />
		
		
		<script src="http://www.google.com/jsapi"></script>
		
		<script>
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
		
			<div id="main">
			
				<div id="page_title">
					Medals
				</div>
			
				<div class="three_column padding_top">
				
					<div class="content">
					
						<div id="slider">
						
							<ul>
							
								<!-- WEEKLY PERIODS -->
								<? foreach ($weeks as $week) : ?>									
								<li class="mission_week">
										
									<div class="slider_title">
										<?=$week->start_date_greg;?> - <?=$week->end_date_greg;?>
									</div>
									
									<div class="campaign_list">	
										<!-- SUBJECTS -->
										<? foreach ($week->subjects as $subject) : ?>
										<? if ($subject->show_user_track == true) : ?>
										<div class="campaign">
											<img src="../file_view.php?id=<?=$subject->subject_image_id;?>" width="45" height="45">
										</div>	
										<? endif; ?>
										<? endforeach; ?>
										<!-- SUBJECTS -->
									</div>
									
									<!-- SUBJECTS -->
									<? foreach ($week->subjects as $subject) : ?>
									<? if ($subject->show_user_track == true) : ?>									
									<div class="progress_list">
									
										<div class="scroll-pane">
										
											<!-- MISSIONS -->
											<? foreach ($subject->missions as $mission) : ?>
											<div class="mission">
												<div class="header clearfix">
													<div class="title clearfix">
														<div class="name">
															<?=$mission->mission_name;?>
														</div>
														<div class="info">
														</div>
													</div>
													
													<div class="meter">
														<? $width = 100; ?>
														
														<input type="hidden" value="">
														<div class="back" style="width:<?=$width;?>%">
														</div>
														<div class="front">
														</div>																								
													</div>
													
													<!-- START DATE TO END DATE -->
													<div class="week">
														<?=$mission->week_string;;?>
													</div>
													<!-- START DATE TO END DATE -->													
												</div>
												
												
												
												
												<!-- ********** TASKS ********** -->
												<? foreach ($mission->tasks as $task) : ?>
												<div class="task clearfix">	
													<div class="title">
														<div class="name">
															<?=$task->name;?>
														</div>													
														<div class="miles">
															Mission Task - <?= $task->points;?> Mile(s) per day
														</div>						
													</div>	

													<div class="day_progress">
														<div class='day checked'></div>
													</div>
													
													<div class="miles_progress">
													</div>
												</div>
												<? endforeach; ?>
												<!-- ********** TASKS ********** -->
												
												
												
												
											</div>
											<? endforeach; ?>
											<!-- MISSIONS -->
											
										</div>
										
									</div>
									<? endif; ?>
									<? endforeach; ?>
									<!-- SUBJECTS -->
									
								</li>									
								<? endforeach; ?>
								<!-- WEEKLY PERIODS -->
								
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