<?php
ini_set('display_errors',1);
//header("Location: under_construction.php");
$agent = @$_SERVER['HTTP_USER_AGENT'];
//echo "<input type='hidden' name='user_agent' value='$agent'>";
//if using chrome or explorer show error
//if (preg_match('/like Gecko/', $agent) || preg_match('/MSIE/', $agent)) {
if (preg_match('/MSIE/', $agent)) { 
	echo "Our Kiosk is not compatable with Internet Explorer.";
	exit;
}

require('header.php');
require_once('calendar.php');
require_once('file_save.php');
require_once('card_printer.php');

$url = $_SERVER["REQUEST_URI"];
//echo "<input type='hidden' name='URL' value='" . $url . "'>\n";

$qryCounter = 0;
if (!isset($_COOKIE["user"])) {
	$qryTimes[$qryCounter]['start'] = time();
	$sql = "SELECT user_id, first, last, user_code, first_he, last_he, username, gender, user_address1, user_address2,
		   user_city, user_state, user_postal, user_country, user_phone, camp_registered, user_start_date, users.add_on_two, 
		   user_serial, user_photo_id, mobile_pic, class_id, class_grade, class_sub, class_teacher, team_id, team_name,
		   users.school_id, school_name, school_number, school_store, school_city, school_state, school_makeup_id, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, kiosk_edit,
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
	ORDER BY class_grade, class_sub, last, first";

	$user_row = mysql_fetch_assoc( mq( $sql ) );
	$qryTimes[$qryCounter++]['end'] = time();
}

$user_id = $user['user_id'];
$school_id = $user['school_id'];

$camp_season = 0;

// ***** TOTAL MILES ***** //
//$sql = totalMarks("WHERE user_id = {$user['user_id']}");
//echo "<input type='hidden' name='TOTAL MILES' value='" . $sql . "'>";
require 'class.points.php';
$p = new Points( $user_id );
$qryTimes[$qryCounter]['start'] = time();
//$user_row['total_miles'] = $user_miles = floor(mysql_result(mq(totalMarks("WHERE user_id = {$user['user_id']}")), 0));
$user_row['total_miles'] = $user_miles = $p->getTotalPoints();
$qryTimes[$qryCounter++]['end'] = time();
// ***** TOTAL MILES ***** //

$qryTimes[$qryCounter]['start'] = time();
//$withdraw_used_points = mysql_fetch_assoc(mq("SELECT SUM(points) points_total FROM user_withdraw WHERE user_id = {$user['user_id']}"));
$qryTimes[$qryCounter++]['end'] = time();

//$mark_date = dateThisYear(13, 18);
//$mysql = totalMarks("WHERE user_id = {$user['user_id']} AND mark_date >= " . dateThisYear(13, 18));
//$myquery = mysql_query($mysql);
//$myrow = mysql_fetch_assoc($myquery);
//$mycurrpoints = $myrow['mark_points'];

//if ($user_row['school_id'] == 61)
 //   $cur_points = floatval(mysql_result(mq(totalMarks("WHERE user_id = {$user['user_id']} AND mark_date >= " . dateThisYear(13, 18))), 0));
//else 
$qryTimes[$qryCounter]['start'] = time();
//$cur_points = floor(floatval(mysql_result(mq(totalMarks("WHERE user_id = {$user['user_id']} and mark_date >= 2457629")), 0)));
$cur_points = $p->getTotalThisYear();
//$mashpiaPoints = floor(mysql_result(mq(totalMarks("WHERE user_id = {$user['user_id']} and mark_date >= 2457629")), 0));
$qryTimes[$qryCounter++]['end'] = time();

include('code_processor.php');

$qryTimes[$qryCounter]['start'] = time();
//$camp_points = mysql_fetch_assoc(mq("SELECT SUM(points) c_points FROM camp_tasks JOIN member_tasks AS mt USING(camp_task_id) WHERE mt.user_id = {$user['user_id']} AND mt.completed = 1"));
$qryTimes[$qryCounter++]['end'] = time();

