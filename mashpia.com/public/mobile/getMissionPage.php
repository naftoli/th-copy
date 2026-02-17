<?php 
ini_set('max_execution_time', 300);
//echo "We are working on some upgrades. Please check again later. Sorry for the inconvenience.";
//exit;
$days_of_week = array("F", "ש", "S", "M", "T", "W", "T");
$mobileURL = "/".explode("/", $_SERVER['REQUEST_URI'])[1]."/"; // get if we are in /mobile/ or /mobileDev/
$daily = isset($_GET['daily']) && $_GET['daily']  == "true" ? true : false; // Get if we should render the daily view or not
$desktop = isset($_GET['desktop']) && $_GET['desktop']  == "true" ? true : false; // Get if we are on a desktop or not
if ($desktop) $daily = false; // only show the weekly view on the desktop...

/********************** LOGOS FOR CAMPAIGNS **********************/
$campaignLogos = array(
    1	=>	'Tehillim.svg',
    4	=>	'Tefilla.svg',
    12	=>	'Mivtzoim.svg',
    13	=>	'Niggunim.svg',
    16	=>	'hiskashrus.svg',
    21	=>	'sefer-hamitzvos.svg',
    27	=>	'tanya.svg',
    40	=>	'Yom-Dipagra.svg',
    41	=>	'Father-son.svg',
    42	=>	'Footsteps.svg',
    45	=>	'Cheshbon-Hanefesh.svg',
    90	=>	'Chitas.svg',
    92  =>  'Niggunim.svg',
    93  =>  'Mivtzoim.svg',
    94  =>  'Yom-Dipagra.svg',
    100	=>	'Brias-Haguf.svg',
    121 =>  "day-school-Jewish Day 250 px.svg",
    122 =>  "day-school-Jewish Uniform 250px.svg",
    124 =>  "day-school-Health 250px.svg",
    125 =>  "day-school_Torah 250px.svg",
    126 =>  "day-school_Shabbat 250px.svg",
    127 =>  "day-school_Special Days 250px.svg",
    129 =>  "day-school-Kosher 250px.svg",
    130 =>  "day-school_Tefilla.svg",
    131 =>  "day-school-ahavat-yisrael.svg",
    132 =>  "day-school-brachot.svg",
    133 =>  "day-school-Tzedaka.svg",
    134 =>  "day-school-honoring parents 250px.svg",
    135 =>  "day-school-Middot-icon.svg",
	136 =>  "pesukim.jpeg"
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
	92  =>  'Niggunim.gif',
	93  =>  'Mivtzoim.gif',
	94  =>  'Yomei Dipagra.gif',
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
	92  =>  'niggunim 5 of 7.png',
	93  =>  'mivtzoyim 5 of 7.png',
	94  =>  'yoma dipagra 5 of 7.png',
	100	=>	'brias haguf 5 of 7.png'
);
/********************** LOAD UP THE DATABASE CONNECTION **********************/
require_once '../db.php';

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
include("../classes/pesukim_task.php");
include("../pesukim/class.pesukim.php");

/********************** LOAD THE USER **********************/
$user_id = mysql_real_escape_string( $_GET['id'] );
$lang = 1; // default language is english
$sql = "SELECT * FROM users WHERE user_id = " . $user_id; // get the user
$query = mysql_query($sql); // run the query
$row = mysql_fetch_assoc($query); // and get the user

$lang = $row['lang_id']; // update the language
$user = new user($row); // create a new user
$user_registered = $user->user_registered && $user->user_start_date > 0 ? 1 : 0;

$school = $user->school_id;
$grade = $user->class_id;

if (in_array($school, [180, 709])) {
    echo "Your school does not allow missions to be entered here.";
    exit;
}

