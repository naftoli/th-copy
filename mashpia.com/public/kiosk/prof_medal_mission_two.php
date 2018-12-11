<?php 
include_once ("../header.php");
include_once('../file_save.php');
include_once('../calendar.php');

$mission = gri('mission', -1);
$date_tasks_mission_id = $mission;
$mission_name = gr('mission_name', '');
$current_date = unixtojd();

if ($mission>0) {

	$sql = "SELECT dtm.*, s.subject_name ";
	$sql = $sql . "FROM date_tasks_missions AS dtm ";
	$sql = $sql . "JOIN subjects AS s USING (subject_id) ";
	$sql = $sql . "WHERE dtm.date_tasks_mission_id=" . $date_tasks_mission_id;
	$query = mysql_query($sql);
	$mission_data = mysql_fetch_assoc($query);
	$date_tasks_mission = mysql_fetch_assoc($query);
	
	$user_row = mysql_fetch_assoc(mq("
	SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
		   user_city, user_state, user_postal, user_country, user_phone,
		   user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id, team_name, 
		   school_name, school_number, school_city, school_state, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, school_id,
		   rank_name, rank_image_id, rank_color, track_id , level
	FROM users
		 LEFT JOIN schools USING (school_id)
		 LEFT JOIN institutions USING (inst_id)
		 LEFT JOIN classes USING (school_id, class_id)
		 LEFT JOIN teams USING (school_id, team_id)
		 LEFT JOIN (SELECT user_id, MAX(rank_ord) rank_ord FROM rank_marks WHERE user_id = {$user['user_id']} GROUP BY user_id) rank USING (user_id)
		 LEFT JOIN ranks USING (rank_ord)
		 LEFT JOIN (SELECT * FROM user_tracks WHERE user_id = {$user['user_id']} AND subject_id={$mission_data['subject_id']}) user_tracks USING (user_id)
	WHERE user_id = {$user['user_id']}
	ORDER BY class_grade, class_sub, last, first
	"));
		
	$current_date = unixtojd();

	$tasks = mysql_fetch_column(mq("
		SELECT * 
		FROM date_tasks
		LEFT JOIN labels ON date_tasks.label_id = labels.label_id
		LEFT JOIN date_tasks_dates USING (date_task_id)
		LEFT JOIN (SELECT * FROM date_tasks_marks WHERE user_id = {$user['user_id']} GROUP BY date_task_id) date_tasks_marks USING (date_task_id)
		WHERE
			date_tasks.date_tasks_mission_id = $mission 
		GROUP BY date_tasks.ord
		"));
		
	$sql = "SELECT * "; 
	$sql = $sql . "FROM date_tasks ";
	$sql = $sql . "LEFT JOIN labels ON date_tasks.label_id = labels.label_id ";
	$sql = $sql . "LEFT JOIN date_tasks_dates USING (date_task_id) ";
	$sql = $sql . "LEFT JOIN date_tasks_marks AS dtm ON (dtm.user_id=" . $user['user_id'] . " AND dtm.date_task_id=date_tasks.date_task_id) ";
	$sql = $sql . "WHERE date_tasks.date_tasks_mission_id=" . $mission . " ";
	$sql = $sql . "GROUP BY date_tasks.ord ";		
	$query = mysql_query($sql);
	$tasks = mysql_fetch_column($query);
	
	$title = "Tasks";
	include("includes/header.php"); 
	include("includes/slider.php");
	include("includes/scroll.php");
	?>


	<body class="blue">

		<div id="wrapper">
			
			<div id="header">
				<?php include("includes/topbar.php"); ?>
			</div>
				
			<div id="main">
				
				<div id="page_title">
					<?=T_("Tasks")?>
				</div>
					
				<div class="three_column padding_top">
					
					<div class="content">
						
						<div id="slider">
						
							<ul class="tasks">
							
								<li>
									<div class="slider_title">
										<?=$mission_data['subject_name'].' ';?><?=(isset($mission_data['mission_number'])? '-Mission #'.$mission_data['mission_number']:' ')?> <?=' '.$mission_name?>
									</div>
										
									<div class="scroll-pane">
										
										<div class="boxes mainbox mission_icon">
											
											<div class="title">
												Mission Tasks
											</div>
												
											<?php
												foreach ($tasks as $task) {
													
													if ($task['optional_qty'] > 0 && $task['quantity'] == NULL)
														continue;
																	
													if ($current_date < $mission_data['start_date'])
														$futureMission = true;
													else
														$futureMission = false;

													$showDescription = true;
													if ($task['label_name'] == "said in Shul" || $task['label_name'] == "started on time" || $task['label_name'] == "Minutes" || $task['label_name'] == "Bonus" || $task['label_name'] == "heard every word") 
														$showDescription = false;
																								
													$mark_points = (!$task['mark_points'])?0:$task['mark_points'];
												
													$taskCompleted = true;
													if ($task['mark_quantity'] < $task['quantity']) {
														$taskCompleted = false;
														$mark_points = ($task['mark_quantity'] / $task['quantity']) * $task['points'];
														$mark_points = round($mark_points, 2);
													}
												
												
													if($task['quantity'])
														$quantity = $task['quantity'];
													elseif($task['done_qty'])
														$quantity = $task['done_qty'];
													else
														$quantity = $task['mark_quantity'];
											?>
												
											<div class="question" style='background:url(<?='/file_view.php?id=' . $task['label_image_id'];?>) no-repeat;'>
												
												<p><?=$task['name'];?></p>
											
											<?											
											if ($quantity != NULL && $quantity > 1 && $futureMission == false) { 
												echo "<div class='mission_quota'>";
												echo "Quota (Ladder " . $mission_data['track_id'] .  "):" . $quantity . " " . $task['label_name'];
												echo "</div>";
											}
												
											if (isset($task['mark_date']) && $taskCompleted == true && $futureMission == false)
												echo "<div class='check_on'></div>";
											else
												echo "<div class='check_off'></div>";												
											
											echo "<div class='mission_complete'>";
											
											if ($futureMission == false) {
												if ($task['label_name'] == "") {
													echo "<div class='mission_complete'>" . $mark_points .  " " . T_("Miles") . "</div>";
												}
												else {												
													if ($task['mark_quantity'] > 0) 
														echo $task['mark_quantity'] . " " . $task['label_name'] . " - " . $mark_points . " " . T_("Miles");
													else 
														echo "0 " . $task['label_name'] . " - " . $mark_points . " " . T_("Miles");													
												}
											}
											echo "</div> ";
												
											if ($task['description'] && $showDescription == true && $futureMission == false) 
												echo "(<span>" . $task['description'] . "</span>)";

												
											echo "<div class='clear'></div>";
											
											echo "</div>";
											

										}
										?>
										
									</div>
									
									<div class="boxes mainbox bonus_icon">
									
										<div class="title">Bonus Tasks</div>
										<?php
										foreach ($tasks as $task)
										{
											if ($task['mandatory_qty'] > 0 || $task['quantity'] != NULL) 
												continue;
													
											$showDescription = true;
											if ($task['label_name'] == "said in Shul" || $task['label_name'] == "started on time" || $task['label_name'] == "Minutes" || $task['label_name'] == "Bonus" || $task['label_name'] == "heard every word") 
												$showDescription = false;
													
											if ($current_date < $mission_data['start_date'])
												$futureMission = true;
											else
												$futureMission = false;
													
											$mark_points = (!$task['mark_points'])?0:$task['mark_points'];
												
											if ($task['quantity'])
												$quantity = $task['quantity'];
											elseif($task['done_qty'])
												$quantity = $task['done_qty'];
											else
												$quantity = $task['mark_quantity'];
											?>
												
											<div class="question" style='background:url(<?='/file_view.php?id=' . $task['label_image_id']; ?>) no-repeat;'>
												
											<p><?=$task['name'];?></p>
											
											<?											
											
											if ($quantity != NULL && $quantity > 1) { 
												echo "<div class='mission_quota'>";
												echo "Quota (Ladder " . $mission_data['track_id'] . "):"; 
												echo "</div>";
											}
												
											if (isset($task['mark_date']))
												echo "<div class='check_on'></div>";
											else
												echo "<div class='check_off'></div>";
											
											if ($futureMission == false)
												echo "<div class='mission_complete'>" . $mark_points .  " " . T_("Miles") . "</div>";

											if ($task['description'] && $showDescription == true && $futureMission == false) 
												echo "(<span>" . $task['description'] . "</span>)";
											
											echo "<div class='clear'></div>";
											echo "</div>";
										}
									?>
										</div>
										
									</div> 
									
								</li>
								
							</ul>
							
						</div>
						
					</div>
					
				</div>
				
			</div>
		
			<div id="footer">
				<?php include("includes/bottombar.php"); ?>
			</div>
		  
		</div>
		
	</body>
	
	<?php include("includes/footer.php"); ?>
	<?php
	}
else
{
    header('Location: prof_medal.php');
    exit("<script>window.location = 'prof_medal.php';</script>");
}
?>