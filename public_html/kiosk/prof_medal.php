<?php 
require_once ("../header.php");
require_once('../file_save.php');
require_once('../calendar.php');

// ********** USER **********//
$user_row = mysql_fetch_assoc(mq("
	SELECT user_id, first, last, dob, dob_he_offset, first_he, last_he, username, gender, user_address1, user_address2,
		   user_city, user_state, user_postal, user_country, user_phone,
		   user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id, team_name, 
		   school_name, school_number, school_city, school_state, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, school_id,
		   rank_name, rank_image_id, rank_color 
	FROM users
		 LEFT JOIN schools USING (school_id)
		 LEFT JOIN institutions USING (inst_id)
		 LEFT JOIN classes USING (school_id, class_id)
		 LEFT JOIN teams USING (school_id, team_id)
		 LEFT JOIN (SELECT user_id, MAX(rank_ord) rank_ord FROM rank_marks WHERE user_id = {$user['user_id']} GROUP BY user_id) rank USING (user_id)
		 LEFT JOIN ranks USING (rank_ord)
	WHERE user_id = {$user['user_id']}
	ORDER BY class_grade, class_sub, last, first
	"));
// ********** USER **********//

$user_id = $user['user_id'];

$subject_id = $_POST['subject_id'];
$medal_ord = $_POST['medal_ord'];
$first_medal = $_POST['first_medal'];
$last_medal = $_POST['last_medal'];

$previous_medal = 0;
if ($medal_ord > 1)
	$previous_medal = $medal_ord - 1;

$first_mission = 0;
$for_missions = 0;

// ********** SUBJECT **********//
$sql = "SELECT * FROM subjects WHERE subject_id=" . $subject_id;
$subject = mysql_fetch_assoc(mysql_query($sql));
// ********** SUBJECT **********//

// ********** MEDAL ********** //
$sql = "SELECT m.medal_name, ms.missions_required, ms.profile_photo_id FROM medals AS m JOIN medals_subjects AS ms USING (medal_ord) WHERE m.medal_ord=" . $medal_ord . " AND ms.subject_id=" . $subject_id;
$medal = mysql_fetch_assoc(mysql_query($sql));
// ********** MEDAL ********** //

$sql = "SELECT * FROM medals_subjects WHERE subject_id=" . $subject_id;
$rows = mysql_query($sql);
while ($row = mysql_fetch_assoc($rows)) {

	if ($medal_ord == 1) {
		$first_mission = 0;
		$for_missions = $row['missions_required'];
		break;
	}
	else {
		if ($medal_ord > $row['medal_ord']) {
			$first_mission += $row['missions_required'];			
		}
		else {
			$for_missions = $row['missions_required'];
			break;
		}
	}
}

$sql  = "SELECT * ";
$sql .= "FROM date_tasks_mission_marks AS dtmm ";
$sql .= "LEFT JOIN date_tasks_missions AS dtm ON (dtm.date_tasks_mission_id=dtmm.date_tasks_mission_id) ";
$sql .= "WHERE dtmm.user_id=" . $user_id . " ";
$sql .= "AND dtmm.subject_id=" . $subject_id . " ";
$sql .= "ORDER BY dtm.start_date ASC ";
$sql .= "LIMIT " . intval($first_mission) . ", " . intval($for_missions);
echo "<input type='hidden' name='SQL' value='" . $sql . "'>";
$missions = mysql_query($sql);
										
$current_date = unixtojd();
$mission_number = $first_mission + 1;

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
			
				<?php //include("includes/topbar.php"); ?>
				
				<div class="nav">
					<ul>
						<!--<li class="icon_back"><a href="#" onclick="javascript:history.back(); return false">Back</a></li>-->
						<li class="icon_home"><a href="../statement.php">Home</a></li>
						<li class="icon_logout"><a href="../logout.php?n=kiosk.php">Logout</a></li>
					</ul>
				</div>
				
				<div class="org_photo">
					<?=(!is_null($user_row['school_logo_id']) ? linkImgFile($user_row['school_logo_id'],100,100) : '');?>
				</div>
				
				Base: #<?=$user_row['school_number']?><br>
				<?=$user_row['school_name']?><br>
				<?=$user_row['rank_name'].' '.$user_row['first'].' '.$user_row['last']?>
				
			</div>		
			
		</div>
				
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
									<?=$subject['subject_name'] . ' - ' . $medal['medal_name'];?>
								</div>
										
								<div class="mission_side">
									<div class="medalImage" style='background: transparent url(<?='/file_view.php?id=' . $medal['profile_photo_id']; ?>);'>
										<span class="badge"><?=(int)$medal['missions_required'];?></span>
									</div>
									<a id="button_up">Up</a>
									<a id="button_dn">Down</a>
								</div>
										
								<div id="slider_inside" class="mission_boxes">
										
									<div id="missions_container">
											
											
<?php
while ($mission = mysql_fetch_assoc($missions)) { 
?>

	<? if ($mission['date_tasks_mission_id'] == null) { ?>
		<div class="mission">
			<div class='number'><?=$mission_number;?></div>
			<div class='date'>Mission not accessible</div>
			<div class='meter' style='background-position:0% 0;'></div>
			<div class='check_on'></div>
		</div>
	<? } else { ?>
		<? $hebrew_start_date = dateToHebrewSplit($mission['start_date']); ?>
		
			<div class="mission">
				<a href="prof_medal_mission.php?mission=<?=$mission['date_tasks_mission_id'];?>">		
					<div class='number'><?=$mission_number;?></div>
					<div class='date'><?=$mission['mission_name'];?></div>
					<div class='date'><?=$hebrew_start_date[2];?></div>
					<div class='meter' style='background-position:0% 0;'></div>
				</a>
				<div class='check_on'></div>
			</div>
		<? } ?>
	<? $mission_number++; ?>
	
	
<?	
} 
?>
											
										
											<? if ($medal_ord > $first_medal) { ?>
											<div class="mission button_back">
												<form name="previous_medal" method="post" action="prof_medal.php">
													<input type="hidden" name="subject_id" id="subject_id" value="<?=$subject_id;?>">
													<input type="hidden" name="medal_ord" id="medal_ord" value="<?=$previous_medal;?>">
													<input type="hidden" name="first_medal" id="first_medal" value="<?=$first_medal;?>">
													<input type="hidden" name="last_medal" id="last_medal" value="<?=$last_medal;?>">													
													<a href="#" onClick="document.previous_medal.submit();">Previous Medal</a>
												</form>
											</div>
											<? } ?>
											
											<? if ($medal_ord < $last_medal) { ?>
											<div class="mission button_back">
												<form name="netx_medal" method="post" action="prof_medal.php">												
													<input type="hidden" name="subject_id" id="subject_id" value="<?=$subject_id;?>">
													<input type="hidden" name="medal_ord" id="medal_ord" value="<?=($medal_ord + 1);?>">
													<input type="hidden" name="first_medal" id="first_medal" value="<?=$first_medal;?>">
													<input type="hidden" name="last_medal" id="last_medal" value="<?=$last_medal;?>">																										
													<a href="#" onClick="document.netx_medal.submit();">Next<br />Medal</a>
												</form>
											</div>
											<? } ?>

											 <div class="mission button_back">
												<form name="back_to_medals" method="post" action="prof_medals.php">
													<input type="hidden" name="subject_id" id="subject_id" value="<?=$subject_id;?>">
													<a href="#" onClick="document.back_to_medals.submit();">Back to Medals</a>
												</form>
											</div>
																						
										</div> <!-- missions_container -->
											
									</div> <!-- mission_boxes -->
										
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
	
		<input type="hidden" name="completed_counter" value="<?=$completed_counter;?>">
		<input type="hidden" name="incomplete_counter" value="<?=$incomplete_counter;?>">
		
	</body>

</html>