$curParsha = [];
$limit = isset($_COOKIE['naftoli']) && $_COOKIE['naftoli'] ? 365 : 28;
if (!isset($_GET['d']) || intval($_GET['d']) < unixtojd() - $limit) { // if the date was not provided or it is older then limit days ago
	//get todays day
	$jd = unixtojd();
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
	if (! isset($_COOKIE['naftoli'])) $jd = $jd < unixtojd() ? $jd : unixtojd(); // make sure that they cannot go into the future
	$today = intval(date('w', jdtounix($jd)));
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

// ********************** LOAD THE USER **********************/
$user->get_rank(); // get his rank
$user->get_school_class(); // and get his class
chdir('../'); // move up a directory
$user->get_user_tracks( -1, $start, $end, array(), $user->lang_id ); // get the users tracks
// echo "<pre>"; print_r( $user ); echo "</pre>"; exit;
chdir('mobile'); // and come back to this folder

// don't show weekly button for day school kids
//$day_school = false;
//if (in_array($user->school_type_id, [4,5])) {
//    $daily = true;
//    $desktop = false;
//    $day_school = true;
//}

/********************** GET THE PARSHA **********************/
$sql = "select name from parshos where start = " . $start . " and end = " . $end;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$hWeek = $row['name'];
//echo "<pre>"; print_r($user); echo "</pre>"; exit;
if (isset($_GET['app'])) define('HOME', 'mission_report');
else define('HOME', '../mission_report');

/********************** GET ALL THE PARSHIOS **********************/
$parshos = [];
if (isset($_GET['d'])) {
	$jdTemp = unixtojd();
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
// only allow parents to go back 3 weeks
$sql = "SELECT * FROM parshos WHERE end <= " . $curParsha['end'] . " ORDER BY end DESC";
if ($limit == 28) $sql .= " LIMIT 12";
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
//$sql = "select school_id, class_id from users where user_id = " . $user_id; // get the school and class id from the users table for the given user
//$result = mysql_query($sql);
//$row = mysql_fetch_assoc($result);
//$school = $row['school_id'];
//$grade = $row['class_id'];
$assigned = MishnaInfo::getAssignedAll( $school, $grade, $user_id, true );
$he_chars = array(
	1	=>	'א',	2	=>	'ב',	3	=>	'ג',		4	=>	'ד',		5	=>	'ה',
	6	=>	'ו',		7	=>	'ז',		8	=>	'ח',	9	=>	'ט',	10	=>	'י',
	11	=>	'יא',	12	=>	'יב',	13	=>	'יג',	14	=>	'יד',	15	=>	'טו',
	16	=>	'טז',	17	=>	'יז',	18	=>	'יח',	19	=>	'יט',	20	=>	'כ',
	21	=>	'כא',	22	=>	'כב',	23	=>	'כג',	24	=>	'כד',	25	=>	'כה',
	26	=>	'כו',	27	=>	'כז',	28	=>	'כח',	29	=>	'כט',	30	=>	'ל'
);

function setDaySchoolSubjects() {
    $sql = "select subject_id from subjects where inst_id = 4";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $daySchoolSubjects[] = $row['subject_id'];
    }
    return $daySchoolSubjects;
}
$daySchoolSubjects = setDaySchoolSubjects();

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
						$jdate = substr(jdtojewish(($start + ($from-1)), true, CAL_JEWISH_ADD_GERESHAYIM),0,-6);
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
						if (unixtojd($timestamp) >= unixtojd() && $from > 1) { 
							break;
						}
					} 
				} else {
					// the current date
					$timestamp = time();
					$jdate = substr(jdtojewish(unixtojd(), true, CAL_JEWISH_ADD_GERESHAYIM),0,-6);
					$jdate = iconv ('windows-1255', 'utf-8', $jdate);
					// jewish start and end dates
					$j_start_date = substr(jdtojewish($start, true, CAL_JEWISH_ADD_GERESHAYIM),0,-6);
					$j_start_date = iconv ('windows-1255', 'utf-8', $j_start_date);
					$j_end_date = substr(jdtojewish($start + 6, true, CAL_JEWISH_ADD_GERESHAYIM),0,-6);
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
						<? if($end - 7 >= unixtojd() - $limit) { ?>
							<a id="previous" class="parsha-navigator" href="<?="..{$mobileURL}missionsNew.html?id=$user_id&d=" . ($end - 7)?>">
								<img src="img_new/arrow-1-color-white-svg.svg" /> <?=$parshos[$end - 7]?>
							</a>
						<? } ?>
						<? if($end + 7 < unixtojd() + 7) { ?>
							<a id="next" class="parsha-navigator" href="<?="..{$mobileURL}missionsNew.html?id=$user_id" . ($end + 7 < unixtojd() ? "&d=" . ($end + 7) : "")?>">
								<?=$parshos[$end + 7]?> <img src="img_new/arrow-1-color-white-svg.svg" />
							</a>
						<? } ?>
					</div>
				<? }?>
            </div>
        </div>
    </div>
</header>

<div class="personalImg"></div> <? //div to load the users image into ?>
<?php if ( isset($_COOKIE['lang']) && $_COOKIE['lang'] == 'he' ) : {} else: ?>
<div class="bug-report">
	<img src="/mobile/img_new/tools-color-white-svg.svg" data-user_id="<?=$user_id?>" data-category="Marking Missions" alt="bug-report" />
</div>
<?php endif; ?>

<?php //if ( !$user->user_registered ) { ?>
<!-- <div class="container">
    <div class="content">
        <h1 style='text-align: center; font-weight: bold;'>
            <?php if (isset($_COOKIE['lang']) && $_COOKIE['lang'] == 'he') {
                ?>
            <span style="direction: rtl">
                נראה ש <?=$user->first?> לא רשום כעת לצבאות ה'<br /><br />
                נא לרשום <?=$user->gender == 'M' ? 'אותו' : 'אותה'?> לפני שמסמנים משימות
            </span>
                <?php
            } else {
                ?>
                It appears that <?=$user->first?> is currently unenrolled from Chayolei Tzivos Hashem (CTH).<br/><br/>
                Please contact your base commander regarding enrolling <?=$user->gender == 'M' ? 'him' : 'her'?> in Chayolei Tzivos Hashem.
                <?php
            }
            ?>
        </h1>
    </div>
