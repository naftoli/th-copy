<?php
//echo "We are working on some upgrades. Please check again later. Sorry for the inconvenience.";
//exit;
$days_of_week = array("F", "ש", "S", "M", "T", "W", "T");
$mobileURL = "/".explode("/", $_SERVER[REQUEST_URI])[1]."/"; // get if we are in /mobile/ or /mobileDev/
$daily = isset($_GET['daily']) && $_GET['daily']  == "true" ? true : false; // Get if we should render the daily view or not
$desktop = isset($_GET['desktop']) && $_GET['desktop']  == "true" ? true : false; // Get if we are on a desktop or not
if($desktop) $daily = false; // only show the weekly view on the desktop...
/********************** LOGOS FOR CAMPAIGNS **********************/
$campaignLogos = array(
	1	=>	'Tehillim.gif',
	4	=>	'Tefilla.gif',
	12	=>	'Mivtzoim.gif',
	13	=>	'Niggunim.gif',
	16	=>	'hiskashrus.gif',
	21	=>	'sefer-hamitzvos.gif',
	27	=>	'tanya.gif',
	40	=>	'Yom-Dipagra.gif',
	41	=>	'Father-Son.gif',
	42	=>	'Footsteps.gif',
	45	=>	'Cheshbon-Hanefesh.gif',
	90	=>	'Chitas.gif',
	100	=>	'Brias-Haguf.gif'
);
/********************** STICKERS **********************/
$stickerOutlines = array(
	1	=>	'Shabbos Mevorchim Tehillim.gif', 
	4	=>	'Tefillah.gif',
	12	=>	'Mivtzoim.gif',
	13	=>	'Niggunim.gif',
	16	=>	'Sticker - Hiskashrus outline.png', 
	21	=>	'sefer hamitzvos bw.png',
	27	=>	'Tanya.gif',
	40	=>	'Yomei Dipagra.gif',
	41	=>	'Avos Ubonim.gif',
	42	=>	'Vihalachta Bidrachov.gif',
	45	=>	'Cheshbon Hanefesh.gif',
	90	=>	'Chitas.gif',
	100	=>	'Sticker - Brias Haguf_outline bw.png'
);
/********************** STICKERS FOR DAILY MISSION GOALS **********************/
$dailyStickers = array(
	1	=>	'tehillim 5 of 7.png', 
	4	=>	'tefilah 5 of 7.png',
	12	=>	'mivtzoyim 5 of 7.png',
	13	=>	'niggunim 5 of 7.png',
	16	=>	'hiskashrus 5 of 7.png', 
	21	=>	'sefer hamitzvos 5 of 7.png',
	27	=>	'tanya 5 of 7.png',
	40	=>	'yoma dipagra 5 of 7.png',
	41	=>	'avos ubanim 5 of 7.png',
	42	=>	'halachta bdrachav5 of 7.png',
	45	=>	'cheshbon hanefesh 5 of 7.png',
	90	=>	'chitas 5 of 7.png',
	100	=>	'brias haguf 5 of 7.png'
);
/********************** LOAD UP THE DATABASE CONNECTION **********************/
require_once '../db.php';
$user_id = mysql_real_escape_string( $_GET['id'] );

/********************** GET THE CURRENT PARSHA **********************/
$curParsha = array();
if (!isset($_GET['d'])) { // if the date was not provided
	//get todays day
	$jd = floor(unixtojd());
	$today = intval(date('w', jdtounix($jd))); //sunday starts 0
	switch ($today) {
		case 0:
		case 1:
		case 2:
		case 3:
		case 4:
			$diff = $today + 2; // add two days to the current date if before thursday
			break;
		case 5: // thursday is the end date so no diff
			$diff = 0;
			break;
		case 6: // friday is the first day so the difference is one
			$diff = 1;
			break;
	}
	$start = $jd - $diff; // the start is the current date minus the difference
	$end = $start + 6; // the ending is the start date plus 6
	// check if we need to change start / end
	// there seems to be some type of discrepancy between the unixtojd and date('w') return values
	$sql = "SELECT * FROM parshos WHERE start = " . $start;
	$result = mysql_query($sql);
	if (mysql_num_rows($result) == 0) {
		$start++;
		$end++;
	}
	$curParsha['end'] = $end;
} else { // if the date was provided by the user
	$jd = intval($_GET['d']);
	$jd = $jd < unixtojd() ? $jd : unixtojd(); // make sure that they cannot go to far back into the future
	$today = intval(date('w', jdtounix($jd+1)));
	if (isset($_GET['s']) && $_GET['s'] == 1) {
		$start = $jd;
		$end = $start + 6;
	} else {
		$end = $jd;
		$start = $end - 6;
	}
//echo $start . "-" . $end; exit;
}
/********************** CREATE AN ARRAY OF THE HEBREW DATES **********************/
$temp = $start;

do {
	$he = iconv('WINDOWS-1255', 'UTF-8', jdtojewish($temp, true, CAL_JEWISH_ADD_GERESHAYIM));
	$heArr = explode(' ', $he);
	$heDates[] = $heArr[0] . ' ' . $heArr[1];
	$heDatesDisp[] = $heArr[0];
} while (++$temp <= $end);

/********************** INCLUDE DEPENDANCIES **********************/
include("../classes/user.php");
include("../classes/user_track.php");
include("../classes/school_class.php");
include("../class.taskExceptions.php");
include("../classes/date_tasks_mission.php");
include("../classes/daily_task.php");
include("../classes/weekly_task.php");
include("../classes/shabbos_task.php");
include("../classes/no_label_task.php");
include("../classes/task.php");
include("../classes/date_tasks_mark.php");

/********************** LOAD THE USER **********************/
$lang = 1; // default language is english
$sql = "SELECT * FROM users WHERE user_id = " . $user_id; // get the user
$query = mysql_query($sql); // run the query
$row = mysql_fetch_assoc($query); // and get the user

$lang = $row['lang_id']; // update the language
$user = new user($row); // create a new user
$user->get_rank(); // get his rank
$user->get_school_class(); // and get his class
chdir('../'); // move up a directory
$user->get_user_tracks( -1, $start, $end, array(), $user->lang_id ); // get the users tracks
chdir('mobile'); // and come back to this folder

/********************** GET THE PARSHA **********************/
$sql = "select name from parshos where start = " . $start . " and end = " . $end;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$hWeek = $row['name'];
//echo "<pre>"; print_r($user); echo "</pre>"; exit;
if (isset($_GET['app'])) define('HOME', 'mission_report');
else define('HOME', '../mission_report');