// find if tanya is active
$qryTimes[$qryCounter]['start'] = time();
$subjects_result = mq("SELECT subjects.subject_id, subject_name, subject_type, subjects.subject_black_image_id
FROM subjects
JOIN school_subjects ON (subjects.subject_id = school_subjects.subject_id AND school_id = {$school_id})
WHERE subject_type NOT IN ('school_points', 'home_points')
ORDER BY subject_name");
$boolTanya = 0;
while ($row = mysql_fetch_assoc($subjects_result))
{
	if (md5($row["subject_name"]) == "2b3ec3fbc6ff09833c2172bd040487ce") {
		$boolTanya = 1;
	}
}
$qryTimes[$qryCounter++]['end'] = time();

//$left_points = $user_row['total_miles'] - $withdraw_used_points['points_total'];

function get_todays_julian_date() {
	$todays_day = date("j");
	$todays_month = date("n");
	$todays_year = date("Y");
	$today_jd = cal_to_jd  (CAL_GREGORIAN, $todays_month,  $todays_day, $todays_year);

	return $today_jd;
}

$qryTimes[$qryCounter]['start'] = time();
$params = array("user_code" => $user_row['user_code']);
//if (in_array($school_id, $australian)) $intUserStorePoints = header_icorpa_points($params);
//else
//$intUserStorePoints = header_store_points($params);
$intUserStorePoints = $p->getStorePoints();
$qryTimes[$qryCounter++]['end'] = time();
$qryTimes[$qryCounter]['start'] = time();
//if (in_array($school_id, $australian)) {
//	$params['no_negs'] = 1;
//	$arrPointsEarned = header_icorpa_points($params);
//} else
//$arrPointsEarned = header_total_points($params);
$arrPointsEarned = $p->getTotalThisYear();
$qryTimes[$qryCounter++]['end'] = time();

//if ($user_row) outputTime();
function outputTime() {
	global $qryTimes;
	if (isset($_COOKIE['naftoli'])) {
		echo "<pre>";
		print_r($qryTimes);
		echo "</pre>";
		exit;
	}
}

require 'mobile/reg/ajax/encrypt.php';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<meta name="mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-capable" content="yes">
		
		<title>User Profile - Tzivos Hashem Management System</title>
		
		<link rel="alternate" media="print" href="withdraw_print.php">
		<link rel="stylesheet" type="text/css" href="kiosk/scripts/shadowbox2/shadowbox.css">
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
		<script src="kiosk/scripts/shadowbox2/shadowbox.js" type="text/javascript"></script>
		<script type="text/javascript">
			// old: <?php print $cur_points; ?> - new: <?php print $intUserStorePoints; ?>
			
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
				confirm_points(intCardPoints);
			}
			
			window.onload = scanFocus;
			window.onkeydown = scanFocus;
			function scanFocus() {
				$("#scantext").focus();
			}
			scanFocus();
			
			function hideURLbar() {
				if (window.location.hash.indexOf('#') == -1) {
					window.scrollTo(0, 1);
				}
			}
			
			if (navigator.userAgent.indexOf('iPhone') != -1 || navigator.userAgent.indexOf('Android') != -1) {
			    addEventListener("load", function() {
			            setTimeout(hideURLbar, 0);
			    }, false);
			}
		</script>
	</head>

	<body class="green" onload="check_browser();hideURLbar();">

<!--		<input type="hidden" name="camp_id" value="--><?//=$camp_id;?><!--">-->
<!--		<input type="hidden" name="camp_registered" value="--><?//=$camp_registered;?><!--">-->
<!--		<input type="hidden" name="camp_season" value="--><?//=$camp_season;?><!--">-->

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
							<?php
							if (!empty($user_row['mobile_pic'])) {
								echo "<img width='150' height='150' src='mobile/reg/" . $user_row['mobile_pic'] . "' />";
							} else if (!is_null($user_row['user_photo_id'])) {
								//linkImgFile($user_row['user_photo_id'], 150, 150);
								echo "<img width='150' height='150' src='file_view.php?id=" . $user_row['user_photo_id'] . "' />";
							}
							?>
						</div>

                        <div class="member_photo_cover">
						</div>

                        <div class="member_badge">

							<? if(!$camp_season) : ?>
                            <?=!is_null($user_row['rank_image_id']) ? linkImgFile($user_row['rank_image_id'], 124 ) : ''?>
							<? endif; ?>
                        </div>
                    </div>

