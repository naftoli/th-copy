<?php 
require_once("../header.php");
require_once("../file_save.php");
require_once('../calendar.php');

require_once("../classes/user.php");
require_once("../classes/subject.php");
require_once("../classes/medal.php");
require_once("../classes/medal_subject.php");
//require_once("../classes/user_track.php");
require_once("../classes/date_tasks_mission.php");

$school_start_date = beginning_of_hebrew_year();

// ***** USER ***** //
$sql = "SELECT * FROM users WHERE user_id=" . $user['user_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$user = new user($row);
$user->get_school();
// ***** USER ***** //

// ***** SUBJECT ***** //
$subject_id = $_POST['subject_id'];
$sql = "SELECT * FROM subjects WHERE subject_id=" . $subject_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$subject = new subject($row);
// ***** SUBJECT ***** //

$medal_ord = $_POST['medal_ord'];
echo "<input type='hidden' name='1) MEDAL ORD' value='" . $medal_ord . "'>\n";

// ***** MISSIONS REQUIRED ***** //
//$missions_required = 0;
$previous_missions_required = 0;
$starting_mission = 1;
$sql = "SELECT * FROM medals_subjects WHERE subject_id=" . $subject_id . " AND medal_ord <= " . $medal_ord;
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query))
{
	$missions_required = $row['missions_required'];
	$starting_mission = $starting_mission + $previous_missions_required;
	$previous_missions_required = $row['missions_required'];
}
//echo "<input type='hidden' name='MISSIONS REQUIRED' value='" . $missions_required . "'>\n";
echo "<input type='hidden' name='2) STARTING MISSION' value='" . $starting_mission . "'>\n";
// ***** MISSIONS REQUIRED ***** //

// ***** MEDAL ***** //
$sql = "SELECT * FROM medals WHERE medal_ord=" . $medal_ord;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$medal = new medal($row);
$medal->get_medal_subject($subject_id);
echo "<input type='hidden' name='3) MISSIONS REQUIRED' value='" . $medal->medal_subject->missions_required . "'>\n\n";
// ***** MEDAL ***** //

// ***** FIRST MEDAL ***** //
$first_medal = $_POST['first_medal'];
//echo "<input type='hidden' name='FIRST MEDAL' value='" . $first_medal . "'>\n";

// ***** LAST MEDAL ***** //
$last_medal = $_POST['last_medal'];
//echo "<input type='hidden' name='LAST MEDAL' value='" . $last_medal . "'>\n";

// ***** START DATE ***** //
$sql = "SELECT dtm.date_tasks_mission_id, dtm.start_date ";
$sql .= "FROM date_tasks_mission_marks AS dtmm ";
$sql .= "JOIN date_tasks_missions AS dtm ON (dtmm.date_tasks_mission_id=dtm.date_tasks_mission_id) ";
$sql .= "WHERE dtmm.user_id=" . $user->user_id . "  ";
$sql .= "AND dtmm.subject_id=" . $subject_id . " "; 
$sql .= "ORDER BY dtm.start_date ASC ";
$sql .= "LIMIT 1";
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$start_date = $row['start_date'];
$date_tasks_mission_id = $row['date_tasks_mission_id'];
//echo "<input type='hidden' name='DATE TASKS MISSION ID AND START DATE' value='" . $date_tasks_mission_id . ":" . $start_date . "'>\n";
// ***** START DATE ***** //

// ***** COMPLETED MISSIONS ***** //
$completed_missions = array();
$sql = "SELECT dtm.* ";
$sql .= "FROM date_tasks_mission_marks AS dtmm ";
$sql .= "JOIN date_tasks_missions AS dtm USING (date_tasks_mission_id) ";
$sql .= "WHERE dtmm.user_id=" . $user->user_id . " ";
$sql .= "AND dtmm.subject_id=" . $subject_id . " ";
$sql .= "ORDER BY dtm.start_date ";
$query = mysql_query($sql);
$row_num = 0;
while ($row = mysql_fetch_assoc($query))
{
	$row_num++;
	
	echo "<input type='hidden' name='ROW NUM' value='" . $row_num . "'>\n";
	echo "<input type='hidden' name='STARTING MISSION' value='" . $starting_mission . "'>\n";
	
	if ($row_num >= $starting_mission)
	{
		$date_tasks_mission = new date_tasks_mission($row);
		array_push($completed_missions, $date_tasks_mission);
	}
	//else
	//{
	//	break;
	//}
}

echo "<input type='hidden' name='# OF COMPLETED MISSIONS' value='" . count($completed_missions) . "'>\n\n";

// ***** USER TRACK ***** //
//$sql = "SELECT * FROM user_tracks WHERE user_id=" . $user->user_id . " AND subject_id=" . $subject_id;
//$query = mysql_query($sql);
//$row = mysql_fetch_assoc($query);
//$user_track = new user_track($row);
//$user_track->fetch_date_tasks_missions($user->school_type_id, $start_date);
// ***** USER TRACK ***** //




$mission_number = 0;