/********************** GET ALL THE PARSHIOS **********************/
$parshos = array();
if (isset($_GET['d'])) {
	$jdTemp = floor(unixtojd());
	$today = intval(date('w', jdtounix($jdTemp))); //sunday starts 0
	switch ($today) {
		case 0:
		case 1:
		case 2:
		case 3:
		case 4:
			$diff = $today + 2;
			break;
		case 5:
			$diff = 0;
			break;
		case 6:
			$diff = 1;
			break;
	}
	$curParsha['start'] = $jdTemp - $diff;
	$curParsha['end'] = $curParsha['start'] + 6;
	
	// check if we need to change start / end
	// there seems to be some type of discrepancy between the unixtojd and date('w') return values
	// looks like unixtojd works off server timezone which is EST but date function works off UTC
	$sql = "select * from parshos where start = " . $curParsha['start'];
	$result = mysql_query($sql);
	if (mysql_num_rows($result) == 0) {
		$curParsha['start']++;
		$curParsha['end']++;
	}
}
// only allow parents to go back 2 weeks
$sql = "select * from parshos where end <= " . $curParsha['end'] . " order by end desc limit 3";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$parshos[$row['end']] = $row['name'];
	$lastParsha = $row['end'];
}
ksort($parshos); // sort the parshios by their ending date

/********************** Check if the user is on mobile **********************/
require_once '../Mobile_Detect.php';
$detect = new Mobile_Detect;

/********************** LOAD UP THE MISHNA INFO **********************/
require '../class.mishnaInfo.php';
$sql = "select school_id, class_id from users where user_id = " . $user_id; // get the school and class id from the users table for the given user
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$school = $row['school_id'];
$grade = $row['class_id'];
$assigned = MishnaInfo::getAssignedAll( $school, $grade, $user_id, true );
$he_chars = array(
	1	=>	'א',	2	=>	'ב',	3	=>	'ג',		4	=>	'ד',		5	=>	'ה',
	6	=>	'ו',		7	=>	'ז',		8	=>	'ח',	9	=>	'ט',	10	=>	'י',
	11	=>	'יא',	12	=>	'יב',	13	=>	'יג',	14	=>	'יד',	15	=>	'טו',
	16	=>	'טז',	17	=>	'יז',	18	=>	'יח',	19	=>	'יט',	20	=>	'כ',
	21	=>	'כא',	22	=>	'כב',	23	=>	'כג',	24	=>	'כד',	25	=>	'כה',
	26	=>	'כו',	27	=>	'כז',	28	=>	'כח',	29	=>	'כט',	30	=>	'ל'
);
/********************** LOAD UP THE SLIDING BAR ON THE TOP OF THE PAGE **********************/
?>
<header class="navbar" id="top" role="banner">
    <div class="container">
        <div class="navbar-header">
            <div class="slick-slider">
				<? if($daily) {
					for ($from = 1; $from < 8; $from++) {
						//get hebrew date
						$timestamp = jdtounix($start + $from);
						$jdate = substr(jdtojewish(($start + ($from-1)), true, CAL_JEWISH_ADD_GERESHAYIM),0,-7);
						$jdate = iconv ('windows-1255', 'utf-8', $jdate);
						?>
						<div class="item" data-date="<?=date('y-m-d', $timestamp)?>">
							<div class="parsha"><span class="hebrew"><?=$hWeek?></span></div>
							<div class="day">
								<?
								$d = date('l',$timestamp-1);
								//if (date('w', $timestamp) == 6) $d = "shabbos";
								if ($d == 'Saturday') $d = "Shabbos";
								echo $d;
								?>
							</div>
							<div class="date"><?=date('F j',$timestamp-1)?> / <span class="hebrew"><?=$jdate?></span></div>
						</div>
						<?
						// if it's friday we need to also load shabbos so that the swiper works
						if (floor(unixtojd($timestamp)) >= floor(unixtojd()) && $from > 1) { 
							break;
						}
					} 
				} else {
					// the current date
					$timestamp = time();
					$jdate = substr(jdtojewish(unixtojd(), true, CAL_JEWISH_ADD_GERESHAYIM),0,-7);
					$jdate = iconv ('windows-1255', 'utf-8', $jdate);
					// jewish start and end dates
					$j_start_date = substr(jdtojewish($start, true, CAL_JEWISH_ADD_GERESHAYIM),0,-7);
					$j_start_date = iconv ('windows-1255', 'utf-8', $j_start_date);
					$j_end_date = substr(jdtojewish($start + 6, true, CAL_JEWISH_ADD_GERESHAYIM),0,-7);
					$j_end_date = iconv ('windows-1255', 'utf-8', $j_end_date);
					
					$start_date = date('M j', jdtounix($start));
					$end_date = date('M j', jdtounix($start + 6));
					?>
					<div class="item" data-date="<?=date('y-m-d', $timestamp)?>">
						<div class="date"><?//$lang == 1 ? date('F j',$timestamp-1) : $jdate?></div>
						<div class="day" style="margin-bottom: 10px;"> 
							<span class="hebrew"><!--פרשת --><?=$hWeek?></span>
						</div>
						<div class="date"><?=$start_date?> - <?=$end_date?> / <span class="hebrew"><?=$j_start_date?> - <?=$j_end_date?></span></div>
					</div>
					<div id="navigation-buttons">
						<? // create the parsha locator for the links
						$parsha_locator = [];
						foreach($parshos as $date => $parsha) {
							$parsha_locator[$parsha] = $date;
						}
						if(!(array_values($parshos)[0] == $hWeek)) {?>
							<span id="previous" class="parsha-navigator" data-d="<?=$parsha_locator[$hWeek] - 7?>">
								<img src="img_new/arrow-1-color-white-svg.svg" /> <?=$parshos[$parsha_locator[$hWeek] - 7]?>
							</span>
						<?}
						if(!(end($parshos) == $hWeek)) {
							$next_week = $parsha_locator[$hWeek] + 7?>
							<span id="next" class="parsha-navigator" data-d="<?=$parshos[$next_week] != end($parshos) ? $next_week : ""?>">
								<?=$parshos[$next_week]?> <img src="img_new/arrow-1-color-white-svg.svg" />
							</span>
						<? } ?>
					</div>
				<? }?>
            </div>
        </div>
    </div>
</header>

<div class="personalImg"></div> <? //div to load the users image into ?>
<div class="bug-report">
	<img src="/mobile/img_new/report-bug-white-svg.svg" data-user_id="<?=$user_id?>" data-category="Marking Missions" alt="bug-report" />
</div>

