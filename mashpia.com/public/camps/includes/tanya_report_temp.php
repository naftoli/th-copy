<?php
DEFINE("BASE", "/home/mashpia/public_html/kiosk/campaigns/tanya");
DEFINE("BASE_URI", "");

$VERBOSE = 0;
require_once("/home/mashpia/public_html/kiosk/campaigns/tanya/config.php");
require_once("/home/mashpia/public_html/kiosk/campaigns/classes/class.DBI.php");
$objDBIHandle = new DBI($VERBOSE);
require_once(BASE . "/source/class.Tanya.php");
$intSheets = 0;
ob_start();
if (
	isset($_POST["users"])
) {
	$arrUsers = unserialize(stripslashes($_POST["users"]));
	if (
		is_array($arrUsers)
		&& count($arrUsers)
	) {
		foreach ($arrUsers as $intUser)
		{
			$strSql = "
				SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2, dob,
					   user_city, user_state, user_postal, user_country, user_phone,
					   user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id,
					   team_name, school_name, school_number, school_city, school_state, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, rank_name, rank_image_id, rank_color
				FROM users
					 LEFT JOIN schools USING (school_id)
					 LEFT JOIN institutions USING (inst_id)
					 LEFT JOIN classes USING (school_id, class_id)
					 LEFT JOIN teams USING (school_id, team_id)
					 LEFT JOIN (SELECT user_id, MAX(rank_ord) rank_ord FROM rank_marks WHERE user_id = {$intUser} GROUP BY user_id) rank USING (user_id)
					 LEFT JOIN ranks USING (rank_ord)
				WHERE
					user_id = {$intUser}
				ORDER BY
					class_grade, class_sub, last, first
			";
			$objDBIHandle->open();
			mysql_select_db("mashpia", $objDBIHandle->objHandle);
			$objResult = $objDBIHandle->query($strSql);
			$user_row = mysql_fetch_assoc($objResult);
			if (!$user_row["dob"])
			{
				// Must have a dob
				continue;
			}
			$objTanya = new Tanya($VERBOSE);
			$objTanya->loadUser();
			if (!$objTanya->objUserHandle->intEnrolled) {
				// User must be enrolled
				continue;
			}
			$intSheets++;
			$objMissions = new TanyaMissions($objTanya->objUserHandle, 0);
			
			// Tanya stuff
			$intLadder = $objTanya->objUserHandle->intLadder;
			$intLadderLines = $objMissions->ladderLines($intLadder);
			$intTaskSize = round($intLadderLines / 416, 2);
			$intMissionSize = ceil($intLadderLines / 8);
			$intLine = $intTaskSize; // First line
			$intDateToStart = $objTanya->objUserHandle->intEnrolledDate;
			$intLinesToCome = ceil($objMissions->procRemainingMissions() * round($intLadderLines / 416,2)) + $objTanya->objUserHandle->intLinesBeforeEnrollment + $objTanya->objUserHandle->intLinesAfterEnrollment;
?>
				<div class="print_header">
					<div class="marking module clearfix">
						<div class="rank_image"><img src="http://www.mashpia.com/file_view.php?id=<?=$user_row["rank_image_id"];?>" height="70" /></div>
						<div class="user_image"><img src="http://www.mashpia.com/file_view.php?id=<?=$user_row["user_photo_id"];?>" height="70" /></div>
						<p class="print_page">&#1489;"&#1492;</p>
						<p class="print_name"><?=$user_row["rank_name"];?> <?=$user_row["first_he"] ? $user_row["first_he"] : $user_row["first"];?> <?=$user_row["last_he"] ? $user_row["last_he"] : $user_row["last"];?></p>
						<p class="print_week">My Tanya Schedule for 5771</p>
						<p class="print_sig" style="float:center">Before I joined Tzivos hashem I knew <?=$objTanya->objUserHandle->intLinesBeforeEnrollment?> Lines. My weekly qouta is <?=$intTaskSize?> lines.</p>
						<p class="print_class" style="float:center">At this pace, I will learn <?=$intMissionSize?> lines each year. By my Bar/Bas Mitvza I will know <?=$intLinesToCome?> Lines.</p>
						<!--<p class="print_instructions">Fill out your mission sheet and review it with your commander, who will give you a campaign sticker for each mission you have completed.</p>-->
					</div>
				</div>
<?
			$intMaxDate = mktime(0,0,0,9,29,2011);
			$boolStop = 0;
			for ($intMedal = 0; $intMedal != 3; $intMedal++) {
?>
				<div class="tanya module clearfix">
					<div class="clearfix">
<?
				for ($intMission=$arrMedalData[$intMedal]+1; $intMission!=$arrMedalData[$intMedal+1]+1; $intMission++) {
					$intCurrentDate = $intDateToStart + ceil(($intMission-1) * 7.024038461538462 * 86400);
					if ($intMaxDate < $intCurrentDate)
					{
						$boolStop = 1;
						break;
					}
					$strDate = mb_convert_encoding( jdtojewish( unixtojd($intCurrentDate), true, CAL_JEWISH_ADD_GERESHAYIM + CAL_JEWISH_ADD_ALAFIM_GERESH), "UTF-8", "ISO-8859-8");
					//$strDate = date("M jS Y", $intCurrentDate);
?>
						<div class="mission">
							<div class="mission_number">#<?=$intMission?></div>
							<div class='mission_date'><?=$objTanya->epochToParshos($intCurrentDate);?></div>
							<div class="mission_lines">Lines 1-<?=ceil($intLine)?></div>
						</div>
<?
					$intLine += $intTaskSize;
				}
?>
					</div>
					<div class="tanya tanya_medal clearfix">
						<div class="box"><? print $arrMedalData[$intMedal+1] - $arrMedalData[$intMedal]; ?> Missions
						</div>
						<div class="box medal"><img src="http://www.mashpia.com/file_view.php?id=<? print $arrMedalData[$arrMedalData[$intMedal+1]]; ?>" width="72" height="75" />
						</div>
						<div class="box"><? print $arrMedalNames[$intMedal+1]; ?> Medal
						</div>
					</div>
				</div>
<?
				if ($boolStop)
				{
					break;
				}
			}
		}
	}
}
$strOut = ob_get_contents();
ob_end_clean();
?>
<script>
	$('.slider:last .list_expand li h3').nextAll().hide();
	$('.slider:last .list_expand li h3').click(function(){
		$(this).nextAll().slideToggle('fast');
		$(this).parents('li').toggleClass('open');
	});