<?php
	echo "<input type='hidden' name='oldPoints' value='" . $cur_points . "'>";
	//$user_row['total_miles'] += $arrPointsEarned[$user_row['user_code']];
	echo "<input type='hidden' name='newPoints' value='" . $intUserStorePoints[$user_row['user_code']] . "'>";
	//$cur_points += $intUserStorePoints[$user_row['user_code']];
	if ($user['registered'] && !$camp_season)
	{
?>
                    <div class="member_info">
                    	<ul>
                        	<li class="member_name"><?=$user['display']?></li>
                        	<li><label>Rank:</label> <span><?=$user_row['rank_name']?></span></li>
                        	<li><label>Serial #:</label> <span><?=$user_row['user_serial']?></span></li>
                        	<li><label>Platoon:</label> <span><?=$user_row['class_grade']?><?=$user_row['class_sub']?></span></li>
                        	<li><label>Teacher:</label> <span><?=$user_row['class_teacher']?></span></li>
                        	<li><label>Total Miles Earned:</label> <span id="total_miles"><?=$user_row['total_miles']?></span></li>
                        	<li><label>Total Miles Earned This Year:</label> <span id="year_miles"><?=$cur_points?></span></li>
                        </ul>
                    </div>
<?php
	}
?>

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
	async function loadShadow(cardnum) {
		if (cardnum.value.match(/^ *$/)) return;
    // open a welcome message as soon as the window loads
    const res = await fetch('/cardpop.php?card=' + cardnum.value + '&amp;user_id=' + <?=$user_id?>)
    const result = await res.json()
    alert(result)
		cardnum.value='';
		return false;
	}
	
	function confirm_points(intPoints)
	{
		window["sb-content"].location.href="/cardpop_template.php?title=Scan successful!&msg=You have been awarded " + intPoints + " points.";
		return false;
	}
	
	Shadowbox.init({
		skipSetup: true,
		players: ["iframe", "html"],
		initialWidth:320,
		initialHeight:30,
		overlayOpacity:0.8
	});
                            </script>

                            <? if ($user['registered'] || $camp_season): ?>
							
								<form name="scancard" id="scancard" onSubmit="loadShadow(this.scantext); return false;">
									Scanning Station<br>
									<input name="scantext" id="scantext" type="text" autocomplete="off"  />
								</form>
								<script type="text/javascript">document.getElementById('scantext').focus();</script>

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
				<? //$inbox = mysql_fetch_assoc(mq("SELECT COUNT(*) num FROM user_codes WHERE user_id = $user_id")); ?>
				<ul class="buttons button_icons">
					<?//if($user_row['kiosk_edit'] != 'off'):?>
				<!--<li><a href="../missions.php" class="icon_missions">Mission Reporting</a></li><?//endif;?>-->
				<!--<li><a href="deposit.php" class="icon_deposit"><?=T_('Deposit')?>   </a><? if($inbox['num']){ ?><span class="badge"><?=$inbox['num']?></span><?}?></li>-->
					<!--
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
					-->
					
					<? //if ( $school_id != 198 ) { 
						//if (0) {
					?>
					<li><a href="auction_home.php" class="icon_auction"><?=T_('Auction')?></a></li>
					<? //} ?>
					<li><a href="kiosk/profile.php" class="icon_profile"><?=T_('Profile')?></a></li>
					<?php if ( $user_row['school_store'] > 0 ) { ?>
						<li><a href="http://mashpia.com/v2/kiosk/auto-login/uc/<?=encrypt_decrypt('encrypt', '3' . $user_row['user_code'])?>/pb/<?=encrypt_decrypt('encrypt', $p->getMashpiaStorePoints())?>" class="icon_shop"><?=T_('Store')?></a></li>
					<!-- LI><a href="logout.php?n=statement.php"><IMG src="kiosk/images/icon_logout.png" alt="" width="48" height="48"><?=T_('Logout')?></A></li-->
					<?php } ?>
				</ul>
			</div> <!-- one_column -->
	     <? endif; ?>

	     <? if ($camp_season) : ?>
            <div class="one_column">
				<? $inbox = mysql_fetch_assoc(mq("SELECT COUNT(*) num FROM user_codes WHERE user_id = {$user_row['user_id']}")); ?>
				<ul class="buttons button_icons">
					<li><a href="kiosk/store_withdraw.php" class="icon_withdraw"><?=T_('Withdraw')?></a><? if($left_points>50){?><span class="badge"><?=((int)($left_points/50))?></span><?}?></li>
					<?php if ( $user_row['school_store'] > 0 ) { ?>
						<li><a href="kiosk/store.php" class="icon_shop"><?=T_('Store')?></a></li>
					<?php } ?>
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
	</BODY>

</HTML>