<div class="container">
    <div class="content">
		<? /********************** BUTTONS ON THE TOP OF THE PAGE **********************/ ?>
    	<div style="margin-bottom: 20px;">
			<? $alignmentRight = $lang == 2 ? "left" : "right"; // swich right to left for hebrew ?>
			<? $alignmentLeft  = $lang == 2 ? "right" : "left"; // swich left to right for hebrew ?>
			<div id="buttons" style="text-align: center;">
				<div id="rightButtons" style="float: <?=$alignmentRight?>; text-align:<?=$alignmentRight?>;">
					<?require_once 'reg/ajax/encrypt.php';?>
					<input type="button" class="showProgress btn btn-danger btn-sm" value="Weekly View" style="<?=$desktop ? "display: none" : ""?>" />
					<a id="printLink" href="https://mashpia.com/mission_report/newParentPrint.php?bypass=1&admin=<?=encrypt_decrypt('decrypt', $_COOKIE['admin'])?>" target="_blank" style="<?=$desktop ? "" : "display: none"?>">
						<input type="button" class="btn btn-danger btn-sm" value="Print Missions" />
					</a>
				</div>
				<div id="leftButtons" style="float: <?=$alignmentLeft?>; text-align:<?=$alignmentLeft?>;">
					<a id="goalsLink" href="" style="float: <?=$alignmentLeft?>">
						<input type="button" class="btn btn-danger btn-sm" value="Personalize" />
					</a>
				</div>
				<button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#myModal" style="margin: 0 5px;">
					Help
				</button>
			</div>
			<? /********************** DATE DROPDOWN. TODO: REMOVE AND MOVE TO SLIDING BAR ON TOP **********************/ ?>
			
			<div style="clear:both"></div>
		</div>

		<?// for every day of the week
		for ($from = 1; $from < 8; $from++) {
			if(!$daily){
				$timestamp = time(); // set the date to todays date....
				$from = 8; // only do this once
			} else {
				$timestamp = jdtounix($start + $from);
			}?>
			<div class="tasks-day" data-date="<?=date('y-m-d', $timestamp)?>">
			<?
			if (count($user->daily_labels) > 0) { // if the user has dialy tasks
				foreach ($user->sorted_daily_labels as $index => $value) { // for each of the users sorted daily labels...
					$info = explode(":", $value); // split up the info
					$label = $info[0]; // and get the label
					
					/*************** GENERATE A PANEL FOR EACH OF THE DAILY TASKS ****************/?>
					<div class="panel panel-default">
						<div class="panel-heading">
							<i class="glyphicon glyphicon-chevron-left"></i> <?=$label?>
						</div>
						<div class="collapse in" id="#panel_<?=$index?>">
							<div class="panel-body dailyPanel">
							<div class="text-<?= $lang == 2 ? "left" : "right"; // move based on language?>">
								<input type="button" class="checkAll<?=!$daily ? "Daily" : ""; // change the class if we are rendering the whole week.?> btn btn-danger btn-xs" value="Check All" style="background-color : #5e1c77;border-color:#834999; <?//$desktop ? "" : "display: none"; ?>"/>
							</div>
							<br />
							<?//if ($lang == 2) echo '<br />'; // extra space for hebrew ?>
							<ul class="list-unstyled">
								<? /************ RENDER EACH TASK ***************/
								$numDaily = count($user->daily_tasks); // count the tasks
								for ($j = 0; $j < $numDaily; $j++) { // for each task
									if ($user->daily_tasks[$j]->label_name == $label) { // make sure that the label fits the label that we are showing.....
										$daily_task = $user->daily_tasks[$j]; // get the daily task
										$date_task_mark = $daily_task->date_task_marks[$from-1]; // and get the mark for todays date <= does not work if the mission starts later in the week...
										
										// if the total count of the missions is less then 7 (does not cover the full week) AND the first one does not start at the beginning of the week....
										if($daily && count($daily_task->date_task_marks) < 7 && $daily_task->date_task_marks[0]->mark_date != $start){ // if there are less then 7 date_task_marks and the first one is not the first date....
											foreach($daily_task->date_task_marks as $task){ // go through each task
												if($task->mark_date == $start + ($from -1 )){ // if the mark date is the date we are generating....
													$date_task_mark = $task; break;
												} else {
													$date_task_mark = false;
												}
											}
										}
										// change the $checked variable based on if the task is marked
										$checked = $date_task_mark->marked == true;
										if($daily && !$date_task_mark) continue; // if we are generating the daily view and there is no mark for today (weekly would be false if it is not available on the last day of the week)
										?>
										<li class="task daily">
											<div class="row">
												<div class="rowImg"> 
													<img src="<?=HOME?>/campaignLogos/<?=$campaignLogos[$daily_task->subject_id]?>" width="50" height="52" alt=""/>
												</div>
												<? if (!empty($daily_task->medium_pic)) { ?>
												<div class="mediumPic">
													<img src="<?=HOME?>/color/<?=$daily_task->medium_pic?>.jpg" />
												</div>
												<? } // end if we should show medium_pic?>
												<label class="checkbox" <?= !$daily ? "style='padding-right:0px;'": "";?> >
													<?if ($daily) {?>
														<div class="actions">
															<input type="checkbox" class="box-check daily <? if ($checked) echo "pre-checked" ?>" 
																value="<?=$date_task_mark->date_task_id;?>:<?=$date_task_mark->mark_date;?>" 
																<? if ($checked) echo "checked" ?> />
															<span class="check"></span>
															<span class="box"></span>
														</div>
													<? } // end daily checkbox ?>
													<? if ($daily_task->focus_task) { ?>
														<div class="focus">
															<img src="images/31204.png" alt="" />
														</div>
													<? } ?>
													<div class="short" style="<?=!$daily ? "max-width: 100%;" : ""?>">
														<? if ($daily_task->mandatory_qty) echo "<span class='mandStar'>*</span>"; ?>
														<?=($daily_task->short_name == '' ? '<br />' : $daily_task->short_name)?>
													</div>
													<div class="long" <?= !$daily ? "style='max-width:100%;'": "";?>>
														<?=$daily_task->task_name?>
													</div>
												</label>
												<? if(!$daily) {// find out the marks dates to know if this task is only on specific dates
													$dates = array(); // all the dates in the week
													foreach ($daily_task->date_task_marks as $mark) { // full the dates array with the mark dates
														$dates[] = $mark->mark_date;									     
													}
													/****** WEEKLY CHECKBOX OPTIONS: TODO MOVE TO WEEKLY RENDER *******/?>
													<div class="dailyBoxes">
														<table>
															<tr>
															<?foreach ($days_of_week as $index => $day) { // go through the days of the week
																if (in_array(($start + $index), $dates)) { // if the date is available
																	$k = array_search($start + $index, $dates); // get the key
																	$until = $jd-$start; // get the until
																	$task_mark = $daily_task->date_task_marks[$k]; // get the date_task_mark or the day
																	$done = $task_mark->marked == true; // mark if it was done
																	?>
																	<td>
																		<div class="checkboxDaily" style="border: none">
																			<div style="color: grey;">
																				<span class='dMark'><?
																				if ($index <= $until) { // if it is before today
																					$id = $task_mark->date_task_id . ':' . $task_mark->mark_date . ($done ? ':1' : ':0');?>
																					<span class='dMarkID' id='<?=$id?>'></span>
																					<span class='<?= $done ? "checked" : "unchecked" ;?>'></span>
																				<? } else { // it is in the future ?>
																					<span class='unchecked' style='padding-top: 2px;'>
																					<?=$user->lang_id == 1 ? $days_of_week[$index] : $heDatesDisp[$index] ; ?>
																					</span>
																				<? }?>
																				</span>
																			</div>
																		</div>
																	</td>
																<?} else { // if it is unavailable render the unavailable checkbox?>
																	<td>
																		<div class="checkboxDaily">
																			<span class="unavilable-box"></span>
																		</div>
																	</td>
																<?} // end if the mission is not available on that date....
															}?>
															</tr>
														</table>
														<? if ($daily_task->mandatory_qty) { // if it is mandatory get the stickers info
															echo "<div class='mandatoryImg'><img src=\"" . HOME . "/5of7stickers/" . $dailyStickers[$daily_task->subject_id] . "\" /></div>";
														}?>
													</div>
													<div class="dailyBoxesLine"></div>
												<? } // end if daily or not ?>
											</div>
										</li>
									<?
									}
								}
								?>
							</ul>
						</div>
					</div>
				</div>
				<?
			}
		}        
		?>
		
		<?
		if (count($user->weekly_labels) > 0) {
			$i = 1;
			foreach ($user->sorted_weekly_labels as $value) {
				$info = explode(":", $value); 
				$label = $info[0]; 
				?>

				<div class="panel panel-default">
					<div class="panel-heading">
						<i class="glyphicon glyphicon-chevron-left"></i> <?=$label?>
					</div>
					<div class="collapse">
						<div class="panel-body">
							<div class="text-<?= $lang == 2 ? "left" : "right"; // move based on language?>">
								<input type="button" class="checkAll btn btn-danger btn-xs" value="Check All" style="background-color : #5e1c77;border-color:#834999;" />
							</div>
							<br />
							<ul class="list-unstyled">
								<?
								$numWeekly = count($user->weekly_tasks);
								for ($j = 0; $j < $numWeekly; $j++) {
									if ($user->weekly_tasks[$j]->label_name == $label) {
										$weekly_task = $user->weekly_tasks[$j];
										$date_task_mark = $weekly_task->date_task_mark; 
										if ($date_task_mark->marked == true) 
											$checked = true; 
										else 
											$checked = false;
										?>
											<li class="task">
												<div class="row">
													<div class="rowImg"> 
														<img src="<?=HOME?>/campaignLogos/<?=$campaignLogos[$weekly_task->subject_id]?>" width="50" height="52" alt=""/>
													</div>
													<label class="checkbox">
														<div class="actions">
															<? if ($weekly_task->quantity) : ?>
															<input type="number"  pattern="\d*" class="textInput" id="<?=$date_task_mark->date_task_id;?>:<?=$weekly_task->mark_date;?>" 
																size="2" maxlength="3" 
																<? 
																if ($lang == 2) echo 'style="left: 15px; right: auto;'; 
																else echo 'style="float: right;"';
																?>
																font-size: 12px;" 
																<? if ($date_task_mark->done_qty) echo "value='" . $date_task_mark->done_qty . "' "; ?>	/>
															<? else : ?>
															<input type="checkbox" class="box-check weekly <? if ($checked) echo "pre-checked" ?>"
																id="<?=$weekly_task->date_task_id?>" 
																value="<?=$date_task_mark->date_task_id;?>:<?=$weekly_task->mark_date;?>"
																<? if ($checked) echo "checked" ?> />
															<!--<span class="circle"></span>-->
															<span class="check"></span>
															<span class="box"></span>
															<? endif; ?>
														</div>
														<div class="short">
															<? if ($weekly_task->mandatory_qty) echo "<span class='mandStar'>*</span>"; ?>
															<?=($weekly_task->short_name == '' ? '<br />' : $weekly_task->short_name)?>
														</div>
														<div class="long">
															<?=$weekly_task->task_name?>
														</div>
													</label>
												</div>
											</li>
										<?
									}
								}
								?>
							</ul>
						</div>
					</div>
				</div>
				<?
				$i++;
			}
		}        
		?>
		
		<?
		if (count($user->shabbos_labels) > 0) {
			$i = 1;
			foreach ($user->sorted_shabbos_labels as $value) {
				$info = explode(":", $value); 
				$label = $info[0]; 
				?>

				<div class="panel panel-default">
					<div class="panel-heading">
						<i class="glyphicon glyphicon-chevron-left"></i> <?=$label?>
					</div>
					<div class="collapse">
						<div class="panel-body">
							<? if (strtolower($label) == 'shabbos mevorchim' || strtolower($label) == 'שבת מברכים') : ?>
							<?
							/*
							$qry = "select qty, minutes from tehillim_ladders where ";
							$p = 1;
							foreach ($user->tehillim as $key => $val) {
								$qry .= $key . " = " . $val;
								if ($p++ == 1) $qry .= " and ";
							}
							//echo "<input type='hidden' name='tehillimQry' value='$qry' />";
							$res = mysql_query($qry);
							$r = mysql_fetch_assoc($res);
							*/
							echo "<span class='age' id='" . $user->tehillim['age'] . "'></span>";
							?>
							<? else : ?>
							<? if ($lang == 2) : ?>
								<div class="text-left">
							<? else : ?>
								<div class="text-right">
							<? endif; ?>
								<input type="button" class="checkAll btn btn-danger btn-xs" value="Check All" style="background-color : #5e1c77;border-color:#834999;" />
							</div>
							<br />
							<? endif; ?>
							<ul class="list-unstyled">
								<?
								$numShabbos = count($user->shabbos_tasks);
								for ($j = 0; $j < $numShabbos; $j++) {
									if ($user->shabbos_tasks[$j]->label_name == $label) {
										$shabbos_task = $user->shabbos_tasks[$j];
										/*
										if ($shabbos_task->short_name == 'Tehillim Quota') {
											echo "<li class='task' style='margin-top: -10px; margin-bottom: 10px; margin-left: 10px;'><div class='row'>";
											echo "My Ladder: <select name='userLevel' class='userLevel'>";
											for ($m = 3; $m < 8; $m++) {
												if ($user->tehillim['ladder'] == $m) echo "<option value='" . $m . "' selected>" . $m . "</option>"; 
												else echo "<option value='" . $m . "'>" . $m . "</option>";	
											}
											echo "</select></div></li>";
										}
										*/
										$date_task_mark = $shabbos_task->date_task_mark; 
										if ($date_task_mark->marked == true) 
											$checked = true; 
										else 
											$checked = false;
										?>
											<li class="task">
												<div class="row">
													<div class="rowImg"> 
														<img src="<?=HOME?>/campaignLogos/<?=$campaignLogos[$shabbos_task->subject_id]?>" width="50" height="52" alt=""/>
													</div>
													<label class="checkbox">
														<div class="actions">
															<? if ($shabbos_task->quantity  || ($shabbos_task->subject_id == 1 && in_array($shabbos_task->grid_id, array(8001,8002)))) : ?>
															<input type="number" pattern="\d*" class="textInput" id="<?=$shabbos_task->date_task_id;?>:<?=$shabbos_task->mark_date;?>" 
																size="2" maxlength="3" 
																<? if ($lang == 2) echo 'style="left: 15px; right: auto;" '; ?>
																<? if ($date_task_mark->done_qty) echo 'value="' . $date_task_mark->done_qty . '" '; ?>
																/>
															<? else : ?>
															<input type="checkbox" class="box-check weekly <? if ($checked) echo "pre-checked" ?>"
																id="<?=$shabbos_task->date_task_id?>" 
																value="<?=$date_task_mark->date_task_id;?>:<?=$shabbos_task->mark_date;?>"
																<? if ($checked) echo "checked" ?> />
															<span class="circle"></span>
															<span class="check"></span>
															<span class="box"></span>
															<? endif; ?>
														</div>
														<div class="short">
															<? if ($shabbos_task->mandatory_qty) echo "<span class='mandStar'>*</span>"; ?>
															<?=($shabbos_task->short_name == '' ? '<br />' : $shabbos_task->short_name)?>
														</div>
														<? 
														if (strtolower($label) == 'shabbos mevorchim' || $label == 'שבת מברכים') {
															if ($shabbos_task->short_name == 'Tehillim Quota' || 
																$shabbos_task->short_name == 'תהילים קוואטע') {
																echo "<div class='quota'>Quota: <b><span class='tQty tehillim'>" . $shabbos_task->quantity . "</span></b> kap.</div>";
															} else if ($shabbos_task->short_name == 'Tehillim Minutes' || 
																		$shabbos_task->short_name == 'תהילים מינוטן') {
																echo "<div class='quota'>Quota: <b><span class='tMin tehillim'>" . $shabbos_task->quantity . "</span></b> min.</div>";
															}
														}
														?>
														<div class="long">
															<?=$shabbos_task->task_name?>
														</div>
													</label>
												</div>
											</li>
											
										<?
									}
								}
								?>
							</ul>
						</div>
					</div>
				</div>
				<?
				$i++;
			}
		}
		?>
		
		<?
		if (count($user->no_label_subjects) > 0) {
			$i = 1;
			foreach ($user->no_label_subjects as $value) {
				$info = explode(":", $value); 
				$label = $info[1];
				?>

				<div class="panel panel-default noLabel">
					<div class="panel-heading">
						<i class="glyphicon glyphicon-chevron-left"></i> <?=$label?>
					</div>
					<div class="collapse mission_list">
						<div class="panel-body">
							<? if ($lang == 2) : ?>
								<div class="text-left">
							<? else : ?>
								<div class="text-right">
							<? endif; ?>
								<input type="button" class="checkAll btn btn-danger btn-xs" value="Check All" style="background-color : #5e1c77;border-color:#834999;"/>
							</div>
							<br />
							<ul class="list-unstyled">
								<?
								$numTasks = count($user->no_label_tasks);
								for ($j = 0; $j < $numTasks; $j++) {
									if ($user->no_label_tasks[$j]->mission_name == $label) {
										$no_label_task = $user->no_label_tasks[$j];
										
										// if daily marking, hide yoma depagra not relevant to today 
										$today = floor(unixtojd($timestamp));
										if (
											($_COOKIE['marking'] == 'daily' ||
											($detect->isMobile() && $_COOKIE['marking'] !== 'weekly'))
											&&
											($today < $no_label_task->start_date || $today > $no_label_task->end_date)
										) continue;
										
										$date_task_mark = $no_label_task->date_task_mark; 
										if ($date_task_mark->marked == true) 
											$checked = true; 
										else 
											$checked = false;
										?>
											<li class="task" data="<?=$no_label_task->start_date . ':' . $no_label_task->end_date?>">
												<div class="row">
													<div class="rowImg"> 
														<img src="<?=HOME?>/campaignLogos/<?=$campaignLogos[$no_label_task->subject_id]?>" width="50" height="52" alt=""/>
													</div>
													<label class="checkbox">
														<div class="actions">
															<? if ($no_label_task->quantity) : ?>
															<input type="number" pattern="\d*" class="textInput" id="<?=$date_task_mark->date_task_id;?>:<?=$no_label_task->mark_date;?>" 
																size="2" maxlength="3" 
																<? 
																if ($lang == 2) echo 'style="left: 15px; right: auto;'; 
																else echo 'style="float: right;"';
																?>
																font-size: 12px;" 
																<? if ($date_task_mark->done_qty) echo "value='" . $date_task_mark->done_qty . "' "; ?>	/>
															<? else : ?>
															<input type="checkbox" class="box-check weekly <? if ($checked) echo "pre-checked" ?>"
																id="<?=$no_label_task->date_task_id?>" 
																value="<?=$date_task_mark->date_task_id;?>:<?=$no_label_task->mark_date;?>"
																<? if ($checked) echo "checked" ?> />
															<span class="circle"></span>
															<span class="check"></span>
															<span class="box"></span>
															<? endif; ?>
														</div>
														<div class="short">
															<? if ($no_label_task->mandatory_qty) echo "<span class='mandStar'>*</span>"; ?>
															<?=($no_label_task->short_name == '' ? '<br />' : $no_label_task->short_name)?>
														</div>
														<div class="long">
															<?=$no_label_task->task_name?>
														</div>
													</label>
												</div>
											</li>
										<?
									}
								}
								?>
							</ul>
						</div>
					</div>
				</div>
				<?
				$i++;
			}
		}        
		?>
		
		<?php if (isset($_GET['naftoli'])) : ?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<i class="glyphicon glyphicon-chevron-left"></i> משניות בע"פ
				</div>
				<div class="collapse">
					<div class="panel-body">
						<div id="mishna">
													
							<? foreach ($assigned as $id => $mesechto) : ?>
								<div class="panel panel-default mishnaPanel">
									<div class="panel-heading mishnaHeader">
										<i class="glyphicon glyphicon-chevron-left"></i> מסכת <?=$mesechto?>
									</div>
									
									<div class="collapse">
										<div class="panel-body" id="<?=$id?>">
											<div class="perokim"></div>
										</div>
									</div>
								</div>
							<? endforeach; ?>
						
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
		
		</div>
		<? // quit the loop if the timestamp is today
			if (floor(unixtojd($timestamp)) == floor(unixtojd())) {
				break;
			}
		}
        ?>
		
    </div>
