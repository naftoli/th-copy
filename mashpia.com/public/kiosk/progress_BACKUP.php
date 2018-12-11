<?php
include ("../db.php");
include ("../lang.php");
include ("classes/week.php");
include ("includes/header.php"); 

include ("get_user_id.php");
$user_id = get_user_id();
$camp_id = get_camp_id($user_id);

$todays_date = get_todays_julian_date();

$start_date = 0;
$todays_day_number = date("N");
if ($todays_day_number < 7)
	$start_date = $todays_date - $todays_day_number;
else
	$start_date = $todays_date;	
$start_date = $start_date - 14;

//****************************************//
// ***** Get desired weekly periods ***** //
$weeks = array();
for ($cntr = 0; $cntr < 5; $cntr++) {	
	if ($cntr > 0)
		$start_date = $start_date + ($start + 7);	
	$end_date = $start_date + 6;
	$week = new week_two($user_id, $start_date, $end_date);
	$week->get_campaigns($todays_date);
	array_push($weeks, $week);
}
// ***** Get desired weekly periods ***** //
//****************************************//

function get_todays_julian_date() {
	$todays_day = date("j"); 
	$todays_month = date("n"); 
	$todays_year = date("Y"); 
	$today_jd = cal_to_jd(CAL_GREGORIAN, $todays_month,  $todays_day, $todays_year);
	return $today_jd;
}
?>



	
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
					Progress
				</div>
			
				<div class="three_column padding_top">
				
					<div class="content">
					
						<div id="slider">
						
							<ul>
							
							<? for ($wno = 0; $wno < count($weeks); $wno++) : ?>
								<? $week = $weeks[$wno]; ?>
								
								<li class="mission_week">
								
									<div class="slider_title">
										<?=$weeks[$wno]->title;?>
									</div>
									
									<div class="campaign_list">
										<? for ($cno = 0; $cno < count($week->campaigns); $cno++) : ?>
											<? $campaign = $week->campaigns[$cno]; ?>
																						
											<div class="campaign">
												<div>
													<span style="font-size:10px;"><?=$campaign['campaign_name'];?>
													</span>
												</div>
												<img src="images/<?=$campaign['campaign_name'];?>.png" width="45" height="45">
											</div>									
										<? endfor; ?>
									</div>
									
									<? for ($cno = 0; $cno < count($week->campaigns); $cno++) : ?>
										<? $campaign = $week->campaigns[$cno]; ?>
										
										<div class="progress_list">
										
											<div class="scroll-pane">
											
												<? for ($mno = 0; $mno < count($campaign['missions']); $mno++) : ?>
													<? $mission = $campaign['missions'][$mno]; ?>
												
													<div class="mission">
													
														<div class="header clearfix">	
														
															<div class="title clearfix">
																<div class="name">
																	<?=$mission['mission_name'];?> 
																</div>
																																
																<div class="info">
																	<?=$mission['mission_tasks_completed'];?>/<?=$mission['no_of_tasks'];?> Completed<br>
																	<?=$mission['mission_points_completed'];?>/<?=$mission['mission_points'];?> Miles
																</div>																
															</div>
														
															<div class="meter">
																<input type="hidden" value="0">
																<div style="width: 0%;" class="back">
																</div>
																<div class="front">
																</div>											
															</div>
													
															<div class="week">
																SMTWTFS
															</div>
															
														</div>
														
														<!-- ********** TASKS ********** -->
														<? for ($tno = 0; $tno < count($mission['tasks']); $tno++) : ?>
														<? $task = $mission['tasks'][$tno]; ?>
														<div class="task clearfix">	
														
															<div class="title">
																<div class="name">
																	<?=$task['task_name'];?>
																</div>	

																<div class="miles">
																	Mission Task - <?=$task['task_points'];?> Mile(s) per day
																</div>																
															</div>
															
															<div class="day_progress">
																<? for ($tdno = 0; $tdno < count($task['task_dates']); $tdno++) : ?>
																
																	<? $task_date = $task['task_dates'][$tdno]; ?>
																	
																	<input type="hidden" name="COMPLETED" value="<?=$task_date['completed'];?>">
																	
																	<? if ($task_date['completed'] == 1) : ?>
																		<div class="day checked"></div>
																	<? elseif ($task_date['completed'] == 0) : ?>																																	
																		<div class="day"></div>
																	<? elseif ($task_date['completed'] == 2) : ?>
																		<div class="day future"></div>
																	<? else : ?>
																		<div class="noday"></div>
																	<? endif; ?>
																
																<? endfor; ?>
																															
															</div>
															
															<div class="miles_progress"><?=$task['completed_points'];?>/<?=$task['total_points'];?> Miles</div>
															
														</div>
														<? endfor; ?>
														<!-- ********** TASKS ********** -->														
														
													</div>												
												<? endfor; ?>
												
											</div>
											
										</div>
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