$title = "Missions";
include("includes/header.php");
?>
	
	<script>
		$(document).ready(function() {
			var itemHeight = 88;
			var itemCol = 7
			var currentTop = 0;
			var containerHeight = Math.ceil($("#slider_inside > div > div").length / itemCol) * itemHeight;
					
			$("a#button_up").click(function () {
						
				if (Math.abs(currentTop) > 0) {
					$("#slider_inside > div").animate({"top":currentTop + itemHeight},{queue:false});
					currentTop += itemHeight;
				} 
				else {
					$("#slider_inside > div").animate({"top":currentTop + (itemHeight/2)},"fast").animate({"top":currentTop},"fast");
				}
						
			});
					
			$("a#button_dn").click(function () {
						
				if (Math.abs(currentTop) < (containerHeight - itemHeight)) {
					$("#slider_inside > div").stop().animate({"top":currentTop - itemHeight},{queue:false});
					currentTop -= itemHeight;
				} 
				else {
					$("#slider_inside > div").stop().animate({"top":currentTop - (itemHeight/2)},"fast").animate({"top":currentTop},"fast");
				}
						
			});	
					
		 });
	</script>
	
		
	<body class="blue">

		<div id="wrapper">
			
			<div id="header">
		
				<div class="org">
							
					<div class="nav">
						<ul>
							<li class="icon_home"><a href="../statement.php">Home</a></li>
							<li class="icon_logout"><a href="../logout.php?n=kiosk.php">Logout</a></li>
						</ul>
					</div>
				
					<div class="org_photo">
						<? if (!is_null($user->school->school_logo_id)) : ?>
							<? linkImgFile($user->school->school_logo_id,100,100); ?>
						<? endif; ?>
					</div>
				
					Base: #<?=$user->school->school_number;?>
					<br>
					<?=$user->school->school_name;?>
					<br>
					<?=$user->rank_name . " "  . $user->first . " " . $user->last;?>
				
				</div>		
			
			</div>
			<!-- header -->
			
			<div id="main">
				
				<div id="page_title">
					Missions
				</div>			
					
				<div class="three_column padding_top">
					
					<div class="content">
							
						<div class="slider_box">
						
							<ul class="missions">					
								
								<li>
								
									<div class="slider_title">
										<?=$subject->subject_name . " - " . $medal->medal_name;?>
									</div>
								
									<div class="mission_side">
										<div class="medalImage" style='background: transparent url(<?='/file_view.php?id=' . $medal->medal_subject->profile_photo_id; ?>);'>
											<span class="badge"><?=(int)$medal->medal_subject->missions_required;?></span>
										</div>
										<a id="button_up">Up</a>
										<a id="button_dn">Down</a>
									</div>
								
									<div id="slider_inside" class="mission_boxes">
									
										<div id="missions_container">
										
											<? foreach ($completed_missions as $completed_mission) : ?>
												<? $mission_number++; ?>
												<div class="mission">
													<a href="prof_medal_mission.php?mission=<?=$completed_mission->date_tasks_mission_id;?>">
														<div class="number">#<?=$mission_number;?></div>
														<div class="date"><?=$completed_mission->mission_name;?></div>
														<? $hebrew_start_date = dateToHebrewSplit($completed_mission->start_date); ?>
														<div class="date"><?=$hebrew_start_date[2];?></div>
														<div class="meter" style='background-position:0% 0;'></div>	
													</a>
													<div class='check_on'></div>
												</div>
											<? endforeach; ?>
											
											<? if ($medal_ord > $first_medal) : ?>
											<div class="mission button_back">
												<form name="previous_medal" method="post" action="prof_medal_two.php">
													<input type="hidden" name="subject_id" id="subject_id" value="<?=$subject_id;?>">
													<input type="hidden" name="medal_ord" id="medal_ord" value="<?=($medal_ord - 1);?>">
													<input type="hidden" name="first_medal" id="first_medal" value="<?=$first_medal;?>">
													<input type="hidden" name="last_medal" id="last_medal" value="<?=$last_medal;?>">													
													<a href="#" onClick="document.previous_medal.submit();">Previous Medal</a>
												</form>
											</div>
											<? endif; ?>
											
											<? if ($medal_ord < $last_medal) : ?>
											<div class="mission button_back">
												<form name="next_medal" method="post" action="prof_medal_two.php">												
													<input type="hidden" name="subject_id" id="subject_id" value="<?=$subject_id;?>">
													<input type="hidden" name="medal_ord" id="medal_ord" value="<?=($medal_ord + 1);?>">
													<input type="hidden" name="first_medal" id="first_medal" value="<?=$first_medal;?>">
													<input type="hidden" name="last_medal" id="last_medal" value="<?=$last_medal;?>">																										
													<a href="#" onClick="document.next_medal.submit();">Next<br />Medal</a>
												</form>
											</div>
											<? endif; ?>
											
											
											<div class="mission button_back">
												<form name="back_to_medals" method="post" action="prof_medals.php">
													<input type="hidden" name="subject_id" id="subject_id" value="<?=$subject_id;?>">
													<a href="#" onClick="document.back_to_medals.submit();">Back to Medals</a>
												</form>
											</div>											
											
										</div> <!-- missions_container -->
										
									</div> <!-- <div id="slider_inside" class="mission_boxes"> -->
									
								</li>
								
							</ul>
							
						</div> <!-- slider_box -->
							
					</div> <!-- content -->
					
				</div> <!-- three_column padding_top -->
					
			</div> <!-- main -->
				
			<div id="footer">
				<div class="footer_logo"></div>
				<div class="footer_logout"></div>
			</div> <!-- footer -->
			
		</div> <!-- wrapper -->
	
	</body>

</html>