</div>

<div style="position: fixed;width: 100%;bottom:0px; z-index: 1000;">
    <div class="span12 footer">
		<div class="span3">
			<? 
			if (isset($_GET['app'])) echo "<a href='/reg/parent_detail.html'>"; 
			else echo "<a href='".$mobileURL."reg/parent_detail.html'>";
			?>
                <div class="menu-item">
                    <div class="span12">
                        <img src="img_new/boy-color-green-svg.svg">
                    </div>
                    <div class="span12">
                        <span>Accounts</span>
                    </div>
                </div>
			</a>
        </div>
        <div class="span3 active">
			<a href="#" id="missionsLink">
			<div class="menu-item">
				<div class="span12">
					<img src="img_new/square-check-color-purple-svg.svg">
				</div>
				<div class="span12">
					<span>Missions</span>
				</div>
			</div>
			</a>
		</div>
		<div class="span3">
			<a href="#" id="rankLink">
			<div class="menu-item">
				<div class="span12">
					<img src="img_new/achievements-color-orange-svg.svg">
				</div>
				<div class="span12">
					<span>Achievements</span>
				</div>
			</div>
			</a>
		</div>
		<div class="span3">
			<a href="#" id="storeLink">
			<div class="menu-item">
				<div class="span12">
					<img src="img_new/cart-color-red-svg.svg">
				</div>
				<div class="span12">
					<span>Rewards</span>
				</div>
			</div>
			</a>
		</div>
    </div>
