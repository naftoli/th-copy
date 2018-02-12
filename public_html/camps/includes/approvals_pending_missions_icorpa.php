<?php
DEFINE("BASE", "/home/mashpia/public_html/kiosk/campaigns/tanya");
DEFINE("BASE_URI", "");

$VERBOSE = 0; // classes
$DEBUG = 0; // classical content
require_once("/home/mashpia/public_html/kiosk/campaigns/tanya/config.php");
require_once("/home/mashpia/public_html/kiosk/campaigns/classes/class.DBI.php");
require_once(BASE . "/source/class.Tanya.php");
$objDBIHandle = new DBI($VERBOSE);
$objDBIHandle->open();
mysql_select_db("mashpia", $objDBIHandle->objHandle);
$intCurrentTime = time();
$intActionCount = 0;
if (isset($_POST["arrItems"]))
{
	if ($DEBUG)
		print "Post started <br>\n";
	unset($_POST["arrItems"]);
	if (!(
		is_array($_POST)
		&& count($_POST)
	)) {
		print "Sorry, there was an error: CT-TLA101-APMI-DHFG54";
		exit;
	}
	foreach ($_POST as $intUser => $intTasksToMark)
	{
		$intUser = preg_replace("/^item_/", "", $intUser);
		if (
			!preg_match("/^[0-9]+$/", $intTasksToMark)
			|| !$intTasksToMark
		) {
			continue;
		}
		if ($intTasksToMark >= 417)
		{
			print "Sorry, there was an error: CT-TLA102-APMI-BV2N1G";
			exit;
		}
	if ($DEBUG)
		print "Loading mashpia data <br>\n";
		$strSql = "
			SELECT
				user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2, dob,
				user_city, user_state, user_postal, user_country, user_phone,
				user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id,
				team_name, school_name, school_number, school_city, school_state, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, rank_name, rank_image_id, rank_color
			FROM
				users
				LEFT JOIN schools USING (school_id)
				LEFT JOIN institutions USING (inst_id)
				LEFT JOIN classes USING (school_id, class_id)
				LEFT JOIN teams USING (school_id, team_id)
				LEFT JOIN (SELECT user_id, MAX(rank_ord) rank_ord FROM rank_marks WHERE user_id = {$intUser} GROUP BY user_id) rank USING (user_id)
				LEFT JOIN ranks USING (rank_ord)
			WHERE
				user_id = {$intUser}
			ORDER BY
				class_grade, class_sub, last, first";
		$objDBIHandle->open();
		mysql_select_db("mashpia", $objDBIHandle->objHandle);
		$objResult = $objDBIHandle->query($strSql);
		if (!$objResult)
		{
			print "Sorry there was an error: tanyaci-apmi101-23n234";
			exit;
		}
		$user_row = mysql_fetch_assoc($objResult);
		$objTanya = new Tanya($VERBOSE);
		if ($DEBUG)
			print "Loading tanya user <br>\n";
		$objTanya->loadUser();
		//print "Tanya user: " . $objTanya->objUserHandle->intTableID . "<br>\n";
		$intTasksToMark = ($intTasksToMark - ($objTanya->objUserHandle->intLinesAfterEnrollment + $objTanya->objUserHandle->intLinesBeforeEnrollment)) . " <br>\n";
		if (!$intTasksToMark)
		{
			print "Not enough tasks <br>\n";
			continue;
		}
		if ($DEBUG)
			print "Loading missions <br>\n";
		$objMissions = new TanyaMissions($objTanya->objUserHandle, $VERBOSE);
		$arrMissionRanges = $objMissions->missionEntryRange(416, 0); // 4 is the most amount possible to group by
		$arrMissionRanges = $objMissions->missionEntryRangeStyle3($arrMissionRanges["design"]);
		$intLineCount = 0;
		$objDBIHandle->open();
		mysql_select_db("mashpia", $objDBIHandle->objHandle);
		foreach ($arrMissionRanges as $intKey => $arrMissions)
		{
			$arrRange = current($arrMissions);
			$strSql = "
				INSERT
					INTO " . tanya_missions_table . "
					(user_id, mission_number, tested, tested_date, ladder, `real`, sum, virtual_sum, date_created)
				VALUES
			";
			$arrSql = array();
			$intVirtual = 0;
			//var_dump($arrMissions);//exit;
			foreach ($arrMissions as $intItr => $arrRange)
			{
				if ($intTasksToMark-1 < $intLineCount) break;
				if ($arrRange["mission_dates"][0] > $intCurrentTime) break;

				$arrSql[] = "({$intUser}, $intItr, 1, UNIX_TIMESTAMP(), "
					. $objTanya->objUserHandle->intLadder
					. ", " . $arrRange["real"]
					. ", " . $arrRange["sum"]
					. ", " . $arrRange["virtual_sum"]
					. ", " . $intCurrentTime
					. ")";
				$intVirtual = $arrRange["virtual_sum"];
			}
			$strSql = $strSql . join(",", $arrSql) . "
				ON DUPLICATE KEY UPDATE
					tested=1,
					tested_date=VALUES(tested_date),
					ladder=VALUES(ladder),
					`real`=VALUES(`real`),
					sum=VALUES(sum),
					virtual_sum=VALUES(virtual_sum);
			";
			if ($DEBUG)
				print "Inserting " . count($arrSql) . " mission rows<br>\n";
			if (count($arrSql))
			{
				$objDBIHandle->query($strSql);
				$intLineCount+=1;
			}
		}
		if ($DEBUG)
			print "Line count: " . $intLineCount . " <br>\n";
		if ($intLineCount)
		{
			$strSql = "
				UPDATE
					" . tanya_user_table . "
				SET
					lines_after_enrollment = lines_after_enrollment + " . $intLineCount . "
				WHERE
					id = " . $objTanya->objUserHandle->intTableID;
			$objDBIHandle->open();
			mysql_select_db("mashpia", $objDBIHandle->objHandle);
			$objDBIHandle->query($strSql);
			$intActionCount++;
		}
	}
	print $intActionCount;
	exit;
}
?>
 <script>
	 $(document).ready(function() {
		$("#appoval_save").click(
			function(event) {
				var strUrl = "/tanya/tanyalist/approve/true";
				$.ajax(
				{
					type : "POST",
					cache : false,
					start: $("#processing_status_approvals").html('Loading...'),
					url : strUrl,
					dataType : "text",
					data: $('#form_approvals').serialize(),
					success : function(strResult)
					{
						if (strResult.match(/[0-9]+/)) {
							$("#processing_status_approvals").html('Marked ' + strResult + ' ' + (strResult > 1 ? "children" : "child") + ' sucessfully.').fadeIn("slow").delay(2000).fadeOut("slow");
						} else {
							$("#processing_status_approvals").html(strResult).fadeIn("slow");
						}
					}
				});
			}
		);
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

.marking {
	color:#333333;
	font-size:14px;
	font-weight:bold;
	line-height:14px;
	font-family:"Myriad Pro",Arial,Helvetica,sans-serif;
}
.marking .row:first-child {
	border-top:medium none;
}
.marking .row:last-child {
	border-bottom:medium none;
}
.marking .row.top_row {
	font-size:18px;
	text-align:center;
}
.marking.tanya_mark .row {
	clear:both;
	font-size:14px;
	min-height:30px;
	height:auto;
}

.marking .row.top_row .cell {
	min-height:30px;
	padding-top:10px;
}
.marking.tanya_mark .row.top_row .cell {
	min-height:0;
	height:auto;
	text-align:left;
}
.marking.tanya_mark .cell {
	float:left;
	margin-left:10px;
	width:100px;
	border:none;
	height:auto;
}
.marking.tanya_mark .cell.name {
	width:180px;
}
.marking.tanya_mark .cell.lines {
	width:80px;
}
.marking.tanya_mark .row:not(.top_row) > .cell {
	font-weight:normal;
	min-height:0;
	padding:6px 0;
}
.marking.tanya_mark .cell input {
	background:none repeat scroll 0 0 #EEEEEE;
	border:1px solid #BBBBBB;
	margin:-2px 0;
}
.marking .row:after {
	visibility: hidden;
	display: block;
	font-size: 0;
	content: " ";
	clear: both;
	height: 0;
}
* .marking .cell              { zoom: 1; } /* IE6 */
*:first-child+html .marking .cell   { zoom: 1; } /* IE7 */

-->
</style>
<div class="slider">
	<div class="col_title">
		<span>Marking Tanya</span>
	</div>
	<div class="col_content">
<?
$strSql = "
	SELECT
		*
	FROM
		" . tanya_user_table . ",
		mashpia.users
	WHERE
		" . tanya_user_table . ".id IN (" . $_POST["user_ids"] . ")
		AND " . tanya_user_table . ".id = mashpia.users.user_id
";
//print $strSql;
$objDBIHandle->open();
$objDBIHandle->query("SET NAMES 'utf8'");
$objResult = mysql_query($strSql, $objDBIHandle->objHandle);
?>
		<div class="body left marking_missions">
			<form id="form_approvals">
				<DIV id="tasks_div">
					<div class="marking tanya_mark module">
						<div class="row top_row">
							<div class="cell name">Student</div>
							<div class="cell">Weekly Quota</div>
							<div class="cell">Completed</div>
							<div class="cell lines">Lines Due</div>
							<div class="cell">Status</div>
							<div class="cell">Tested</div>
						</div>
<?
	$boolNoTasks = 0;
	if (@mysql_num_rows($objResult))
	{
		$arrComplete = array();
		while ($objRow = mysql_fetch_assoc($objResult)) {
			$intUser = $objRow["id"];
			if ($arrComplete[$intUser])
				continue;
			$arrComplete[$intUser] = 1;
			$strSql = "
				SELECT
					user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2, dob,
					user_city, user_state, user_postal, user_country, user_phone,
					user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id,
					team_name, school_name, school_number, school_city, school_state, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, rank_name, rank_image_id, rank_color
				FROM
					users
					LEFT JOIN schools USING (school_id)
					LEFT JOIN institutions USING (inst_id)
					LEFT JOIN classes USING (school_id, class_id)
					LEFT JOIN teams USING (school_id, team_id)
					LEFT JOIN (SELECT user_id, MAX(rank_ord) rank_ord FROM rank_marks WHERE user_id = {$intUser} GROUP BY user_id) rank USING (user_id)
					LEFT JOIN ranks USING (rank_ord)
				WHERE
					user_id = {$intUser}
				ORDER BY
					class_grade, class_sub, last, first";
			$objDBIHandle->open();
			mysql_select_db("mashpia", $objDBIHandle->objHandle);
			$objResult2 = mysql_query($strSql, $objDBIHandle->objHandle) or die(mysql_error());
			$user_row = mysql_fetch_assoc($objResult2);
			$objTanya = new Tanya($VERBOSE);
			$objTanya->loadUser();
			$objMissions = new TanyaMissions($objTanya->objUserHandle, $VERBOSE);
			$intReal = $objMissions->procLinesPerMission();
			// Load all possible missions
			$arrMissionRanges = $objMissions->missionEntryRange(416, 0);
			if ($DEBUG)
				print "Missions loaded <br>\n";
			$arrCurrent = current($arrMissionRanges["design"]);
			$intStartLine = $arrCurrent["line_start"];
			$intCurrentTime = time();
			reset($arrMissionRanges["design"]);
			$boolNoTasks = 1;
			$intLine = $objRow["lines_after_enrollment"] + 1;
			$intLadderLines = number_format($objMissions->ladderLines($objTanya->objUserHandle->intLadder)/416, 2);
			$intMissions =
			floor(
				(
					($intCurrentTime - $objTanya->objUserHandle->intEnrolledDate)
					- ($objRow["lines_after_enrollment"] * real_week2)
				)
				/ (real_week2 * 86400)
			) + $objRow["lines_after_enrollment"];
			$intEndLine = ceil($intMissions * $intLadderLines);
			$intWeeksBehind = ceil($intLine * (1/$intLadderLines));
			$intMissionsBehind = 0;
			foreach ($arrMissionRanges["design"] as $arrRange)
			{
				if ($arrRange["mission_dates"][0] < $intCurrentTime)
				{
					$intMissionsBehind++;
					$intCurrentLine = $arrRange["line_start"];
				}
				else
					break;
			}
			if ($DEBUG)
				print "Missions behind: " . $intMissionsBehind . " <br>\n";
			$intLinesDue = $intCurrentLine - $intStartLine;
			
			
			
?>
						<div class="row">
							<!-- name -->
							<div class="cell name"><? print ($objRow["first_he"] ? $objRow["first"] : $objRow["first"]) . " " . ($objRow["last_he"] ? $objRow["last"] : $objRow["last"]); ?></div>
							<!-- weekly quota -->
							<div class="cell"><? print $intLadderLines; ?> lines</div>
							<!-- completed -->
							<div class="cell"><? print ($objRow["lines_before_enrollment"] + $objRow["lines_after_enrollment"]); ?> lines</div>
							<!-- lines due -->
							<div class="cell lines"><?
			if ($intMissionsBehind)
			{
				print $intStartLine . " - " . $intCurrentLine;
			}
			else
			{
				print "On task";
			}
			?></div>
							<!-- status -->
							<div class="cell"><? print $intMissionsBehind; ?> weeks behind</div>
							<!-- tested -->
							<div class="cell"><?
			if ($intMissionsBehind)
			{
				print ($objRow["lines_before_enrollment"] + $objRow["lines_after_enrollment"] + 1); ?> - <input type="text" size="4" value="" name="item_<?=$objRow['id']?>"><?php
			}
			?></div>
						</div>
<?
		}
	}
	$objDBIHandle->close();
	if (!$boolNoTasks)
	{
?>
		There are no tasks to complete.
<?php
	}
?>
					</div>
				</div>
				<div style="clear:both; height:1px;"></div>
			</form>
			&nbsp;<br>
			<div id="processing_status_approvals"></div>
<?php
	if ($boolNoTasks)
	{
?>
			<span><a href="#" title="Save" class="button submit" id="appoval_save">Save</a></span>
<?php
	}
?>
		</div>
	</div> <!-- <div class="col_content"> -->
</div> <!-- <div class="slider"> -->