</script>
<style>
<!--
.infobox2 {
	-moz-border-radius:10px 10px 10px 10px;
	-moz-box-shadow:1px 1px #FFFFFF;
	background-color:#EEEEEE;
	border:1px solid #BBBBBB;
	margin-bottom:30px;
	margin-right:25px;
	min-height:50px;
	padding:5px 10px;
}


.marking_list .select_box {
	float:left;
	margin:0 8px;
}
a.button {
	padding:6px 10px;
}
.marking_list .button.prev, .marking_list .button.next{
	display:block;
	float:left;
	margin:3px 4px;
	height:14px;
}
.marking_list .button.next {
	float:right;
}
.marking_list .button.prev .icon, .infobox2 .button.next .icon {
	float:left;
	height:14px;
	margin:0 -5px;
	width:14px;
}
.marking_list .button.prev .icon {
	background:url("http://www.mashpia.com/images/icon_control_left.png") no-repeat scroll 0 0 transparent;
}
.marking_list .button.next .icon {
	background:url("http://www.mashpia.com/images/icon_control_right.png") no-repeat scroll 0 0 transparent;
}
.marking_list .button .label {
	float:left;
	text-indent:-9999em;
}


.list_expand li:first-child {
	border-top:medium none;
}
.list_expand img {
	border:1px solid #BBBBBB;
	margin:2px;
}

.generate {
	padding:5px;
}
.generate h3 {
	line-height:1.6;
	margin-bottom:15px;
	text-align:center;
}


.generate .button {
	float:none;
	margin:3px 250px;
	text-align:center;
	line-height:1;
}
.marking_missions .print_header .marking {
	position:relative;
	font-weight:bold;
}
.marking_missions .print_header:not(.print_page_two) .marking {
	min-height:78px;
}
.marking_missions .print_header .rank_image {
	float:right;
	padding:4px;
	position:absolute;
	right:0;
}
.marking_missions .print_header .user_image {
	float:left;
	overflow:hidden;
	padding:4px 4px 4px 8px;
	position:absolute;
	text-align:center;
	width:80px;
}