</div>

<? include ("inc/modals/updateMedalRanks.php"); ?>

<style>
	.slick-prev, .slick-next {
		color: #000;
		font-size: 18px;
		font-weight: 600;
		position: absolute;
		top: 77px;
		padding: 0 5px;
		text-align: center;
		width: 35px;
	}
	.slick-prev {
		left: 0px;
	}
	.slick-next {
		right: 0px;
	/* Hide the buttons on smaller screens */
	/*@media screen and (min-width: 767px) {*/
	/*	.slick-prev, .slick-next {*/
	/*		visibility: hidden;*/
	/*	}*/
	/*}*/
</style>

<script src="/js/utils/browser_detect.js"></script>
<script>
	/******************* DATE SLIDER *******************/
	var currentSlide = <?=$jd-$start?>;
	var bSlid = localStorage.getItem('achos-missions-slid');
	
	/******************* SCREEN SIZE *******************/
	var screenSize = $(".container").width();

	//$( function() {
		//$(".checkAll").hide();
		var url = location.toString();
		var pos = url.indexOf('='); 
		var id = url.substring( pos+1 );
		
		var d = id.indexOf('&');
		if (d > 0) {
			id = id.substring( 0, d );
		}
		
		var lang = <?=$lang?>;
		
		$(".panel:not(.mishnaPanel)").addClass('open');
		$(".panel:not(.mishnaPanel) .collapse").addClass('in');
		// start the page with each dropdown expanded
		$.each($(".collapse.in"), function(index, item){
			item = $(item);
			var multiplier = parseInt(screenSize) < 790 && item.find(".dailyPanel").length > 0 ? 1.5 : 1.2; // add some (50%) initial spacing for the daily/tall ones
			if (browser_detect() != "Chrome" && browser_detect() != "Safari") { // multiplier only used in chrome. other browsers do not have the height bug....
                multiplier = 1;
            }
			//console.log(multiplier, screenSize, item.find(".dailyPanel").length > 0);
			item.css({"height": item.children()[0].scrollHeight * multiplier});
		});
		/******************* DROPDOWNS *******************/
		$(".panel-heading").click( function() {
			var c = $(this).parent().attr('class'); // get the classes of the parent
			// if the panel is open
			if (c.indexOf('open') > 0) {
				var parent = $(this).parent(); // get the parent
				parent.removeClass('open'); // remove the open class
				parent.find('> .collapse').removeClass('in'); // remove the "in" class from it's collapse child
				$(this).parent().find('> .collapse').css({"height": '0px'}); // and set the height of it to 0px so that it animates closed
			} else { // if it is closed
				// open the dropdown
				$(this).parent().addClass('open');
				$(this).parent().find('> .collapse').addClass('in');
				
				var multiplier = parseInt(screenSize) < 790 && $(this).find(".dailyPanel").length > 0 ? 1.5 : 1.2; // add some (50%) initial spacing for the daily/tall ones
				if (browser_detect() != "Chrome" && browser_detect() != "Safari") { // multiplier only used in chrome. other browsers do not have the height bug....
					multiplier = 1;
				}
				
				$(this).parent().find('> .collapse').css({"height": $(this).parent().find('.collapse').children()[0].scrollHeight * multiplier}); // set the height to the height of the content to animate it down
				// close all the other ones
				$(this).parent().siblings().removeClass('open');
				$(this).parent().siblings().find('> .collapse').removeClass('in');
				$(this).parent().siblings().find('> .collapse').css({"height": '0px'}); // set the height to 0px to animate the closing
			}
		});
		/******************* CHECK ALL EVENT LISTENER *******************/
		$(".checkAll").click( function() {
			$(this).width(65);
			if ($(this).val() == 'Check All') {
				$(this).val('Uncheck All');
				var inputs = $(this).parent().parent().find('.box-check');
				var l = inputs.length;
				for (var i = 0; i < l; i++) {
					var input = inputs[i];
					if (!$(input).is(":checked")) {
						$(input).trigger('click');
					}
				}
			} else if ($(this).val() == 'Uncheck All') {
				$(this).val('Check All');
				var inputs = $(this).parent().parent().find('.box-check');
				var l = inputs.length;
				for (var i = 0; i < l; i++) {
					var input = inputs[i];
					if ($(input).is(":checked")) {
						$(input).trigger('click');
					}
				}
			}
		});
		
		$(".checkAllDaily").click( function() {
			$(this).width(65);
			if ($(this).val() == 'Check All') {
				$(this).val('Uncheck All');
				var inputs = $(this).parent().parent().find('.dMark');
				var l = inputs.length;
				for (var i = 0; i < l; i++) {
					var input = inputs[i];
					//alert($(input).text());
					//debugger;
					if ($(input).find(".unchecked").length == 1) { // if it is unchecked
						$(input).trigger('click');
					}
				}
			} else if ($(this).val() == 'Uncheck All') {
				$(this).val('Check All');
				var inputs = $(this).parent().parent().find('.dMark');
				var l = inputs.length;
				for (var i = 0; i < l; i++) {
					var input = inputs[i];
					if ($(input).find(".checked").length == 1) {
						$(input).trigger('click');
					}
				}
			}
		});
		
		$(".box-check").click( function() {
			var daily = false;
			var weekly = false;
			
			var function_name, url;
			
			var className = $(this).attr('class');
			if (className.indexOf('daily') > 0) {
				daily = true;
			} else if (className.indexOf('weekly') > 0) {
				weekly = true;
			}
			
			var checked = $(this).is(":checked");
			if (checked) {
				if (daily) {
					function_name = "add_daily_task_mark";
				} else if (weekly) {
					function_name = "add_task_mark";
					//need to make sure all weekly checkboxes in other slides are updated as well
					var id = $(this).attr('id');
					$(".tasks-day").find("#" + id).attr('checked', true);
            	}
            	url = "../add_functions.php";
			} else {
				if (daily) {
					function_name = "delete_daily_task_mark";
				} else if (weekly) {
					function_name = "delete_task_mark";
					//need to make sure all weekly checkboxes in other slides are updated as well
					var id = $(this).attr('id');
					$(".tasks-day").find("#" + id).attr('checked', false);
            	}
            	url = "../delete_functions.php";
			}
			
			var user_id = <?=$user_id?>;
			var value = $(this).val();
			var pos = value.indexOf(':');
			var date_task_id = value.substring(0,pos);
			var mark_date = value.substring(++pos, value.length);
			        			
			var parameters = [user_id, date_task_id, mark_date];
            url += "?function_name=" + function_name + "&parameters=" + parameters;

            $.getJSON(url, function(success) {  
                if (success == false) {
                    alert("Update not performed.");
                } else {
					updateMedalRanks.update(user_id);
				}
            });
		});
		
		$(".dMark").click( function() {
			var id = $(this).find('span.dMarkID').attr('id');
			if (id) {
				//alert(id);
				var pos = id.indexOf(':');
				var task_id = id.substring(0,pos);
				var rest = id.substring(++pos);
				var pos2 = rest.indexOf(':');
				var mark_date = rest.substring(0,pos2);
				var checked = parseInt(rest.substring(++pos2));
				var user_id = <?=$user_id?>;
				var parameters = [user_id, task_id, mark_date];
				
				var url, function_name;
				if (checked) {
					url = "../delete_functions.php";
					function_name = "delete_daily_task_mark2";
				} else {
					url = "../add_functions.php";
					function_name = "add_daily_task_mark2";
				}
				url += "?function_name=" + function_name + "&parameters=" + parameters + "&update=1"; // was set to 0
				//alert(url);
				var span = this;
				$.getJSON(url, function(success) {
					if (success == 1) {
						alert("Update not performed.");
					} else {
						updateMedalRanks.update(user_id);
						$(span).empty();
						var html = "<span class='dMarkID' id='" + task_id + ':' + mark_date;
						//if (checked) html += ":0'></span><span style='color: red'>&#x2717;</span>";
						//else html += ":1'></span><span style='color: green'>&#x2713;</span>";
						if (checked) html += ":0'></span><span class='unchecked'></span>";
						else html += ":1'></span><span class='checked'></span>";
						$(span).append(html);
					}
				});
			}
		});
		
		$(".textInput").keyup( function() {
			var info = $(this).attr('id');
			var split = info.indexOf(':');
			var task = info.substring(0,split);
			var date = info.substring(++split);
			var user_id = <?=$user_id?>;
			var url = '';
			var div = this;
			
			var val = $(this).val();
			if (val == '') {
				val = 0;
			}
			//if (val > 0) {
                var parameters = [user_id, task, date, val];
                url = "../add_functions.php?function_name=add_mark&parameters=" + parameters + "&update=1";
                $.getJSON(url, function(success) {
					if (success == false) {
						alert("Update not performed.");
					} else {
						updateMedalRanks.update(user_id);
					}
				});
			//}
		});
		
		$(".userLevel").change( function() {
			var level = $(this).val();
			var age = $(".age").attr('id');
			$.post('..<?=$mobileURL?>reg/ajax/changeLevel.php', { user: id, level : level, age : age }, function( success ) {
				if (success) {
					var data = $.parseJSON( success );
					$(".tQty").text( data.qty );
					$(".tMin").text( data.min );
				}
			});
		});
		
		$(".noLabel").each( function() {
			var obj = $(this).find('.task');
			if (!obj.length) {
				$(this).remove();
			}
		});
		
		<? if($daily) {?>
//		Show the hint
			if (!bSlid) { // make sure that the user has never swiped before on this system....
				$('.navbar-header').addClass('hint');
				setTimeout(function(){
					$('.navbar-header').removeClass('hint');
				}, 2500);
			}
//			Set up the sliding menu
			$('.slick-slider').slick({
				arrows: true,
				prevArrow: '<button type="button" class="slick-prev"><img src="img_new/arrow-1-color-white-svg.svg"/></button>',
				nextArrow: '<button type="button" class="slick-next"><img src="img_new/arrow-1-color-white-svg.svg"/></button>',
				infinite: false,
				responsive: [{
					breakpoint: 1024,
					settings: {
					slidesToShow: 1,
					centerMode: false,
					slidesToScroll: 1
					}
				}],
				mobileFirst: true,
				initialSlide: <?=$jd-$start?>,
				onInit: function() {
					var date = $('.slick-slider').find('.item').eq(currentSlide).attr('data-date');
					$('.content .tasks-day').fadeOut('fast').filter('[data-date='+date+']').fadeIn('fast');
				},
				onAfterChange: function (d) {
					if (!bSlid) {
						localStorage.setItem('achos-missions-slid',1);
					}
					if (d.currentSlide!=currentSlide) {
						currentSlide = d.currentSlide;
						var date = $('.slick-slider').find('.item').eq(currentSlide).attr('data-date');
						$('.content .tasks-day').fadeOut('fast').filter('[data-date=' + date + ']').fadeIn('fast');
					} else {
						if (currentSlide == 0 && d.currentSlide == 0) {
							//only allow going back up to two weeks
							var today = <?=floor(unixtojd())?>;
							var start = <?=$start?>;
							if ((today - start) < 18) {
								//reload page with mission dates of the previous week
								var url = "..<?=$mobileURL?>missionsNew.html?id=" + id + "&d=" + <?=$start - 1?>;
								window.location.href = url;
							}						
						} else if (currentSlide == 6 && d.currentSlide == 6) {
							//reload page with mission dates of the next week					
							var url = "..<?=$mobileURL?>missionsNew.html?id=" + id + "&d=" + <?=$end + 1?> + "&s=1";
							window.location.href = url;
						}
					}
				}
			});
		<?}?>
		
		// change the Daily/Weekly view button based on the cookies...
		if (Cookies.get('marking') == 'weekly') {
			$('.dailyBoxes').show();
			var w = $(".showProgress").width();
			$(".showProgress").val('Daily View');
			$(".showProgress").width( w );
		} else if (Cookies.get('marking') == 'daily') {
			$(".showProgress").val('Weekly View');
		}

		
		$(".showProgress").click( function() {
			if ($(this).val() == 'Weekly View') {
				Cookies.set('marking', 'weekly');
			} else if ($(this).val() == 'Daily View') {
				Cookies.set('marking', 'daily');
			}
			location.reload();
			//$(this).parent().parent().find('.dInfo').toggle();
			//$(this).parent().parent().find('.dMark').toggle();
		});
		
		$(".parsha-navigator").click(function(event){
			var date = event.target.dataset.d;
			var url = "..<?=$mobileURL?>missionsNew.html?id=<?=$user_id?>" + (date ? "&d=" + date : "");
			window.location.href = url;
		});
		
		//$("#dateSelection").change(function() {
		//	var date = $(this).val();
		//	// reload page with mission dates of the chosen week
		//	var curParsha = <?=$curParsha['end']?>;
		//	if (date != curParsha) var url = "..<?=$mobileURL?>missionsNew.html?id=" + id + "&d=" + date;
		//	else var url = "..<?=$mobileURL?>missionsNew.html?id=" + id;
		//	window.location.href = url;
		//});
	//});
	
	//$(window).load(function(){        		
        $(".slider img").click(function(){
            var item_id = $(this).attr("id");
            $(".slider img").each(function(){
                $(this).removeClass("active");
            });
            $("#item"+item_id).addClass("active");
            item_id = item_id.replace("item","");
            if(item_id == "0"){
                carousel.select(0, parseInt(item_id)+1);
            }else{
                carousel.select(parseInt(item_id), parseInt(item_id)+1);
            }
            console.log(parseInt(item_id)+1);
        });
	//});
	
		var he = <?=json_encode( $he_chars )?>;
		$(".panel-heading.mishnaHeader").click( function() {
			//var c = $(this).parent().attr('class');
			//if (c.indexOf('open') > 0) {
				//$(this).parent().removeClass('open');
				//$(this).parent().find('.collapse').removeClass('in');
			//} else {
				//$(this).parent().addClass('open');
				//$(this).parent().find('.collapse').addClass('in');
				//$(this).parent().siblings().removeClass('open');
				//$(this).parent().siblings().find('.collapse').removeClass('in');
			//}
			
			var user = id;
			var panel = $(this).parent().find(".panel-body");
			var p = $(panel).find(".perokim");
			if ($(p).html() == '') {
				var mesechto = $(panel).attr('id');
				
				$.post('/ajax/getPerokim.php', { mesechto : mesechto, user : user, getLearned : true }, function( reply ) { 
					var info = $.parseJSON( reply );
					var perokim = info[0];
					var learned = info[1];
					
					var html = "<div class='mishnaDiv'><input type='checkbox' class='entireM' /> Entire מסכת </div>";
					html += "<div class='mishnaDiv'><input type='checkbox' class='mesechtoAtOnce' /> בבת אחת</div>";
					html += "<div style='clear: both'></div><br />";
					for (var perek in perokim) {
						html += "<div class='perekID' id='" + perek + "'><div class='mishnaDiv'>";
						html += "<input type='checkbox' class='perek' /> פרק " + he[perek] + "</div>";
						html += "<div class='mishnaDiv'><input type='checkbox' class='perekAtOnce' /> בבת אחת</div>";
						html += "<div style='clear: both'></div>";
						for (var mishna in perokim[perek]) {
							html += "<span class='mishnaSingle'><input type='checkbox' class='mishna' id='" + mesechto + ':' + perek + ':' + mishna + ':' + perokim[perek][mishna] + "'";
							if (learned[perek][mishna]) {
								html += " checked";
							}
							html += " /> משנה " + he[mishna] + "&nbsp;&nbsp;&nbsp;</span>";
							//html += "(" + perokim[perek][mishna] + ") ";
						}
						html += "</div><div style='clear:both'></div><hr width='75%' />";
					}
					//alert(html);
					$(p).empty();
					$(p).append(html);
					
					$(".entireM").click( function() {
						var p = $(this).parent().parent().find('.perek');
						$(p).trigger('click');
						
						if ($(this).is(":checked")) {
							if (!$(p).is(":checked")) {
								$(p).trigger('click');
							}
						} else {
							if ($(p).is(":checked")) {
								$(p).trigger('click');
							}
							$(this).parent().parent().find('.mesechtoAtOnce').attr('checked', false);
						}
					});
					
					$(".mesechtoAtOnce").click( function() {
						if ($(this).is(":checked")) {
							var elem = $(this).parent().parent().find('.entireM');
							if (!elem.is(":checked")) {
								$(elem).trigger('click');
							}
							//save to db
							$.post('markAtOnce.php', { 
								user : user, 
								mesechto : mesechto, 
								perek : 0, 
								checked : true 
							}, function( error ) {
								if (error != 0) {
									alert('There was an error saving.');
								}
							});
						} else {
							//delete from db
							$.post('markAtOnce.php', { 
								user : user, 
								mesechto : mesechto, 
								perek : 0, 
								checked : false 
							}, function( error ) {
								if (error != 0) {
									alert('There was an error saving.');
								}
							});
						}
					});
					
					$(".perekAtOnce").click( function() {
						var perek = $(this).parent().parent().attr('id');
						if ($(this).is(":checked")) {
							var elem = $(this).parent().parent().find('.perek');
							if (!elem.is(":checked")) {
								$(elem).trigger('click');
							}
							//save to db
							$.post('markAtOnce.php', { 
								user : user, 
								mesechto : mesechto, 
								perek : perek, 
								checked : true 
							}, function( error ) {
								if (error != 0) {
									alert('There was an error saving.');
								}
							});
						} else {
							//delete from db
							$.post('markAtOnce.php', {
								user : user, 
								mesechto : mesechto, 
								perek : perek, 
								checked : false 
							}, function( error ) {
								if (error != 0) {
									alert('There was an error saving.');
								}
							});
						}
					});
					
					$(".perek").click( function() {
						var m = $(this).parent().parent().find('.mishna');
						if ($(this).is(":checked")) {
							$(m).each( function() {
								if (!$(this).is(":checked")) {
									$(this).trigger('click');
								}
							});
						} else {
							//make sure mesechto is 'unchecked'
							$(this).parent().parent().find('.perekAtOnce').attr('checked', false);
							$(m).each( function() {
								if ($(this).is(":checked")) {
									$(this).trigger('click');
								}
							});
						}
					});
					
					$(".mishna").click( function() {
						if ($(this).is(":checked")) {
							//save to db
							var id = $(this).attr('id');
							$.post('markMBP.php', { user : user, info : id, checked : true }, function( error ) {
								if (error != 0) {
									alert('There was an error saving.');
								}
							});						
						} else {
							//delete from db
							var id = $(this).attr('id');
							$.post('markMBP.php', { user : user, info : id, checked : false }, function( error ) {
								if (error != 0) {
									alert('There was an error saving.');
								}
							});
						}
					});
				});
			}
		});
</script>
<script src="js/bug_report.js?v=2.0"></script>
