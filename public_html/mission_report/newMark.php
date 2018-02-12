<?
require 'getUsers.php'; 
require 'includes.php'; 
//$admin_auth = array('school','user');
 
if (isset($_GET['user_id']) && $_GET['user_id'] == -1) {
	echo "you must choose a student. please go back and try again.";
	exit;
}
/*
include("classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
if ($admin->auth != "super") {
    $admin->get_schools();
    if (count($admin->schools) == 1) {
    	//print_r($admin->schools);
        $school_id = $admin->schools[0]->school_id;
    }
}

require 'getUsers.php'; 
require 'includes.php'; 

if (isset($_POST['action'])) {
	
	if (!isset($school_id)) {
		$school_id = 0;
	}
	if (!isset($class_id)) {
		$class_id = 0;
	}
	
    get_classes_select($school_id, $class_id);
    get_users_select($school_id, $class_id, $user_id);
	            
    if ($action == "produce_report") {
		//$showDate = $_POST['showDate'];
		//$dblSided = $_POST['dblSided'];
		header("Location: mission_report/newMark.php?user_id=$user_id&school_id=$school_id&class_id=$class_id&start_date=$start_date&end_date=$end_date");
		exit;
	} 
}

function get_users_select($school_id, $class_id, $user_id) {
    global $users_select;
    
    $sql = "SELECT u.user_id, u.first, u.last, u.class_id, c.class_grade, c.class_sub ";
    $sql = $sql . "FROM users AS u ";
    $sql = $sql . "JOIN classes AS c USING (class_id) ";
    $sql = $sql . "WHERE u.school_id=" . $school_id . " and u.user_registered > 0 ";
    if ($class_id > 0)
        $sql = $sql . "AND class_id=" . $class_id . " ";
    $sql = $sql . "ORDER BY u.class_id, u.last, u.first";
    //echo $sql;
    $query = mysql_query($sql); 
    
    $users_select = "<div class='user_list select_box'>";
    $users_select = $users_select . "<a class='prev button'>";
    $users_select = $users_select . "<span class='icon'></span><span class='label'>Previous Student</span>";
    $users_select = $users_select . "</a>";
    $users_select = $users_select . "<select name='user_id' id='user_id' class='sSelect'>";
    $users_select = $users_select . "<option value='-1'>All students</option>";
        
    while ($row = mysql_fetch_assoc($query)) {
        $grade = $row['class_grade'];
        if ($row['class_sub'] != "")
            $grade = $grade . "-" . $row['class_sub'];
            
        if ($user_id == $row['user_id'])
            $users_select = $users_select . "<option selected value='" . $row['user_id'] . "'>" . $grade . " " . $row['first'] . " " . $row['last'] . "</option>";
        else
            $users_select = $users_select . "<option value='" . $row['user_id'] . "'>" . $grade . " " . $row['first'] . " " . $row['last'] . "</option>";       
    }

    $users_select = $users_select . "</select>";
    $users_select = $users_select . "<a class='next button'>";
    $users_select = $users_select . "<span class='icon'></span><span class='label'>Next Student</span>";
    $users_select = $users_select . "</a>";
    $users_select = $users_select . "</div>";
}

function get_classes_select($school_id, $class_id) {
    global $classes_select;
    
    $sql = "SELECT * FROM classes WHERE school_id=" . $school_id . " and class_era = 0 order by class_grade, class_sub";
    $query = mysql_query($sql);
    
    $classes_select = "<div class='class_list select_box'>";
    $classes_select = $classes_select . "<a class='prev button'>";
    $classes_select = $classes_select . "<span class='icon'></span>";
    $classes_select = $classes_select . "<span class='label'>Previous Platoon</span>";
    $classes_select = $classes_select . "</a>";
    $classes_select = $classes_select . "<select name='class_id'>";
    $classes_select = $classes_select . "<option>Choose a Platoon</option>";
    
    while ($row = mysql_fetch_assoc($query)) {      
        if ($class_id == $row['class_id']) 
            $classes_select = $classes_select . "<option selected value='" . $row['class_id'] . "'>" . $row['class_grade'] . "-" . $row['class_sub'] . "</option>";
        else
            $classes_select = $classes_select . "<option value='" . $row['class_id'] . "'>" . $row['class_grade'] . "-" . $row['class_sub'] . "</option>";
    }
    
    $classes_select = $classes_select . "</select>";
    $classes_select = $classes_select . "<a class='next button'>";
    $classes_select = $classes_select . "<span class='icon'></span>";
    $classes_select = $classes_select . "<span class='label'>Next Platoon</span>";
    $classes_select = $classes_select . "</a>";
    $classes_select = $classes_select . "</div>";
}


// ***** SCHOOLS ***** //
if ($admin->auth == "super") {
    $schools_sql = "SELECT school_id, school_name FROM schools ORDER BY school_name";
    $schools_query = mysql_query($schools_sql);
}
elseif (count($admin->schools) > 0) 
{
    $schools_sql = "SELECT s.school_id, s.school_name FROM schools AS s JOIN admin_auths AS aa ON (aa.admin_id=" . $admin->admin_id . " AND aa.auth='school' AND aa.id=s.school_id) ORDER BY school_name";
    $schools_query = mysql_query($schools_sql);
}
// ***** SCHOOLS ***** //

if ($school_id > 0) 
{
    // ***** REPORT DATES ***** //
    include("classes/report.php");
    $reports = array();
    $sql = "SELECT * FROM reports WHERE report_type='mission_cover_sheet' AND visibility != 'none' and start_date > $start ORDER BY start_date";    
    $query = mysql_query($sql);
    while ($row = mysql_fetch_assoc($query)) {
        $report = new report($row);
        //hide pesach 2014
        if ($report->start_date == 2456761 || $report->start_date == 2456768) continue;
		//if ($report->start_date >= 2456927 && $admin_user['auth'] != 'super') continue;
        array_push($reports, $report);
    }
    // ***** REPORT DATES ***** //
}
*/
//echo "<pre>"; print_r($user); echo "</pre>"; exit;
function createFooter() {
	return; 
	?>
	<div class="footer">
		<img class="footerImg" src="image/missionInstructions.gif" width="100%" alt=""/>
		<strong>
			<div class="boxer">
				<div class="task cell">
					<div class="box">
					  <p><b>1.</b> A sticker near a task means it’s a mission needed to earn your medals.</p>
					</div>
					<div class="box">
						<p><b>2.</b> Daily missions are completed when the task is done five out of seven times a week.</p> 
					</div>
					<div class="box">
						<p><b>3.</b> A <img src="image/31204.png" width="30" height="8" alt=""/> icon near a task means you earn a charge card for completing the mission.</p> 
					</div>
					<div class="box">
						<p><b>4.</b> A “quota” means your assigned goal, decided upon with your commanders.</p>
					</div>
					<div class="box"><p><b>5.</b> If circumstances prevent you from completing a task, speak to your Base Commander.</p></div>
				</div>
			</div>
		</strong>
	</div><!-- .footer -->
<? } ?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Missions</title>
		<link rel="stylesheet" href="newStyle.css?v1.3" type="text/css" />
	</head>
	
	<body>
		<!--
		<div id="marking">
			<div id="school"></div>
			<div id="grade"></div>
			<div id="student"></div>
			<div id="parsha"></div>
		</div>
		<div style="clear: both"></div>
		-->
		<?
		$totalUsers = count($users);
		foreach ($users as $key => $user) {
			$numLabels = count($user->daily_labels) + count($user->weekly_labels) + count($user->shabbos_labels) + count($user->no_label_subjects);
			$tracks = $user->user_tracks;
			$numTasks = 0;
			$types = array('daily_tasks', 'weekly_tasks', 'shabbos_tasks', 'no_label_tasks');
			foreach ($tracks as $track) {
				foreach ($types as $type) {
					$numTasks += count($track->$type);
				}
			}
			$totalRows = (floor($numLabels / 2)) + $numTasks;
			?> 
			
			<div class="firstContainer">
				<div class="header">
					<div class="userImg">
						<? if (isset($user->user_photo_id)) : ?>
							<img src="../file_view2.php?id=<?=$user->user_photo_id?>" width="60" alt=""/>
						<? else : ?>
							<img src="" width="60" alt=""/> 
						<? endif; ?>
					</div>
					<?
					$sql = "select school_number from schools where school_id = " . $user->school_class->school_id;
					$result = mysql_query($sql);
					$row = mysql_fetch_assoc($result);
					?>
					<div class="schoolLogo">
						<img src="schools/<?=$row['school_number']?>.png" width="80" alt=""/>
					</div>
			    	<table>
			    		<tr>
			    			<td class="title">
								<span class="sb">Platoon:</span> 
								<?=$user->school_class->class_grade;?>-<?=$user->school_class->class_sub;?><br>
								<span class="sb">Commander:</span><br>
								<?=$user->school_class->class_teacher;?>
			    			</td>
			    			<td>
			    				<div class="headerImg">
			    					<img src="image/mission report logo.png" width="350" />
			    				</div>
			    			</td>
			    			<td class="title">
								<?=$user->rank_name?><br />
								<span class="b"><?=$user->first . ' ' . $user->last?></span>
			    			</td>
			    		</tr>
			    		<tr> 
			    			<td colspan="3" class="line">
							<? 
							if ($showDate > 0) {
								if ($showDate == 1) { 
									?>
									<span class="hebrew-text">פרשת <?=$parsha?> &#10022; <?=$heDates[0]?> - <?=$heDates[6]?></span>
								<? } else if ($showDate == 2) { ?>
									<b><?=date('M j', (jdtounix($start_date+1))) . ' - ' . date('M j, Y', (jdtounix($end_date+1)))?></b>
									<span class="hebrew-text"> &#10022; פרשת <?=$parsha?> &#10022; <?=$heDates[0]?> - <?=$heDates[6]?></span>
								<? } ?>
								</span></td>
							<? } else { ?>
								<span class="hebrew-text">פרשת <?=$parsha?></span>
							<? } ?>
							</td>
			    		</tr>
				  </table>
				</div>
				
				<div align="center" style="padding-bottom: 10px;">
					<button id="checkAll">Check All</button>
					<button id="uncheckAll">Uncheck All</button>
				</div>
				
				<div class="left">
					<?
					$totalRendered = 0;
					$page = 1;
					$labelAdded = 0;
					$firstColumn = true;
					
					$numDaily = count($user->daily_labels);
					if ($numDaily) {
						foreach ($user->sorted_daily_labels as $key0 => $value) {
							for ($dlno = 0; $dlno < $numDaily; $dlno++) {
								$key1 = $user->daily_labels[$dlno]; 
					            $info = explode(":", $key1); 
					            $label = $info[0]; 
					            if ($value == $user->daily_labels[$dlno]) {
									$labelAdded++;
								?>
							 		<div class="label"><?=$label?></div>
									<? 
									$numTasks = count($user->daily_tasks);
									for ($dtno = 0; $dtno < $numTasks; $dtno++) {
										if ($user->daily_tasks[$dtno]->label_name == $label) {
											$daily_task = $user->daily_tasks[$dtno]; 
											?>
								            <div class="row">
												<div class="rowImg">
													<img src="campaignLogos/<?=$campaignLogos[$daily_task->subject_id]?>" width="50" height="52" alt=""/>
												</div>
												<? if ($daily_task->focus_task) { ?>
													<div class="focus">
														<img src="image/31204.png" width="50" height="12" alt="" />
													</div>
												<? } ?>
												<div class="short"><?=($daily_task->short_name == '' ? '<br />' : $daily_task->short_name)?></div>
												<div class="task"><?=$daily_task->task_name?></div>
										    </div>
										    
										    <?
										    if ($daily_task->mandatory_qty) {
										    	echo "<div class='mandatoryImg";
												if ($firstColumn) 
													echo " firstColumn";
												else 
													echo " secondColumn";
										    	echo "'><img src=\"5of7stickers/" . $dailyStickers[$daily_task->subject_id] . "\" /></div>";
										    }
										    ?>
										    
										    <div style="float: left; margin-left: -20px; margin-top: 14px;">
										    	<input type="checkbox" class="dailyRow" />
										    </div>
										    <div class="dailyBoxes">
												<table>
													<tr>
														<? 
														foreach ($days_of_week as $index => $day) { 
															$mark = $daily_task->date_task_marks[$index];
															$identifier = $daily_task->date_task_id . ':' . $mark->mark_date;
															$marked = false;
															if ($mark->marked) {
																$marked = true;
															}
															?>
														  	<td>
																<div class="checkboxDaily <?=$marked ? 'marked' : 'unmarked'?>" id="<?=$identifier?>">
															 		<? 
															 		if ($marked) 
															 			echo "<span class='checkmark'>&#10004;</span>";
															 		?>
															 	</div>
															</td>
														<? } ?>
													</tr>
												</table>
											 </div>
											 
											 <div style="clear: both"></div>
											<?
											$totalRendered++;
											$addLabel = floor($labelAdded / 2);
											//echo $totalRendered;
											$split = pager($page, $totalRendered, $totalRows, $addLabel); 
											if ($split == 2) {
												echo "</div>";
												
												if ($page == 1)
													createFooter();
												else {
													/*
													?>
													<div class="pageFooter">
														<div class="userName"><?=$user->first . ' ' . $user->last?></div>
														<div class="pageNum">Page <?=$page?></div>
														<div class="parsha">פרשת <?=$parsha?></div>
													</div>
												<?
												*/ 
												}
												$page++;
												$totalRendered = 0;
												$labelAdded = 0;
												?>
												</div>
												<div style="clear: both"></div>
												<hr />
												
												<div class="container">
												<div class="left">
												<?
											}
											else if ($split == 1) {
												$totalRendered += floor($labelAdded / 2);
												$labelAdded = 0;
												$firstColumn = false;
												?>
												</div><!-- .left-sidebar -->
												<div class="right">
												<?
											}
										}
									}
								}
							}
						}
					}
					
					$numWeekly = count($user->weekly_labels);
					if ($numWeekly) {
						foreach ($user->sorted_weekly_labels as $label_name1) {
							for ($lno = 0; $lno < $numWeekly; $lno++) {
								if ($label_name1 == $user->weekly_labels[$lno]) {
									$labelAdded++;
									?>
							 		<div class="label"><?=$label_name1?></div>
							 		<?
							 		$numTasks = count($user->weekly_tasks);
							 		for ($wtno = 0; $wtno < $numTasks; $wtno++) {
							 			$label_name = $user->weekly_tasks[$wtno]->label_name;
										if ($label_name == $user->weekly_labels[$lno]) {
											$weekly_task = $user->weekly_tasks[$wtno];
											?>
											<div class="row">
												<div class='mandatoryImg'>&nbsp;
												<?
											    if ($weekly_task->mandatory_qty) {
											    	echo "<img src='stickerOutlines/" . $stickerOutlines[$weekly_task->subject_id] . "' />";
											    } else {
											    	echo "<img src='' alt='' />";
											    }
											    ?>
											    </div>
											    <?
											    $class = '';
												$mark = $weekly_task->date_task_mark;
												$identifier = $weekly_task->date_task_id . ':' . $weekly_task->mark_date;
												$marked = false;
												if ($mark->marked) {
													$marked = true;
													$class = "marked";
												} else {
													$class = "unmarked";
												}
												$input = false;
												if (!is_null($weekly_task->quantity)) {
													$input = true;
													$class .= " textInput";
												}
												?>
												<div class="checkbox <?=$class?>" id="<?=$identifier?>">
													<?
													if ($input) {
														echo "<input value='" . $mark->done_qty . "' type='text' " . 
															"onkeypress='return number_validation(event);' size='1' maxlength='4' />";
													} else {
														if ($marked) 
												 			echo "<span class='checkmark'>&#10004;</span>";
													}
													?>
												</div>
												<div class="rowImg">
													<img src="campaignLogos/<?=$campaignLogos[$weekly_task->subject_id]?>" width="50" height="52" alt=""/>
												</div>
												<div class="short"><?=($weekly_task->short_name == '' ? '<br />' : $weekly_task->short_name)?>
												<? if ($weekly_task->focus_task) { ?>
													<div class="focus">
														<img src="image/31204.png" width="30" height="8" alt="" />
													</div>
												<? } ?>
												</div>
												<div class="task"><?=$weekly_task->task_name?></div>
											</div>
											<div style="clear: both"></div>
										    <?
										    $totalRendered++;
											$addLabel = floor($labelAdded / 2);
											//echo $totalRendered + $addLabel;
											$split = pager($page, $totalRendered, $totalRows, $addLabel); 
											if ($split == 2) {
												echo "</div>";
												if ($page == 1)
													createFooter();
												else {
													/*
													?>
													<div class="pageFooter">
														<div class="userName"><?=$user->first . ' ' . $user->last?></div>
														<div class="pageNum">Page <?=$page?></div>
														<div class="parsha">פרשת <?=$parsha?></div>
													</div>
												<?
												*/
												}
												$page++;
												$totalRendered = 0;
												$labelAdded = 0;
												?>
											</div>
											<div style="clear: both"></div>
											<hr />
											
											<div class="container">
											<div class="left">
											<?
											}
											else if ($split == 1) {
												$totalRendered += floor($labelAdded / 2);
												$labelAdded = 0;
												?>
												</div><!-- .left-sidebar -->
												<div class="right">
												<?
											}
										}
							 		}
								}
							}
						}
					}
					
					$numShabbos = count($user->shabbos_labels);
					if ($numShabbos) {
						foreach ($user->sorted_shabbos_labels as $label_name1) {
							for ($lno = 0; $lno < $numShabbos; $lno++) {
								if ($label_name1 == $user->shabbos_labels[$lno]) {
									$labelAdded++;
									?>
							 		<div class="label"><?=$label_name1?></div>
							 		<?
							 		$numTasks = count($user->shabbos_tasks);
							 		for ($stno = 0; $stno < $numTasks; $stno++) {
							 			$label_name = $user->shabbos_tasks[$stno]->label_name;
										if ($label_name == $user->shabbos_labels[$lno]) {
											$shabbos_task = $user->shabbos_tasks[$stno];
											?>
											<div class="row">
												<div class='mandatoryImg'>&nbsp;
												<?
											    if ($shabbos_task->mandatory_qty) {
											    	echo "<img src='stickerOutlines/" . $stickerOutlines[$shabbos_task->subject_id] . "' />";
											    } else {
											    	echo "<img src='' alt='' />";
											    }
											    ?>
											    </div>
												<?
											    $class = '';
												$mark = $shabbos_task->date_task_mark;
												$identifier = $shabbos_task->date_task_id . ':' . $shabbos_task->mark_date;
												$marked = false;
												if ($mark->marked) {
													$marked = true;
													$class = "marked";
												} else {
													$class = "unmarked";
												}
												$input = false;
												if (!is_null($shabbos_task->quantity)) {
													$input = true;
													$class .= " textInput";
												}
												?>
												<div class="checkbox <?=$class?>" id="<?=$identifier?>">
													<?
													if ($input) {
														echo "<input value='" . $mark->done_qty . "' type='text' " . 
															"onkeypress='return number_validation(event);' size='1' maxlength='4' />";
													} else {
														if ($marked) 
												 			echo "<span class='checkmark'>&#10004;</span>";
													}
													?>
												</div>
												<div class="rowImg">
													<img src="campaignLogos/<?=$campaignLogos[$shabbos_task->subject_id]?>" width="50" height="52" alt=""/>
												</div>
												<div class="short"><?=($shabbos_task->short_name == '' ? '<br />' : $shabbos_task->short_name)?>
												<? if ($shabbos_task->focus_task) { ?>
													<div class="focus">
														<img src="image/31204.png" width="30" height="8" alt="" />
													</div>
												<? } ?>
												</div>
												<div class="task">
													<?
													if ($label_name == 'Shabbos Mevorchim' && $shabbos_task->mandatory_qty > 0) 
					                                    echo "<i>My quota for this Shabbos Mevorchim is $shabbos_task->desc kapitelach.</i><br />";
					                                if ($label_name == 'Shabbos Mevorchim' && $shabbos_task->mandatory_qty == 0 && $shabbos_task->quantity > 0)
					                                    echo "<i>My quota for this Shabbos Mevorchim is $shabbos_task->desc minutes.</i><br/>";
													?>
													<?=$shabbos_task->task_name?>
												</div>
											</div>
											<div style="clear: both"></div>
											<?
											$totalRendered++;
											$addLabel = floor($labelAdded / 2);
											//echo $totalRendered;
											$split = pager($page, $totalRendered, $totalRows, $addLabel);  
											if ($split == 2) {
												echo "</div>";
												if ($page == 1)
													createFooter();
												else {
													/*
													?>
													<div class="pageFooter">
														<div class="userName"><?=$user->first . ' ' . $user->last?></div>
														<div class="pageNum">Page <?=$page?></div>
														<div class="parsha">פרשת <?=$parsha?></div>
													</div>
												<?
												*/
												}
												$page++;
												$totalRendered = 0;
												$labelAdded = 0;
												?>
											</div>
											<div style="clear: both"></div>
											<hr />
											
											<div class="container">
											<div class="left">
											<?
											}
											else if ($split == 1) {
												$totalRendered += floor($labelAdded / 2);
												$labelAdded = 0;
												?>
												</div><!-- .left-sidebar -->
												<div class="right">
												<?
											}						
										}
							 		}
								}
							}
						}
					}

					$numYD = count($user->no_label_subjects);
					if ($numYD) {
						for ($nlno = 0; $nlno < $numYD; $nlno++) {
							$key1 = $user->no_label_subjects[$nlno];
					        $info = explode(":", $key1); 
					        $subject_name = $info[0]; 
					        $mission_name = $info[1];
							$labelAdded++;
							?>
					 		<div class="label"><?=$subject_name;?> - <?=$mission_name;?></div>
					 		<?
							$numTasks = count($user->no_label_tasks);
							for ($nltno = 0; $nltno < $numTasks; $nltno++) {
								$no_label_task = $user->no_label_tasks[$nltno];         
					            $subject_name = $no_label_task->subject_name;
					            $mission_name = $no_label_task->mission_name;
					            $key2 = $subject_name . ":" . $mission_name;
								if ($key1 == $key2) {				
									?>
									<div class="row">
										<div class='mandatoryImg'>&nbsp;
										<?
									    if ($no_label_task->mandatory_qty) {
									    	echo "<img src='stickerOutlines/" . $stickerOutlines[$no_label_task->subject_id] . "' />";
									    } else {
									    	echo "<img src='' alt='' />";
									    }
									    ?>
									    </div>
										<?
									    $class = '';
										$mark = $no_label_task->date_task_mark;
										$identifier = $no_label_task->date_task_id . ':' . $no_label_task->mark_date;
										$marked = false;
										if ($mark->marked) {
											$marked = true;
											$class = "marked";
										} else {
											$class = "unmarked";
										}
										$input = false;
										if (!is_null($no_label_task->quantity)) {
											$input = true;
											$class .= " textInput";
										}
										?>
										<div class="checkbox <?=$class?>" id="<?=$identifier?>">
											<?
											if ($input) {
												echo "<input value='" . $mark->done_qty . "' type='text' " . 
													"onkeypress='return number_validation(event);' size='1' maxlength='4' />";
											} else {
												if ($marked) 
										 			echo "<span class='checkmark'>&#10004;</span>";
											}
											?>
										</div>
										<div class="rowImg">
											<img src="campaignLogos/<?=$campaignLogos[$no_label_task->subject_id]?>" width="50" height="52" alt=""/>
										</div>
										<div class="short"><?=($no_label_task->short_name == '' ? '<br />' : $no_label_task->short_name)?>
										<? if ($no_label_task->focus_task) { ?>
											<div class="focus">
												<img src="image/31204.png" width="30" height="8" alt="" />
											</div>
										<? } ?>
										</div>
										<div class="task"><?=$no_label_task->task_name?></div>
									</div>
									<div style="clear: both"></div>
									<?
									$totalRendered++;
									$addLabel = floor($labelAdded / 2);
									//echo $totalRendered;
									$split = pager($page, $totalRendered, $totalRows, $addLabel);
									if ($split == 2) {
										echo "</div>";
										if ($page == 1)
											createFooter();
										else {
											/*
											?>
											<div class="pageFooter">
												<div class="userName"><?=$user->first . ' ' . $user->last?></div>
												<div class="pageNum">Page <?=$page?></div>
												<div class="parsha">פרשת <?=$parsha?></div>
											</div>
										<?
										*/
										}
										$page++;
										$totalRendered = 0;
										$labelAdded = 0;
										?>
									</div>
									<div style="clear: both"></div>
									<hr />			
									
									<div class="container">
									<div class="left">
									<?
									}
									else if ($split == 1) {
										$totalRendered += floor($labelAdded / 2);
										$labelAdded = 0;
										?>
										</div><!-- .left-sidebar -->
										<div class="right">
										<?
									}
								}
					 		}
						}
					}
				?>
				</div>
				
				<div id="<?=$user->user_id?>" class="bottomFooter" align="center">
					<input type="hidden" class="pages" value="<?=$page?>" />
					<img class="rankLogo" src="image/rank report logo.png" />
					<img class="rank" src="../rankPics/<?=$user->rank_ord;?>BW.png" height="70" />
					<table class="footerInfo">
						<tr style="vertical-align: bottom">
							<td align="left" class="border"><span class="sb"><?=$user->first . ' ' . $user->last?></span></td>
							<td width="48%" class="i review">
								&#10004; I reviewed my child's progress as a chayol in Hashem's army.
							</td>
						</tr>
						<tr>
							<td align="left"><span class="i"><?=$user->getRankInfo(true)?></span></td>
							<td><span class="i">Parent's Signature</span></td>
						</tr>
					</table> 
					<div style="clear: both"></div>
				</div> 
			</div>
			<? 
			if (($key + 1) != $totalUsers) {
				echo "<div style='clear: both'></div>";
				echo "<div style='page-break-after: always'></div>";
				if ($dblSided && $page % 2 != 0) {
					echo "<div style='page-break-after: always'>&nbsp;</div>";
				}
			}
		} 
		?>
	</body>
    <script src="../scripts/functions.js"></script>
	<script src="../jquery.js"></script>
	<script>
		$(function() {
			var user_id = <?=$user->user_id?>;
			
			$(".checkboxDaily").click( function() {
				var info = $(this).attr('id');
				var split = info.indexOf(':');
				var task = info.substring(0,split);
				var date = info.substring(++split);
				var url = '';
				var div = this;
				
				if ($(div).hasClass('marked')) { 
					url = "../delete_functions.php?function_name=delete_daily_task_mark";
				} else if ($(div).hasClass('unmarked')) {
					url = "../add_functions.php?function_name=add_daily_task_mark";
				}
				
				var parameters = [user_id, task, date];
				url += "&parameters=" + parameters;
				
				$.getJSON(url, function(success) {
					if (success == false) {
						alert("Update not performed. Please try again.");
					} else {
						update(div);
					}
				});
			});
			
			$(".checkbox").click( function() {
				var info = $(this).attr('id');
				var split = info.indexOf(':');
				var task = info.substring(0,split);
				var date = info.substring(++split);
				var url = '';
				var div = this;
				
				if ($(this).hasClass('textInput')) {
					var val = $(this).find('input').val();
					if (val == '') {
						val = 0;
					}
					if (val > 0) {
		                var parameters = [user_id, task, date, val];
		                url = "../add_functions.php?function_name=add_mark&parameters=" + parameters;
		                $.getJSON(url, function(success) {
							if (success != 1) {
								alert("Update not performed. Please try again.");
							} else {
								update(div);
							}
						});
					}
				} else {
					if ($(div).hasClass('marked')) { 
						url = "../delete_functions.php?function_name=delete_task_mark";
					} else if ($(div).hasClass('unmarked')) {
						url = "../add_functions.php?function_name=add_task_mark";
					}
					var parameters = [user_id, task, date];
					url += "&parameters=" + parameters;
					
					$.getJSON(url, function(success) {
						if (success == false) {
							alert("Update not performed. Please try again.");
						} else {
							update(div);
						}
					});
				}
			});
			
			function update(div) {
				if ($(div).hasClass('marked')) {
					$(div).removeClass('marked');
					$(div).addClass('unmarked');
					if (!$(div).hasClass('textInput')) {
						$(div).empty();
					}
				} else if ($(div).hasClass('unmarked')) {
					$(div).removeClass('unmarked');
					$(div).addClass('marked');
					if (!$(div).hasClass('textInput')) {
						$(div).html("<span class='checkmark'>&#10004;</span>");
					}
				}
			}
			
			$("#checkAll").click( function() {
				var tasks = '';
				var dates = '';
				var boxes = [$(".checkboxDaily"),$(".checkbox")];
				
				$(boxes).each( function(i, e) {
					$(e).each( function() {
						var info = $(this).attr('id');
						var split = info.indexOf(':');
						var task = info.substring(0,split);
						var date = info.substring(++split);
						
						if ($(this).hasClass('unmarked') && !$(this).hasClass('textInput')) {
							tasks += task + ':';
							dates += date + ':';
						}
					})
				});
				
				tasks = tasks.substring(0, tasks.length - 1);
				dates = dates.substring(0, dates.length - 1);
								
				var parameters = [user_id, tasks, dates];
				var url = "../add_functions.php?function_name=add_date_tasks_marks";
				url += "&parameters=" + parameters;
				
				$.getJSON(url, function(success) {  
                    if (success == false) {
                        alert("Update not performed.");
                    } else {
                    	$(boxes).each( function(i, e) {
							$(e).each( function() {
	                    		if ($(this).hasClass('unmarked') && !$(this).hasClass('textInput')) {
	                    			$(this).removeClass('unmarked');
									$(this).addClass('marked');
	                    			$(this).html("<span class='checkmark'>&#10004;</span>");
	                    		}
	                    	});
	                    });
                    }
                });
			});
			
			$("#uncheckAll").click( function() {
				var tasks = '';
				var dates = '';
				var boxes = [$(".checkboxDaily"),$(".checkbox")];
				
				$(boxes).each( function(i, e) {
					$(e).each( function() {
						var info = $(this).attr('id');
						var split = info.indexOf(':');
						var task = info.substring(0,split);
						var date = info.substring(++split);
						
						if ($(this).hasClass('marked') && !$(this).hasClass('textInput')) {
							tasks += task + ':';
							dates += date + ':';
						}
					})
				});
				
				tasks = tasks.substring(0, tasks.length - 1);
				dates = dates.substring(0, dates.length - 1);
								
				var parameters = [user_id, tasks, dates];
				var url = "../delete_functions.php?function_name=delete_date_tasks_marks";
				url += "&parameters=" + parameters;
				
				$.getJSON(url, function(success) {  
                    if (success == false) {
                        alert("Update not performed.");
                    } else {
                    	$(boxes).each( function(i, e) {
							$(e).each( function() { 
	                    		if ($(this).hasClass('marked') && !$(this).hasClass('textInput')) {
	                    			$(this).removeClass('marked');
									$(this).addClass('unmarked');
	                    			$(this).empty();
	                    		}
	                   		});
                    	});
                    }
                });
			});
			
			$(".dailyRow").click( function() {
				var checked = $(this).is(":checked");
				var tasks = '';
				var dates = '';
				var boxes = $(this).parent().next('.dailyBoxes').find('.checkboxDaily');
				
				$(boxes).each( function() {
					var info = $(this).attr('id');
					var split = info.indexOf(':');
					var task = info.substring(0,split);
					var date = info.substring(++split);
					
					if (checked) {
						if ($(this).hasClass('unmarked')) {
							tasks += task + ':';
							dates += date + ':';
						}
					} else {
						if ($(this).hasClass('marked')) {
							tasks += task + ':';
							dates += date + ':';
						}
					}
				});
				
				tasks = tasks.substring(0, tasks.length - 1);
				dates = dates.substring(0, dates.length - 1);
								
				var parameters = [user_id, tasks, dates];
				if (checked) {
					var url = "../add_functions.php?function_name=add_date_tasks_marks";
					url += "&parameters=" + parameters;
				} else {
					var url = "../delete_functions.php?function_name=delete_date_tasks_marks";
					url += "&parameters=" + parameters;
				}
				
				$.getJSON(url, function(success) {  
                    if (success == false) {
                        alert("Update not performed.");
                    } else {
                    	$(boxes).each( function() {
                    		if (checked) {
	                    		if ($(this).hasClass('unmarked')) {
	                    			$(this).removeClass('unmarked');
									$(this).addClass('marked');
	                    			$(this).html("<span class='checkmark'>&#10004;</span>");
	                    		}
	                    	} else {
	                    		if ($(this).hasClass('marked')) {
	                    			$(this).removeClass('marked');
									$(this).addClass('unmarked');
	                    			$(this).empty();
	                    		}
	                    	}
	                    });
                    }
                });
			});
			
			<? foreach ($users as $user) { ?>			
			var user_id = <?=$user->user_id?>;			
	    	var image = 'All';
	    	
			$.ajax({
	            url: '../ajax/getMissionInfo.php', 
	            async: false, 
	            data: {user_id : user_id, type : image}, 
	            success: function(data, textStatus, jqXHR) {
	                data = $.parseJSON(data);
	                
	                var stickers = {
	            		1	: 'Shabbos Mevorchim Tehillim.gif', 
						4	: 'Tefillah.gif',
						12	: 'Mivtzoim.gif',
						13	: 'Niggunim.gif',
						16	: 'Sticker - Hiskashrus outline.png', 
						21	: 'sefer hamitzvos bw.png',
						27	: 'Tanya.gif',
						40	: 'Yomei Dipagra.gif',
						41	: 'Avos Ubonim.gif',
						42	: 'Vihalachta Bidrachov.gif',
						45	: 'Cheshbon Hanefesh.gif',
						90	: 'Chitas.gif',
						100	: 'Sticker - Brias Haguf_outline bw.png'
	            	}
	            	
	            	var str = "<div class='finalFooter'>";
	                $.each(data, function(i, val) { 
	                    str += "<span class='footer_info'>";
	                    var j = 0;
	                    var s = stickers;
	                    $.each(val, function(indx, value) {
	                        //build footer info
	                        if (j++ == 0) { //first get sticker info
	                            str += "<img src='stickerOutlines/" + s[i] + "' /><br /><b>" + indx + "</b><br />";
	                        } else { //then get medal info
	                            str += "<i>" + value + " to " + indx + "</i>";
	                        }
	                    });
	                    str += "</span>"; 
	                });
	                str += "</div>";
	                $("#" + user_id).append(str);
	            }
	        });
	    	<? } ?>
	    	//window.print();
		});
	</script>
</html>