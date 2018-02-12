<?
$agent = $_SERVER['HTTP_USER_AGENT'];
echo "<input type='hidden' name='user_agent' value='$agent'>";
$pattern = '/Firefox\/3.6/i';
//if not firefox 3.6 or using chrome
//if (!preg_match($pattern, $agent) || preg_match('/like Gecko/', $agent)) {
//if using chrome or explorer show error
if (preg_match('/like Gecko/', $agent) || preg_match('/MSIE/', $agent)) {
	echo "You cannot use our Kiosk unless you have Firefox 3.6. Click <a href='http://download.mozilla.org/?product=firefox-3.6.17&os=win&lang=en-US'>here</a> to download it.";
	exit;
}

require('header.php');
require_once('calendar.php');
require_once('file_save.php');
require_once('card_printer.php');

$url = $_SERVER["REQUEST_URI"];
//echo "<input type='hidden' name='URL' value='" . $url . "'>\n";

if (!isset($_COOKIE["user"])) {
	$user_row = mysql_fetch_assoc(mq("
	SELECT user_id, first, last, user_code, first_he, last_he, username, gender, user_address1, user_address2,
		   user_city, user_state, user_postal, user_country, user_phone, camp_registered, user_start_date, users.add_on_two, 
		   user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id, team_name,
		   school_name, school_number, school_store, school_city, school_state, school_makeup_id, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, kiosk_edit,
		   rank_ord, rank_name, rank_image_id, rank_color, users.camp_id, camp_number, camp_name
	FROM users
		 LEFT JOIN schools USING (school_id)
		 LEFT JOIN institutions USING (inst_id)
		 LEFT JOIN classes USING (school_id, class_id)
		 LEFT JOIN teams USING (school_id, team_id)
		 LEFT JOIN (SELECT user_id, MAX(rank_ord) rank_ord FROM rank_marks WHERE user_id = {$user['user_id']} GROUP BY user_id) rank USING (user_id)
		 LEFT JOIN ranks USING (rank_ord)
		 LEFT JOIN camps ON (users.camp_id=camps.camp_id)
	WHERE user_id = {$user['user_id']}
	ORDER BY class_grade, class_sub, last, first
	"));
}

$user_id = $user['user_id'];
$school_id = $user['school_id'];

$camp_season = 0;
$camp_id = $user_row['camp_id'];
$camp_registered = $user_row['camp_registered'];
/*
//update missions
require_once("classes/mission_marks_updater.php");
$mission_marks_updater = new mission_marks_updater();
$mission_marks_updater->mission_marks_update($user['user_id']); 

//update missions using user_track
require_once("classes/user_track.php");
require_once("classes/date_tasks_mission.php");
$qry = "select * from user_tracks where user_id = " . $user['user_id'];
$res = mysql_query($qry);
while ($track = mysql_fetch_assoc($res)) {
	$user_track = new user_track($track);
	$user_track->set_school_type_id();
	$user_track->update_mission();
}
require_once("classes/missions_updater.php");
$mu = new mission_updater();
$tasks = "select distinct date_tasks_mission_id from date_tasks_missions";
$result = mysql_query($tasks);
while ($task = mysql_fetch_assoc($result)) {
	$mu->mission_update($user['user_id'], $task);
}
*/
//update medals
include("classes/medal_updater.php");
$medal_updater = new medal_updater();
$medal_updater->update_medal($user['user_id']);
//update ranks
include("classes/rank_updater.php");
$rank_updater = new rank_updater();
$rank_updater->update_rank($user['user_id']);
/*
$qry = "select date_tasks_mission_id from date_tasks_mission_marks where mission_name = ''";
$res = mysql_query($qry);
$num = mysql_num_rows($res);
echo $num;
while ($r = mysql_fetch_row($res)) {
	$id = $r[0];
	$dtm = "select mission_name from date_tasks_missions where date_tasks_mission_id = " . $id;
	$dtm_res = mysql_query($dtm) or die(mysql_error());
	$dtm_row = mysql_fetch_row($dtm_res);
	$fix = "update date_tasks_mission_marks set mission_name = \"" . mysql_real_escape_string($dtm_row[0]) . "\" where date_tasks_mission_id = " . $id;
	//echo $fix;
	mysql_query($fix) or die(mysql_error());
}
*/
exit;

if ($camp_id > 0 && $camp_registered > 0) {
	$todays_date = get_todays_julian_date();
	$row = mysql_fetch_assoc(mysql_query("SELECT start_date, end_date FROM camps WHERE camp_id=" . $camp_id));
	$camp_start_date = $row['start_date'];
	$camp_end_date = $row['end_date'];
	if ($todays_date >= $camp_start_date && $todays_date <= $camp_end_date)
		$camp_season = 1;
	setcookie("camper_id", $user['user_id'], time()+3600);
}


$user_row['class_average'] = ( is_null($user_row['class_id']) ) ? T_('N/A') : @number_format(mysql_result(mq(totalMarks("JOIN users USING (user_id) WHERE school_id = {$user['school_id']} AND class_id = {$user_row['class_id']} AND user_start_date IS NOT NULL")), 0) / mysql_result(mq("SELECT COUNT(*) FROM users WHERE school_id = {$user['school_id']} AND class_id = {$user_row['class_id']} AND user_start_date IS NOT NULL"), 0), 2);
$user_row['school_average'] = ( is_null($user_row['class_id']) ) ? T_('N/A') : @number_format(mysql_result(mq(totalMarks("JOIN users USING (user_id) WHERE school_id = {$user['school_id']} AND user_start_date IS NOT NULL")), 0) / mysql_result(mq("SELECT COUNT(*) FROM users WHERE school_id = {$user['school_id']} AND user_start_date IS NOT NULL"), 0), 2);

// ***** TOTAL MILES ***** //
$sql = totalMarks("WHERE user_id = {$user['user_id']}");
echo "<input type='hidden' name='TOTAL MILES' value='" . $sql . "'>";
$user_row['total_miles'] = $user_miles = mysql_result(mq(totalMarks("WHERE user_id = {$user['user_id']}")), 0);
// ***** TOTAL MILES ***** //

$withdraw_used_points = mysql_fetch_assoc(mq("SELECT SUM(points) points_total FROM user_withdraw WHERE user_id = {$user['user_id']}"));

//$mark_date = dateThisYear(13, 18);
//$mysql = totalMarks("WHERE user_id = {$user['user_id']} AND mark_date >= " . dateThisYear(13, 18));
//$myquery = mysql_query($mysql);
//$myrow = mysql_fetch_assoc($myquery);
//$mycurrpoints = $myrow['mark_points'];

$cur_points = floatval(mysql_result(mq(totalMarks("WHERE user_id = {$user['user_id']} AND mark_date >= " . dateThisYear(13, 18))), 0));

//echo totalMarks("WHERE user_id = {$user['user_id']} AND mark_date >= " . dateThisYear(13, 18));

if ($camp_season)
	include('camp_code_processor.php');
else
	include('code_processor.php');

// ********** USER MILES ********** //
$sql = "SELECT SUM(points) AS user_miles FROM member_tasks JOIN camp_tasks USING (camp_task_id) WHERE user_id=" . $user_id . " AND completed=1";
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$user_miles = floatval($row['user_miles']);
$sql = "SELECT SUM(points) AS user_miles FROM member_points WHERE user_id=" . $user_id;
$row = mysql_fetch_assoc(mysql_query($sql));
$user_miles = $user_miles + floatval($row['user_miles']);
// ********** USER MILES ********** //

$camp_points = mysql_fetch_assoc(mq("SELECT SUM(points) c_points FROM camp_tasks JOIN member_tasks AS mt USING(camp_task_id) WHERE mt.user_id = {$user['user_id']} AND mt.completed = 1"));

/*
if ($camp_season) {
	$camp_logo_id = 0;
	$row = mysql_fetch_assoc(mysql_query("SELECT camp_logo_id FROM camps WHERE camp_id=" . $camp_id));
	$camp_logo_id = $row['camp_logo_id'];

	$groups = array();
	$sql = "SELECT * FROM member_groups JOIN groups USING (group_id) WHERE user_id=" . $user_id . " AND end_date=0";
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$group_id = $row['group_id'];
		$group_name = $row['group_name'];

		$sql2 = "SELECT COUNT(*) AS no_of_members FROM member_groups WHERE group_id=" . $group_id . " AND end_date=0";
		$query2 = mysql_query($sql2);
		$row2 = mysql_fetch_assoc($query2);
		$no_of_members = $row2['no_of_members'];

		$sql3 = "SELECT SUM(points) AS total_points FROM member_tasks JOIN camp_tasks USING (camp_task_id) WHERE group_id=" . $group_id . " AND completed=1";
		$query3 = mysql_query($sql3);
		$row3 = mysql_fetch_assoc($query3);
		$total_points = $row3['total_points'];

		$sql4 = "SELECT SUM(points) AS total_points FROM group_task_dates JOIN camp_tasks USING (camp_task_id) WHERE group_id=" . $group_id . " AND completed=1";;
		$query4 = mysql_query($sql4);
		$row4 = mysql_fetch_assoc($query4);
		$total_group_points = $row4['total_points'];

		if ($total_points > 0)
			$average_points = $no_of_members / $total_points;
		else
			$average_points = 0;

		$total_group_points = $total_group_points + $average_points;
		$element = compact('group_id', 'group_name', 'total_group_points');
		array_push($groups, $element);
	}
}
*/

$left_points = $cur_points - $withdraw_used_points['points_total'];

function get_todays_julian_date() {
	$todays_day = date("j");
	$todays_month = date("n");
	$todays_year = date("Y");
	$today_jd = cal_to_jd  (CAL_GREGORIAN, $todays_month,  $todays_day, $todays_year);

	return $today_jd;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="chrome=1">
		
		<title>User Profile - Tzivos Hashem Management System</title>
		
		<link rel="alternate" media="print" href="withdraw_print.php">
		<link rel="stylesheet" type="text/css" href="kiosk/scripts/shadowbox/shadowbox.css">
		<LINK href="card_printer.css" rel="stylesheet" type="text/css">
		<link href="kiosk/styles/reset.css" rel="stylesheet" type="text/css" />
		<link href="kiosk/styles/style.css" rel="stylesheet" type="text/css" />
		<link href="kiosk/styles/print.css" rel="stylesheet" type="text/css" media="print" />
		
		<!--[if IE]>
		<link href="kiosk/styles/style_ie.css" rel="stylesheet" type="text/css" />
		<![endif]-->
		
		<style type="">
			.rank 
			{
			  width: 100px;
			  height: auto;
			  border: none;
			  position: absolute;
			  margin-top: 100px;
			}
		</style>
		
		<script src="kiosk/scripts/jquery.core.js" type="text/javascript"></script>
		<script src="kiosk/scripts/jquery.ui.js" type="text/javascript"></script>
		<!--Copyright Ariel Shkedi 2007-2010-->
		<script type="text/javascript">
			var school_number = <?=isset($user_row['school_number'])?$user_row['school_number']:0?>;

			function check_browser() {
				if (navigator.appName == "Microsoft Internet Explorer") {
					window.location = "redirect.php"
				}

				if (school_number == 613829) {
					document.getElementById("online_chat_one").style.width = window.screen.width;
					document.getElementById("online_chat_two").style.width = window.screen.width / 2;
				}
			}
			
			function add_to_miles(intCardPoints)
			{
				document.getElementById("total_miles").innerHTML = document.getElementById("total_miles").innerHTML * 1 + intCardPoints;
				document.getElementById("year_miles").innerHTML = document.getElementById("year_miles").innerHTML * 1 + intCardPoints;
			}
		</script>
	</head>

	<body class="green" onload="check_browser();">

		<input type="hidden" name="camp_id" value="<?=$camp_id;?>">
		<input type="hidden" name="camp_registered" value="<?=$camp_registered;?>">
		<input type="hidden" name="camp_season" value="<?=$camp_season;?>">

		<div id="wrapper">

			<div id="header">

				<div class="org">

					<div class="nav">
						<ul>
<!--                    	<li class="icon_back"><a href="#" onclick="javascript:history.back(); return false">Back</a></li>-->
<!--                    	<li class="icon_home"><a href="../statement.php">Home</a></li>-->
							<li class="icon_logout"><a href="logout.php?n=statement.php">Logout</a></li>
						</ul>
					</div>

					<div class="org_photo">
					   <? if ($camp_season) : ?>
							<? if ($camp_logo_id > 0) : ?>
								<?=linkImgFile($camp_logo_id,100,100);?>
							<? endif; ?>
					   <? else : ?>
						<?=(!is_null($user_row['school_logo_id']) ? linkImgFile($user_row['school_logo_id'],100,100) : '');?>
					   <? endif; ?>
					</div>

				<? if ($camp_season) : ?>
					Base: #<?=$user_row['camp_number']?><br>
					<?=$user_row['camp_name']?><br>
				<? else : ?>
					Base: #<?=$user_row['school_number']?><br>
					<?=$user_row['school_name']?><br>
				<? endif; ?>
				<?=$user_row['rank_name'].' '.$user_row['first'].' '.$user_row['last']?>

			</div> <!-- org -->

		</div> <!-- header -->

		<div id="main">

            <div id="page_title">
				Welcome
			</div>

            <div class="two_column">

            	<div class="padding_top padding_left member_info_box">

                	<div class="member_photo">
                        <div class="member_photo_img">
							<?=!is_null($user_row['user_photo_id']) ? linkImgFile($user_row['user_photo_id'], 150, 150) : ''?>
						</div>

                        <div class="member_photo_cover">
						</div>

                        <div class="member_badge">

							<? if(!$camp_season) : ?>
                            <?=!is_null($user_row['rank_image_id']) ? linkImgFile($user_row['rank_image_id'], 124, 100) : ''?>
							<? endif; ?>
                        </div>
                    </div>

		   <?
		   if($user['registered'] && (!$camp_season) ):
				$strUrl = 'http://v2.mashpia.com/legacy/userpointsextract2/bar_code/3' . $user_row['user_code'];
				ob_start();
				$objCurl = curl_init();
				curl_setopt($objCurl, CURLOPT_URL, $strUrl);
				curl_exec($objCurl);
				$strResult = ob_get_contents();
				curl_close($objCurl);
				ob_end_clean();
				$arrResultPoints = preg_split("/\t/", $strResult);
				$cur_points += $arrResultPoints[0];
				$user_row['total_miles'] += $arrResultPoints[1];
		   ?>
                    <div class="member_info">
                    	<ul>
                        	<li class="member_name"><?=$user['display']?></li>
                        	<li><label>Rank:</label> <span><?=$user_row['rank_name']?></span></li>
                        	<li><label>Serial #:</label> <span><?=$user_row['user_serial']?></span></li>
                        	<li><label>Platoon:</label> <span><?=$user_row['class_grade']?><?=$user_row['class_sub']?></span></li>
                        	<li><label>Teacher:</label> <span><?=$user_row['class_teacher']?></span></li>
                        	<li><label>Platoon Average:</label> <span><?=$user_row['class_average']?></span></li>
                        	<li><label>Base Average:</label> <span><?=$user_row['school_average']?></span></li>
                        	<li><label>Total Miles:</label> <span id="total_miles"><?=$user_row['total_miles']?></span></li>
                        	<li><label>5772 Miles:</label> <span id="year_miles"><?=$cur_points?></span></li>
                        </ul>
                    </div>
		   <? endif; ?>

		   <? if(!$user['registered'] && ($camp_season) ): ?>
                    <div class="member_info">
                    	<ul>
                        	<li class="member_name"><li>
				<?=$user['display']?></li>
                        	<li><label>Serial #:</label>
								<span><?=$user_row['user_serial']?></span></li>
                        	<li><label>Camp Average:</label>
								<span><?=$user_row['school_average']?></span></li>
                        	<li><label>Current Total Miles:</label>
								<!--<span><?//=$camp_points['c_points']?></span></li>-->
								<span><?=$user_miles;?></span></li>
                        	<li><label>GROUP NAME & average group points:</label></li>

							<? for ($gno = 0; $gno < count($groups); $gno++) : ?>
								<li><label><?=$groups[$gno]['group_name'];?></label> <span><?=$groups[$gno]['total_group_points'];?></span></li>
							<? endfor; ?>


                     </ul>


                    </div>
		   <? endif; ?>



                    <div class="clear"></div>
                </div>
				
            	<div class="padding_left">
                	<div class="scan_card">
                    	<div class="scan_card_inside">
							<script>
								function loadShadow(cardnum) {
									// open a welcome message as soon as the window loads
									Shadowbox.open({
										content:    'cardpop.php?card=' + cardnum.value,
										player:     "iframe",
										width:      770,
										height:     430
									});
									cardnum.value='';
									return false;
								};
                            </script>

                            <? if ($user['registered'] || $camp_season): ?>

                                <FORM action="statement.php" method="post" id="scancard" accept-charset="UTF-8" autocomplete="off">
                                <DIV>
                                <?=T_('Scanning Station')?><BR>
                                <INPUT type="text" name="scan_code" size="25" maxlength="20" id="focus" title="<?=T_('Scan achievement or prize card')?>" autocomplete="off">
                                </DIV>
                                <SCRIPT type="text/javascript">document.getElementById('focus').focus();</SCRIPT>
                                </FORM>

                             <? else: ?>

                                  <H1>
                                    <?=T_('You are not currently registered in Tzivos Hashem.')?><BR>
                                    <?=T_('Please see the program director.')?>
                                  </H1>

                             <? endif; ?>
                          <div class="scan_card_cover"></div>
                        </div>
                    </div>
                </div>
            </div>
			<!-- <div class="two_column"> -->
			
	     <? if($user['registered'] && (!$camp_season) ): ?>
            <div class="one_column">
				<? $inbox = mysql_fetch_assoc(mq("SELECT COUNT(*) num FROM user_codes WHERE user_id = {$user_row['user_id']}")); ?>
				<ul class="buttons button_icons">
					<?//if($user_row['kiosk_edit'] != 'off'):?>
				<!--<li><a href="../missions.php" class="icon_missions">Mission Reporting</a></li><?//endif;?>-->
				<!--<li><a href="deposit.php" class="icon_deposit"><?=T_('Deposit')?>   </a><? if($inbox['num']){ ?><span class="badge"><?=$inbox['num']?></span><?}?></li>-->
					<?
					$show = true;
					//$sql = "select mark_date from date_tasks_marks where user_id = " . $user_row['user_id'] . " order by mark_date";
					//$result = mysql_query($sql);
					//$row = mysql_fetch_assoc($result);
					$date = $user_row['user_start_date'];
					if ($date >= 2455448) {
						if ($user_row['add_on_two'] == 0) {
							$show = false;
						}
					}
					//take away withdraw button for 5772
					$show = false;
					if ($show) {
					?>
					<li><a href="kiosk/withdraw.php" class="icon_withdraw"><?=T_('Withdraw')?></a>
					<? if($left_points>50){ ?>
					<span class="badge">
					<?
					$num = (int)($left_points/50);
					if ($num > 64)
						$num = 64;
					echo $num;
					?>
					</span><?}?></li>					
					<? } ?>
					<li><a href="auction_home.php" class="icon_auction"><?=T_('Auction')?></a></li>
					<? if ($user_row['school_store'] == 1) { ?>
<!--					<li><a href="statement2.php"><?=T_('Store')?></a></li>
-->					<? } ?>
					<li><a href="kiosk/profile.php" class="icon_profile"><?=T_('Profile')?></a></li>
					<?php /*
						$users = array();
						$sql = "select * from users where user_registered > 0 and add_on_one = 1";
						$result = mysql_query($sql);
						while ($row = mysql_fetch_assoc($result)) {
							$users[] = $row['user_code'];
						}
						if (in_array($user_row['user_code'], $users)) {
						//if($user_row['user_code'] == 3456950614912023040) { */
					?>					
						<li><a href="http://v2.mashpia.com/kiosk/auto-login?uc=3<?=$user_row['user_code']?>" class="icon_shop"><?=T_('Store')?></a></li>
					<?php 
					//}
					?>
					<!-- LI><a href="logout.php?n=statement.php"><IMG src="kiosk/images/icon_logout.png" alt="" width="48" height="48"><?=T_('Logout')?></A></li-->
                </ul>
			</div> <!-- one_column -->
	     <? endif; ?>

	     <? if ($camp_season) : ?>
            <div class="one_column">
				<? $inbox = mysql_fetch_assoc(mq("SELECT COUNT(*) num FROM user_codes WHERE user_id = {$user_row['user_id']}")); ?>
				<ul class="buttons button_icons">
					<li><a href="kiosk/store_withdraw.php" class="icon_withdraw"><?=T_('Withdraw')?></a><? if($left_points>50){?><span class="badge"><?=((int)($left_points/50))?></span><?}?></li>
					<li><a href="kiosk/store.php" class="icon_shop"><?=T_('Store')?></a></li>
					<li><a href="kiosk/progress.php" class="icon_progress">Progress</a></li>
					<!--<li><a href="kiosk/medals.php?camp_id=<?=$camp_id;?>&user_id=<?=$user['user_id'];?>" class="icon_medals">Medals</a></li>-->
					<!-- LI><a href="logout.php?n=statement.php"><IMG src="kiosk/images/icon_logout.png" alt="" width="48" height="48"><?=T_('Logout')?></A></li-->
                </ul>
			</div> <!-- one_column -->
	     <? endif; ?>


		</div> <!-- main -->

	<? if (1 == 2) { //$user_row['school_number'] == 613829 // 613818 ?>
		<!-- BEGIN Comm100 Live Chat Button Code -->
		<div id="online_chat_one">

			<div id="online_chat_two" style="margin-left:auto; margin-right:auto;">

				<div id="comm100_LiveChatDiv">
				</div>

				<div>
					<a href="http://www.comm100.com/livechat/" onclick="comm100_Chat();return false;" target="_blank" title = "Live Chat Live Help Software for Website">
						<img id="comm100_ButtonImage" src="http://chatserver.comm100.com/BBS.aspx?siteId=27781&planId=326" border="0px" alt="Live Chat Live Help Software for Website" />
					</a>
				</div>

				<script src="http://chatserver.comm100.com/js/LiveChat.js?siteId=27781&planId=326" type="text/javascript">
				</script>

				<div id="comm100_track" style="z-index:99;">
					<span style="font-size:10px; font-family:Arial, Helvetica, sans-serif;color:#555; margin-left:auto; margin-right:auto;">
						<a href="http://www.comm100.com/livechat/" style="text-decoration:none;color:#555" target="_blank">
							<b>&nbsp;&nbsp;Live Chat</b>
						</a>
						by
						<a href="http://www.comm100.com/" style="text-decoration:none;color:#009999;" target="_blank">
							Comm100
						</a>
					</span>
				</div>

			</div>

		</div>
		<!-- End Comm100 Live Chat Button Code -->
	<? } ?>


		<div style="width:100%;text-align:center;padding:5px;bottom:0; position:absolute; left:120;">

<BR>

</div>

		<input type='hidden' name='page_width' id='page_width' value=''>
		<input type='hidden' name='school number' value='<?=$user_row['school_number'];?>'>

	</div> <!-- wrapper -->

<script type="text/javascript" src="kiosk/scripts/shadowbox/shadowbox.js"></script>
<script type="text/javascript">
setInterval ( "scanFocus()", 1000 );
var scancard = document.forms.scancard.scan_code;
function scanFocus() {
	scancard.focus();
}

Shadowbox.init({
    skipSetup: true,
	players:    ["iframe"],
	initialWidth:320,
	initialHeight:30,
	overlayOpacity:0.8
});

</script>


	</BODY>

</HTML>