</div> -->
<?php //} else { ?>
    <div class="container">
        <div class="content">
            <? /********************** BUTTONS ON THE TOP OF THE PAGE **********************/ ?>
            <div style="margin-bottom: 20px;">
                <? $alignmentRight = $lang == 2 ? "left" : "right"; // swich right to left for hebrew ?>
                <? $alignmentLeft  = $lang == 2 ? "right" : "left"; // swich left to right for hebrew ?>
                <div id="buttons" style="display: flex; justify-content: space-between; flex-wrap: wrap;">
					<?php require_once dirname(__FILE__) . '/reg/ajax/encrypt.php';?>
					<input type="button" class="showProgress btn btn-danger btn-sm" value="Weekly View" style="<?= $desktop ? "display: none" : ""?>" />
					<?php if ( (!isset($_COOKIE['lang']) || $_COOKIE['lang'] != 'he') && isset($_COOKIE['admin']) ) { ?>
						<a id="printLink" href="/mission_report/newParentPrint.php?bypass=1&admin=<?=encrypt_decrypt('decrypt', $_COOKIE['admin'])?>" target="_blank" style="<?=$desktop ? "" : "display: none"?>"
							class="btn btn-danger btn-sm" type="button">Print Missions
						</a>
					<?php } ?>
					<a type="button" class="btn btn-danger btn-sm" id="goalsLink">Personalize</a>
					<a type="button" class="btn btn-danger btn-sm" href="/mobile/duch/?id=<?=$user_id?>">Duch</a>
					<a type="button" class="btn btn-danger btn-sm" href="/mobile/streaks/?id=<?=$user_id?>">Streaks</a>
					<a type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#myModal">Help</a>
                </div>
                <? /********************** DATE DROPDOWN. TODO: REMOVE AND MOVE TO SLIDING BAR ON TOP **********************/ ?>
                <div style="clear:both"></div>
            </div>

			<?php if (isset($_COOKIE['naftoli']) && !in_array($user->school_type_id, [4, 5])) { ?>
				<style>
					.sponsor {	
						height: 70px;
						border-radius: 10px;
						background-color: #e9ecef; /* A nice, soft blue color */
						padding: 10px;
						margin: 10px;
						text-align: center;
						border: 1px solid #ced4da; /* A matching border color */
						vertical-align: middle;
						font-family: cursive;
						font-size: 17px;
						line-height: 1.5;
					}
					.sponsor img {
						width: 50px;
						height: 50px;
						border-radius: 50%;
						margin-right: 10px;
						float: right;
					}
				</style>
				<div class="sponsor">
					<?php
					// check if there is a sponsorship for this week
					$start_d = jdtogregorian($start);
					$end_d = jdtogregorian($end);
					// convert from mm/dd/yyyy to yyyy-mm-dd
					$start_d = date('Y-m-d', strtotime($start_d));
					$end_d = date('Y-m-d', strtotime($end_d));
					$sponsorships = mysql_query("SELECT * FROM mashpiadb.sponsorships WHERE start_date >= '" . $start_d . "' AND end_date <= '" . $end_d . "' LIMIT 1");
					$sponsorship = mysql_fetch_assoc($sponsorships);
					if ($sponsorship) { 
						$image = '/sponsorships/images/' . $sponsorship['image'];
						$sponsor = 'This weeks missions have been sponsored by: <strong>' . $sponsorship['sponsor'] . '</strong>';
						$reason = $sponsorship['reason'] . ': <strong>' . $sponsorship['name'] . '</strong>';
						if ($sponsorship['image']) echo '<img src="' . $image . '" />';
						echo $sponsor . '<br />';
						echo $reason;						
					} else if (!$sponsorship) {
						echo '<img src="/images/avatar_boy.jpg" />';
						echo 'This weeks missions are available for sponsorship.';
					}
					?>
				</div>
			<?php } ?>

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
							<?php $panel_id = 'panel_' . $index; ?>
                            <div class="collapse in" id="<?=$panel_id?>">
                                <div class="panel-body dailyPanel">
									<div class="text-<?= $lang == 2 ? "left" : "right"; // move based on language?>">
										<input id="checkAll" type="button" class="checkAll<?=!$daily ? "Daily" : ""; // change the class if we are rendering the whole week.?> btn btn-danger btn-xs i18n" data-key="CheckAll" value="Check All" style="background-color : #1b2b51;border-color:#1b2b51; <?//$desktop ? "" : "display: none"; ?>"/> 
									</div>
									<br />
									<?//if ($lang == 2) echo '<br />'; // extra space for hebrew ?>
									<ul class="list-unstyled">
										<? /************ RENDER EACH TASK ***************/
										$numDaily = count($user->daily_tasks); // count the tasks
										for ($j = 0; $j < $numDaily; $j++) { // for each task
											if ($user->daily_tasks[$j]->label_name == $label) { // make sure that the label fits the label that we are showing.....
												$daily_task = $user->daily_tasks[$j]; // get the daily task
												if ( isset( $daily_task->date_task_marks[ $from - 1 ] ) ) 
													$date_task_mark = $daily_task->date_task_marks[ $from - 1 ]; // and get the mark for todays date <= does not work if the mission starts later in the week...
												else 
													$date_task_mark = false;
													
												// if the total count of the missions is less then 7 (does not cover the full week) AND the first one does not start at the beginning of the week....
												if($daily && count($daily_task->date_task_marks) < 7 && $daily_task->date_task_marks[0]->mark_date != $start){ // if there are less then 7 date_task_marks and the first one is not the first date....
													foreach($daily_task->date_task_marks as $task){ // go through each task
														if($task->mark_date == $start + ($from - 1 )){ // if the mark date is the date we are generating....
															$date_task_mark = $task; break;
														} else {
															$date_task_mark = false;
														}
													}
												}
												// change the $checked variable based on if the task is marked
												$checked = isset( $date_task_mark->marked ) ? $date_task_mark->marked == true : false;
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
																<?php if ($daily_task->quantity > 1) : ?>
																	<script>
																		$('#<?=$panel_id?> #checkAll').remove()
																	</script>
																	<input type="number"  pattern="\d+" class="textInput" id="<?=$date_task_mark->date_task_id;?>:<?=$date_task_mark->mark_date;?>" 
																		size="2" maxlength="3" 
																		<? 
																		if ($lang == 2) echo 'style="left: 15px; right: auto; position: relative;'; 
																		else echo 'style="float: right; position: relative;"';
																		?>
																		font-size: 12px;" 
																		<? if ($date_task_mark->done_qty) echo "value='" . $date_task_mark->done_qty . "' "; ?>	/>
																<?php else : ?>
																	<div class="actions">
																		<input type="checkbox" class="box-check daily <? if ($checked) echo "pre-checked" ?>" 
																			value="<?=$date_task_mark->date_task_id;?>:<?=$date_task_mark->mark_date;?>" 
																			<? if ($checked) echo "checked" ?> />
																		<span class="check"></span>
																		<span class="box"></span>
																	</div>
																<?php endif; ?>
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
																<table <? if ($daily_task->quantity > 1) echo 'style="margin-left: 15px;"'; ?>>
																	<tr>
																	<?foreach ($days_of_week as $index => $day) { // go through the days of the week
																		if (in_array(($start + $index), $dates)) { // if the date is available
																			$k = array_search($start + $index, $dates); // get the key
																			$until = $jd-$start; // get the until
																			$task_mark = $daily_task->date_task_marks[$k]; // get the date_task_mark or the day
																			$done = $task_mark->marked == true; // mark if it was done
																			?>
																			<td>
																				<?php if ($daily_task->quantity > 1) : ?>
																					<script>
																						$('#<?=$panel_id?> #checkAll').remove()
																					</script>
																					<input type="number" pattern="\d+" class="textInput" id="<?=$task_mark->date_task_id;?>:<?=$task_mark->mark_date;?>" 
																						size="2" maxlength="3" 
																						<? 
																						if ($lang == 2) echo 'style="left: 15px; right: auto; position: relative;'; 
																						else echo 'style="float: right; position: relative;"';
																						?>
																						font-size: 12px;" 
																						<? if ($task_mark->done_qty) echo "value='" . $task_mark->done_qty . "' "; ?>	/>
																				<?php else : ?>
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
																				<?php endif; ?>
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
																<?
																if ($daily_task->mandatory_qty && !in_array($daily_task->subject_id, $daySchoolSubjects)) { // if it is mandatory get the stickers info
																	echo "<div class='mandatoryImg'><img src=\"" . HOME . "/5of7stickers/" . $dailyStickers[$daily_task->subject_id] . "\" /></div>";
																}
																if ($daily_task->grid_id == 13012) {
																	if (in_array($lang, [2, 4])) $style = "margin-right: -20px;"; // yiddish / hebrew
																	// else $style = "margin-left: -20px;";
																	else $style = '';
																	echo "<div class='mandatoryImg'><img src=\"/mission_report/mission_marathon/tiny.png\" style='" . $style . "' /></div>";
																}
																?>
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
                                    <input type="button" class="checkAll btn btn-danger btn-xs i18n" data-key="CheckAll"  value="Check All" style="background-color : #1b2b51;border-color:#1b2b51;" />
                                </div>
                                <br />
                                <ul class="list-unstyled">
                                    <?
                                    $numWeekly = count($user->weekly_tasks);
                                    for ($j = 0; $j < $numWeekly; $j++) {
                                        if ($user->weekly_tasks[$j]->label_name == $label) {
                                            $weekly_task = $user->weekly_tasks[$j];
											$date_task_mark = $weekly_task->date_task_mark; 
											$checked = isset( $date_task_mark->marked ) ? $date_task_mark->marked == true : false;
                                            ?>
                                                <li class="task">
                                                    <div class="row">
                                                        <div class="rowImg"> 
                                                            <img src="<?=HOME?>/campaignLogos/<?=$campaignLogos[$weekly_task->subject_id]?>" width="50" height="52" alt=""/>
                                                        </div>
                                                        <label class="checkbox">
                                                            <div class="actions">
                                                                <? if ($weekly_task->quantity) : ?>
                                                                <input type="number" pattern="\d+" class="textInput" id="<?=$date_task_mark->date_task_id;?>:<?=$weekly_task->mark_date;?>" 
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
                                    <input type="button" class="checkAll btn btn-danger btn-xs i18n" data-key="CheckAll"  value="Check All" style="background-color : #1b2b51;border-color:#1b2b51;" />
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
                                            $checked = isset( $date_task_mark->marked ) ? $date_task_mark->marked == true : false;
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
                                    <input type="button" class="checkAll btn btn-danger btn-xs i18n" data-key="CheckAll"  value="Check All" style="background-color : #1b2b51;border-color:#1b2b51;"/>
                                </div>
                                <br />
                                <ul class="list-unstyled">
                                    <?
                                    $numTasks = count($user->no_label_tasks);
                                    for ($j = 0; $j < $numTasks; $j++) {
                                        if ($user->no_label_tasks[$j]->mission_name == $label) {
                                            $no_label_task = $user->no_label_tasks[$j];
                                            
                                            // if daily marking, hide yoma depagra not relevant to today 
                                            $today = unixtojd($timestamp);
                                            if (
                                                ((isset($_COOKIE['marking']) && $_COOKIE['marking'] == 'daily') ||
                                                ($detect->isMobile() && (isset($_COOKIE['marking']) ? $_COOKIE['marking'] !== 'weekly' : 1)))
                                                &&
                                                ($today < $no_label_task->start_date || $today > $no_label_task->end_date)
                                            ) continue;
                                            
                                            $date_task_mark = $no_label_task->date_task_mark; 
                                            $checked = isset( $date_task_mark->marked ) ? $date_task_mark->marked == true : false;
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
                                    if (isset($_COOKIE['naftoli'])) {
                                        echo "Click <a href='stopwatch.html' target='_blank'>here</a> for timer / stopwatch.";
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

			// if (isset($_COOKIE['naftoli'])) {
			// add 12 pesukim tasks
			if (count($user->pesukim_labels) > 0) {
				// get mechunachim for child
				$p = new Pesukim($user->user_id);
				$mechunachim = $p->getMechunachim();
				$verified = [];
				$mechunachim_tasks = [];
				foreach ($mechunachim as $mechunach) {
					if (intval($mechunach['verified'])) {
						$verified[] = $mechunach;
					}
				}
				
                $i = 1;
                foreach ($user->sorted_pesukim_labels as $value) {
                    $info = explode(":", $value); 
                    $label = $info[0];
					$originalLabel = $label;

					if (strpos(strtolower($label), 'teach') !== false) {
						$showMechunachim = true;
						$numMechunachim = count($verified) > 0 ? count($verified) : 1;										
					} else {
						$showMechunachim = false;
						$numMechunachim = 1;
					}

					for ($k = 0; $k < $numMechunachim; $k++) {
						if ($showMechunachim && ! empty($verified)) {
							$name = $verified[$k]['name'];
							$mechunach_id = $verified[$k]['mechunach_id'];
							$label = $originalLabel . ' - ' . $name;
						}
						?>
						<div class="panel panel-default">
							<div class="panel-heading">
								<i class="glyphicon glyphicon-chevron-left"></i> <?=$label?>
							</div>
							<div class="collapse">
								<div class="panel-body">
									<div class="text-<?= $lang == 2 ? "left" : "right"; // move based on language?>">
										<input type="button" class="checkAll btn btn-danger btn-xs i18n" data-key="CheckAll"  value="Check All" style="background-color : #1b2b51;border-color:#1b2b51;" />
									</div>
									<br />
									<?php if ($showMechunachim && empty($verified)) : ?>
									<p><i>No mechunachim found. Please add a mechunach first.</i></p>
									<?php else: ?>
									<ul class="list-unstyled">
										<?
										$numPesukim = count($user->pesukim_tasks);
										for ($j = 0; $j < $numPesukim; $j++) {
											if ($user->pesukim_tasks[$j]->label_name == $originalLabel) {
												$pesukim_task = $user->pesukim_tasks[$j];
												$date_task_mark = $pesukim_task->date_task_mark; 
												$checked = isset( $date_task_mark->marked ) ? $date_task_mark->marked == true : false;
												if ($showMechunachim) {
													$mid = $pesukim_task->date_task_id . '_' . $mechunach_id;
												} else {
													$mid = $pesukim_task->date_task_id;
												}
												?>
												<li class="task">
													<div class="row">
														<div class="rowImg"> 
															<img src="<?=HOME?>/campaignLogos/<?=$campaignLogos[$pesukim_task->subject_id]?>" width="50" height="52" alt=""/>
														</div>
														<label class="checkbox">
															<div class="actions">
																<? if ($pesukim_task->quantity) : ?>
																	<input type="number" pattern="\d+" class="textInput" id="<?=$date_task_mark->date_task_id;?>:<?=$pesukim_task->mark_date;?>" 
																		size="2" maxlength="3" 
																	<? 
																	if ($lang == 2) echo 'style="left: 15px; right: auto;'; 
																	else echo 'style="float: right;"';
																	?>
																		font-size: 12px;" 
																	<? if ($date_task_mark->done_qty) echo "value='" . $date_task_mark->done_qty . "' "; ?>	/>
																<? else : ?>
																	<input type="checkbox" class="box-check weekly<? if ($checked) echo " pre-checked"; if ($showMechunachim) echo " mechunach_task"; ?>"
																		id="<?=$mid?>" 
																		value="<?=$date_task_mark->date_task_id;?>:<?=$pesukim_task->mark_date;?>:<?=$mechunach_id ?? ''?>"
																		<? if (
																			(!$showMechunachim && $checked) || 
																			($showMechunachim && $checked && isset($mechunach_id) && $date_task_mark->mechunach_id == $mechunach_id)
																		) echo "checked" ?> <? if ($pesukim_task->disable) echo "checked disabled" ?> />
																	<!--<span class="circle"></span>-->
																	<span class="check"></span>
																	<span class="box"></span>
																	<? 
																	if ($showMechunachim) {
																		if (! in_array($pesukim_task->date_task_id, $mechunachim_tasks)) {
																			$mechunachim_tasks[] = $pesukim_task->date_task_id;
																		}
																	}
																	?>
																<? endif; ?>
															</div>
															<div class="short">
																<? if ($pesukim_task->mandatory_qty) echo "<span class='mandStar'>*</span>"; ?>
																<?=($pesukim_task->short_name == '' ? '<br />' : $pesukim_task->short_name)?>
															</div>
															<div class="long">
																<?=$pesukim_task->task_name?>
															</div>
														</label>
													</div>
												</li>
											<?
											}
										}
										?>
									</ul>
									<?php endif; ?>
								</div>
							</div>
						</div>
						<?
					}
					$i++;
                }

				if ($desktop) {
					$style = 'display: none;';
				} else {
					$style = 'display: block;';
				}

				$label = $user_registered ? 'Mechunachim' : 'Students';
				?>
				<div class='panel panel-default'>
					<div class='panel-heading'>
						<i class='glyphicon glyphicon-chevron-left'></i> <?=$label?>
					</div>
					<div class='collapse in mechunachPanel' style='height: auto;'>
						<div class='panel-body'>
							<!-- add link that drops down when clicked to add a new mechunach -->
							<?php if ($desktop) echo "<a href='#' onclick='toggleAddMechunachForm(); return false;'>Add New</a>"; ?>
							<style>
								#addMechunachForm input {
									padding: 5px;
									margin: 5px;
								}
							</style>
							<div>
								<form name='addMechunachForm' id='addMechunachForm' action='addMechunach.php' method='post' style='<?=$style?>'>
									<input type='text' name='name' placeholder='Name' id='mechunachName' />
									<input type='tel' name='phone' placeholder='Phone' id='mechunachPhone' />
									<input type='email' name='email' placeholder='Email' id='mechunachEmail' />
									<input type='hidden' name='user_id' value='<?=$user_id?>' />
									<?php if (! $desktop) echo "<br />"; ?>
									<button type='submit' onclick='submitAddMechunachForm(this); return false;' style='margin-top: 10px;'>Add New</button>
								</form>
							</div>
							<br />
							<table class='table table-bordered' id='mechunachimTable'>
								<thead>
									<tr>
										<th>Name</th>
										<th>Verified</th>
										<th>Code / Date Verified</th>
									</tr>
								</thead>
								<tbody>
									
								</tbody>
							</table>
							<br />
							<br />
						</div>
					</div>
				</div> 

				<style>
					#recruits ul {
						list-style-type: none;
						padding-left: 0;
					}
					#recruits ul li {
						margin-bottom: 10px;
					}
				</style>
				<div class='panel panel-default'>
					<div class='panel-heading'>
						<i class='glyphicon glyphicon-chevron-left'></i> Recruits
					</div>
					<div class='collapse in mechunachPanel' style='height: auto;'>
						<div class='panel-body'>
							<?php if ($user_registered) : ?>
								<!-- add link that drops down when clicked to add a new mechunach -->
								<?php if ($desktop) echo "<a href='#' onclick='toggleAddRecruitForm(); return false;'>Add recruit for Duch</a>"; ?>
								<style>
									#addRecruitForm input {
										padding: 5px;
										margin: 5px;
									}
								</style>
								<div>
									<form name='addRecruitForm' id='addRecruitForm' action='addRecruit.php' method='post' style='<?=$style?>'>
										<input type='text' name='name' placeholder='Name' id='recruitName' />
										<input type='text' name='mothers_name' placeholder='Mothers Name' id='mothersName' />
										<input type='hidden' name='user_id' value='<?=$user_id?>' />
										<?php if (! $desktop) echo "<br />"; ?>
										<button type='submit' onclick='submitAddRecruitForm(this); return false;' style='margin-top: 10px;'>Add recruit</button>
									</form>
								</div>
								<br />
							<?php endif; ?>
							<div id='recruits'></div>
						</div>
					</div>
				</div> 
				<?php 
			}
		?>

<?php if (isset($_COOKIE['naftoli'])) : ?>
<div id="audioModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background:white; padding:20px; border-radius:8px; box-shadow:0 4px 6px rgba(0,0,0,0.3); z-index:1000;">
    <h3>Audio Player</h3>
    <audio id="audioPlayer" controls style="width:300px;">
        <source type="audio/mpeg">
        Your browser does not support the audio element.
    </audio>
    <br><br>
    <button onclick="closeAudioPlayer()">Close</button>
</div>
<div id="overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999;" onclick="closeAudioPlayer()"></div>
<?php endif; ?>

<script>
	$(document).ready(function() {
		async function init() {
			await getMechunachim(false);
			await checkMechunachimTasks();
			await getRecruits();
		}
		init();
	});
	var mechunachim = [];
	var mechunachim_tasks = <?= isset($mechunachim_tasks) ? json_encode($mechunachim_tasks) : '[]'; ?>;

	async function getMechunachim(refresh = true) {
		if (!refresh && mechunachim && mechunachim.length > 0) {
			displayMechunachim();
			return;
		}
		await $.ajax({
			url: '/pesukim/ajax/getMechunachim.php',
			type: 'POST',
			data: { user_id: <?=$user_id?> },
			success: function(response) {
				const res = JSON.parse(response);
				if (res.success) {
					mechunachim = res.data;
					displayMechunachim();
				} else {
					alert('Error getting mechunachim.');
				}
			},
			error: function(response) {
				alert('Error getting mechunachim: ' + response.responseText);
			}
		});
	}

	function displayMechunachim() {
		let html = '';
		$.each(mechunachim, function(index, mechunach) {
			if (! parseInt(mechunach.verified)) {
				html += "<tr><td>" + mechunach.name + "</td><td>Not Verified</td><td>" +
							"<input type='text' name='verification_code' class='verificationCode' placeholder='Code' style='width: 75px; margin-right: 10px;' />" +
							"<button type='submit' data-user_id='" + mechunach.mechanech_user_id + "' data-id='" + mechunach.mechunach_id + "' onclick='verifyMechunach(this); return false;'>Verify</button>" +
							"</td></tr>";
			} else {
				html += "<tr><td>" + mechunach.name + "</td><td>Verified</td><td>" + mechunach.date_verified + "</td></tr>";
			}
		});
		$('#mechunachimTable tbody').empty().append(html);
	}

	function toggleAddMechunachForm() {
		if (document.getElementById('addMechunachForm').style.display == 'block') {
			document.getElementById('addMechunachForm').style.display = 'none';
		} else {
			document.getElementById('addMechunachForm').style.display = 'block';
		}
	}

	function submitAddMechunachForm(button) {
		if (! validateMechunachForm()) return false;
		// check what the button says 
		button.disabled = true;
		button.innerHTML = 'Adding...';
		$.ajax({
			url: '/pesukim/ajax/addMechunach.php',
			type: 'POST',
			data: $('#addMechunachForm').serialize(),
			success: function(response) {
				const res = JSON.parse(response);
				button.disabled = false;
				if (res.success) {
					alert('Mechunach added successfully. You will receive a verification code via email.');
					button.innerHTML = 'Add';
					getMechunachim();
				} else {
					alert('Error adding mechunach.');
					button.innerHTML = 'Add';
				}
			},
			error: function(response) {
				button.disabled = false;
				button.innerHTML = 'Add';
				alert('Error adding mechunach: ' + response.responseText);
			}
		});
	}

	function validateMechunachForm() {
		const name = document.getElementById('mechunachName').value.trim();
		const phone = document.getElementById('mechunachPhone').value.trim();
		const email = document.getElementById('mechunachEmail').value.trim();

		if (name.length < 2) {
			alert('Please enter a valid name (at least 2 characters)');
			return false;
		}

		// Accepts numbers with or without +, with 10-15 digits (including country code)
		const phonePattern = /^(\+?\d{1,3}[- ]?)?\d{10,15}$/;
		if (!phonePattern.test(phone)) {
			alert('Please enter a valid phone number');
			return false;
		}

		const emailPattern = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;
		if (!emailPattern.test(email)) {
			alert('Please enter a valid email address');
			return false;
		}
		return true;
	}

	function verifyMechunach(elem) {
		const id = elem.dataset.id;
		const user_id = elem.dataset.user_id;
		const code = $(elem).parent().find('.verificationCode').val();
		// encode the code
		const encodedCode = encodeURIComponent(code);
		$.ajax({
			url: '/pesukim/ajax/verifyMechunach.php',
			type: 'POST',
			data: { id: id, user_id: user_id, code: encodedCode },
			success: function(response) {
				const res = JSON.parse(response);
				if (res.success) {
					alert('Mechunach verified successfully.');
					// reload the page
					location.reload();
				} else {
					alert('Error verifying mechunach.');
					// location.reload();
				}
			},
			error: function(response) {
				alert('Error verifying mechunach: ' + response.responseText);
				// location.reload();
			}
		});
	} 

	async function checkMechunachimTasks() {
		const user_id = <?=$user_id?>;
		const start = <?=$start?>;
		const end = <?=$end?>;
		
		// check if this task was done at some point for this mechunach
		await $.ajax({
			url: '/pesukim/ajax/checkMechunachimTasks.php',
			type: 'POST',
			data: { user_id, mechunachim_tasks: JSON.stringify(mechunachim_tasks), mechunachim: JSON.stringify(mechunachim) },
			success: function(response) {
				const res = JSON.parse(response);
				if (res.success) {
					// loop through the tasks and check if they were done for this mechunach
					const tasks = res.data;
					for (const task_id in tasks) {
						for (const mechunach_id in tasks[task_id]) {
							const elem = $('#' + task_id + '_' + mechunach_id);
							if (elem) {
								const date_marked = parseInt(tasks[task_id][mechunach_id]);
								if (date_marked) {
									elem.attr('checked', true);
									if (date_marked > parseInt(end) || date_marked < parseInt(start)) {
										elem.attr('disabled', true);
									}
								} 
							}
						}
					}
				}
			},
			error: function(error) {
				console.log(error)
			}
		})
	}

	async function getRecruits() {
		await $.ajax({
			url: '/pesukim/ajax/getRecruits.php',
			type: 'POST',
			data: { user_id: <?=$user_id?> },
			success: function(response) {
				const res = JSON.parse(response);
				if (res.success) {
					displayRecruits(res.data, res.duch_data);
				} else {
					alert('Error getting recruits.');
				}
			},
			error: function(response) {
				alert('Error getting recruits: ' + response.responseText);
			}
		})
	}

	function displayRecruits(recruits, duch_recruits) {
		let recruits_html = '<ul>';
		$.each(recruits, function(index, recruit) {
			recruits_html += "<li>" + recruit.first + " " + recruit.last;
			if (recruit.mothers_name) {
				let gender = recruit.gender == 'M' ? 'ben' : 'bas';
				recruits_html += " (" + recruit.first + " " + gender + " " + recruit.mothers_name + ") ";
			}
			recruits_html += " 60 Miles earned</li>";
		});
		$.each(duch_recruits, function(index, recruit) {
			recruits_html += "<li>" + recruit.name + " bas " + recruit.mothers_name + " (For Duch)</li>";
		});
		recruits_html += '</ul>';
		$('#recruits').html(recruits_html);
	}

	function toggleAddRecruitForm() {
		if (document.getElementById('addRecruitForm').style.display == 'block') {
			document.getElementById('addRecruitForm').style.display = 'none';
		} else {
			document.getElementById('addRecruitForm').style.display = 'block';
		}
	}

	function submitAddRecruitForm(button) {
		if (! validateRecruitForm()) return false;
		button.disabled = true;
		button.innerHTML = 'Adding...';
		$.ajax({
			url: '/pesukim/ajax/addDuchRecruit.php',
			type: 'POST',
			data: $('#addRecruitForm').serialize(),
			success: function(response) {
				const res = JSON.parse(response);
				button.disabled = false;
				if (res.success) {
					alert('Recruit added successfully.');
					button.innerHTML = 'Add';
					getRecruits();
				}
			},
			error: function(response) {
				button.disabled = false;
				button.innerHTML = 'Add';
				alert('Error adding recruit: ' + response.responseText);
			}
		});
	}

	function validateRecruitForm() {
		const name = document.getElementById('recruitName').value.trim();
		const mothers_name = document.getElementById('mothersName').value.trim();
		if (name.length < 2) {
			alert('Please enter a valid name (at least 2 characters)');
			return false;
		}
		if (mothers_name.length < 2) {
			alert('Please enter a valid mother name (at least 2 characters)');
			return false;
		}
		if (name == mothers_name) {
			alert('Name and mother name cannot be the same');
			return false;
		}
		return true;
	}

	function showAudioPlayer(e) {
		var audioFile = '/chidonOld/limud/audio_links/' + e.dataset.audio + '.mp3';
		document.getElementById('audioModal').style.display = 'block';
		document.getElementById('overlay').style.display = 'block';
		document.getElementById('audioPlayer').src = audioFile;
		document.getElementById('audioPlayer').play();
	}
	
	function closeAudioPlayer() {
		document.getElementById('audioModal').style.display = 'none';
		document.getElementById('overlay').style.display = 'none';
		document.getElementById('audioPlayer').pause();
	}
</script>
            
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
                if (unixtojd($timestamp) == unixtojd()) {
                    break;
                }
            }
            ?>
            
        </div>
    </div>
<!-- <?php //} ?> -->

<div class="footer-spacer"></div>

<div style="position: fixed;width: 100%;bottom:0px; z-index: 1000; background:#4258a2; display:flex;">
    <div class="span12 footer">
		<div class="span3" id="mainLink">
			<? 
			if (isset($_GET['app'])) echo "<a href='/reg/parent_detail.html'>"; 
			else echo "<a href='".$mobileURL."reg/parent_detail.html'>";
			?>
                <div class="menu-item">
                    <div class="span12">
                        <img src="img_new/boy-color-white.svg">
                    </div>
                    <div class="span12">
                       <span class="i18n" data-key="Accounts">Accounts</span>
                    </div>
                </div>
			</a>
        </div>
        <div class="span3 active">
			<a href="#" id="missionsLink">
			<div class="menu-item">
				<div class="span12">
					<img src="img_new/square-check-color-white.svg">
				</div>
				<div class="span12">
					<span class="i18n" data-key="Missions">Missions</span>
				</div>
			</div>
			</a>
		</div>
		<div class="span3">
			<a href="#" id="rankLink">
			<div class="menu-item">
				<div class="span12">
					<img src="img_new/achievements-color-white.svg">
				</div>
				<div class="span12">
					<span class="i18n" data-key="Achievements">Achievements</span>
				</div>
			</div>
			</a>
		</div>
		<div class="span3">
			<a href="#" id="storeLink">
			<div class="menu-item">
				<div class="span12">
					<img src="img_new/cart-color-white.svg">
				</div>
				<div class="span12">
					  <span class="i18n" data-key="Rewards">Rewards</span>
				</div>
			</div>
			</a>
		</div>
    </div>
</div>

<?php include ("inc/modals/updateMedalRanks.php"); ?>

<style>
	.slick-prev, .slick-next {
		color: #1b2b51;
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
	}
	textarea {
		width: 100%;
		height: 150px;
		padding: 12px 20px;
		box-sizing: border-box;
		border: 2px solid #ccc;
		border-radius: 4px;
		background-color: #f8f8f8;
		resize: none;
	}
	.footer-spacer {
		margin-top: 93.66px;
	}
	@media screen and (max-width: 639px) {
		.footer-spacer {
			margin-top: 63.66px;
		}
	}
</style>

<script src="/mobile/js/js.cookie.js"></script>
<script src="/js/utils/browser_detect.js"></script>
<script>
	/******************* DATE SLIDER *******************/
	var currentSlide = <?=$jd-$start?>;
	var bSlid = localStorage.getItem('achos-missions-slid');
	
	/******************* SCREEN SIZE *******************/
	var screenSize = $(".container").width();

	// var showPoll = <?//=$show?>;
	// if ( showPoll ) $("#hachayolPoll").modal('show');

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
		Cookies.set('lang', lang)
		
		$(".panel:not(.mishnaPanel)").addClass('open');
		$(".panel:not(.mishnaPanel) .collapse").addClass('in');
		// start the page with each dropdown expanded
		$.each($(".collapse.in:not(.mechunachPanel)"), function(index, item){
			item = $(item);
			var multiplier = parseInt(screenSize) < 790 && item.find(".dailyPanel").length > 0 ? 1.5 : 1.2; // add some (50%) initial spacing for the daily/tall ones
			if (browser_detect() != "Chrome" && browser_detect() != "Safari") { // multiplier only used in chrome. other browsers do not have the height bug....
                multiplier = 1;
            }
			//console.log(multiplier, screenSize, item.find(".dailyPanel").length > 0);
			item.css({"height": item.children()[0].scrollHeight * multiplier});
		});
		
		// Prevent panel-body clicks from bubbling to panel-heading
		$(".panel-body").on('click touchstart', function(e) {
			e.stopPropagation();
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

		var CheckALLText = "Check All";
		var unCheckALLText = "Uncheck All";
		 if (localStorage.getItem("locallang") == "he") {
			  CheckALLText = "סמן הכל";
			 unCheckALLText = "הסר סימון";
		 //
		 }
		// alert($(this).val() );
		// alert(CheckALLText);
			$(this).width(65);
			if ($(this).val() == CheckALLText ) {
				$(this).val(unCheckALLText);
				var inputs = $(this).parent().parent().find('.box-check');
				var l = inputs.length;
				for (var i = 0; i < l; i++) {
					var input = inputs[i];
					if (!$(input).is(":checked")) {
						$(input).trigger('click');
					}
				}
			} else if ($(this).val() == unCheckALLText) {
				$(this).val(CheckALLText);
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
		var CheckALLText = "Check All";
		var unCheckALLText = "Uncheck All";
		 if (localStorage.getItem("locallang") == "he") {
			  CheckALLText = "סמן הכל";
			 unCheckALLText = "הסר סימון";
		 //
		 }
			$(this).width(65);
			if ($(this).val() == CheckALLText) {
				$(this).val(unCheckALLText);
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
			} else if ($(this).val() == unCheckALLText) {
				$(this).val(CheckALLText);
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
			var info = value.split(':');
			var date_task_id = info[0];
			var mark_date = info[1];
			let mechunach_id = undefined;
			if (info.length > 2) {
				mechunach_id = info[2];
			}
			        			
			var parameters = [user_id, date_task_id, mark_date, mechunach_id];
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
					if (success == false) {
						alert("Update not performed.");
					} else {
						updateMedalRanks.update(user_id);
						$(span).empty();
						var html = "<span class='dMarkID' id='" + task_id + ':' + mark_date;
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
					// return;
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
						//var today = <?=unixtojd()?>;
						var start = <?=$start?>;
						var end = start + 6;
						if (currentSlide == 0 && d.currentSlide == 0) {
							//only allow going back up to three weeks
							//if ((today - start) < 28) {
								//reload page with mission dates of the previous week
								var url = "..<?=$mobileURL?>missionsNew.html?id=" + id + "&d=" + (--start);
								window.location.href = url;
							//}						
						} else if (currentSlide == 6 && d.currentSlide == 6) {
							//reload page with mission dates of the next week					
							var url = "..<?=$mobileURL?>missionsNew.html?id=" + id + "&d=" + (++end) + "&s=1";
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
			if (Cookies.get('lang') === 'he') $(".showProgress").val('צפייה לפי יום');
            else $(".showProgress").val('Daily View');
			$(".showProgress").width( w );
		} else if (Cookies.get('marking') == 'daily') {
		    if (Cookies.get('lang') === 'he') $(".showProgress").val('צפייה לפי שבוע');
            else $(".showProgress").val('Weekly View');
		}

		
		$(".showProgress").click( function() {
			if ($(this).val() == 'Weekly View' || $(this).val() == 'צפייה לפי שבוע') {
				Cookies.set('marking', 'weekly');
			} else if ($(this).val() == 'Daily View' || $(this).val() == 'צפייה לפי יום') {
				Cookies.set('marking', 'daily');
			}
			location.reload();
			//$(this).parent().parent().find('.dInfo').toggle();
			//$(this).parent().parent().find('.dMark').toggle();
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