.marking_missions .print_header div.module p, .marking_missions .print_footer div.module p {
	font-family:"Myriad Pro",Arial,Helvetica,sans-serif;
	margin:4px 10px;
	text-align:center;
}
.marking_missions .print_header:not(.print_page_two) .print_page {
	font-size:12px;
	position:absolute;
	right:95px;
}
.marking_missions .print_name {
	font-size:20px;
	margin-bottom:5px;
}
.marking_missions .print_week {
	font-size:18px;
}
.marking_missions .print_class {
	float:left;
	font-size:14px;
	margin-left:100px !important;
}
.marking_missions .print_sig {
	float:right;
	font-size:14px;
	margin-right:120px !important;
}


.tanya.module {
	-moz-box-shadow:none;
	background:none repeat scroll 0 0 #FFFFFF;
	border:1px solid #000000;
	margin-bottom:30px;
	padding:1px 8px 8px 1px;
	line-height:1;
}
.tanya .mission {
	-moz-border-radius:8px 8px 8px 8px;
	background:none repeat scroll 0 0 #FFFFFF;
	border:1px solid #000000;
	float:left;
	font-size:70%;
	height:59px;
	margin:7px 0 0 7px;
	text-align:center;
	width:59px;
}
.tanya .mission .mission_number {
	font-size:14px;
	font-weight:bold;
	margin-top:3px;
}
.tanya .mission .mission_date {
	margin:3px 0;
}
.tanya .mission .mission_lines {
	font-size:95%;
	font-weight:bold;
}
.tanya.tanya_medal {
	-moz-border-radius:8px 8px 8px 8px;
	background:none repeat scroll 0 0 #A8A8A8;
	margin:10px 0 0 7px;
	padding:0;
}
.tanya.tanya_medal .box {
	float:left;
	height:30px;
	margin-top:10px;
	text-align:center;
	width:45%;
}
.tanya.tanya_medal .medal {
	margin:-5px 0 15px;
	width:10%;
}

@media print {
	.header, .body_header_left, .body_header_right, .left_menu, .noprint, #nav, .col_title {
		display: none !important;
	}
	#content {
		overflow:visible !important;
		background:none !important;
		width:auto !important;
	}
	#wrapper, #content .slider, #content .slider_container, #content .col_content {
		float:none !important;
		width:auto !important;
		height:auto !important;
	}
	#content .col_content {
		padding:0 !important;
	}
	.col_title_bg {
		background:none !important;
		height:0 !important;
		margin:0 !important;
	}
	div.module {
		margin-right:0;
	}
	.slider_container  {
		margin-left:0 !important;
	}
	.slider_container .slider {
		display:none;
	}
	.slider_container .slider:last-child {
		display:block;
	}
}
-->
</style>
<div class="slider">
	<div class="col_title">
		<span>Print Tanya Schedules</span>
	</div>
	<div class="col_content">
		<div class="module_content marking_missions">
		<div class="noprint">
			<div class="module clearfix generate">
				<p>Generate Mission Sheets by filling in all the options above.</p>
			</div>
			<div class="module clearfix">
				<div class="list_expand">
					<ul>
						<li>
							<h3><span class="icon"></span>Print Instructions</h3>
							<p><img src="http://www.mashpia.com/images/Print-Dialog-Small-2.jpg" align="right" /><img src="http://www.mashpia.com/images/Print-Dialog-Small-1.jpg" align="right" />
								In your browser click 'File' then 'Page Setup...'</p>
								<p>Step 1: Set the Orientation to Portrait</p>
								<p>Step 2: Check 'Shrink to fit Page Width'</p>
								<p>Step 3: In Options check 'Print Background (colors & images)'</p>
								<p>Step 4: In the second tab set all Margins to 0.5 inches (All Sides)</p>
								<p>Step 5: Set all Headers & Footers to Blank</p>
								<p>Note: The browser will save these preferences for later use.</p>
						</li>
					</ul>
				</div>
			</div>
			<div class="module clearfix generate">
				<h3>Mission Sheets were generated<br/>for <?=$intSheets?> students.<br/>(<?=$intSheets?> Sheets)</h3>
				<p><a href="javascript:window.print()" class="button">Print</a></p>
			</div>
		</div>
			<DIV id="tasks_div">
				<?=$strOut?>
			</DIV>
		</div> <!-- <div class="module_content"> -->
	</div> <!-- <div class="col_content"> -->
</div> <!-- <div class="slider"> -->