<?php 
// enable debuging
if ($_GET['debug']) {
    //error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
}
// since we are using this straight from the mobile site, we need to make sure the needed cookies are set b/c the
// mobile login did not set any admin_auth or user_id cookies
if (isset($_GET['id'])) {
	if (!isset($_COOKIE['user_id'])) {
		setcookie('user_id', $_GET['id'], 0, '/');
	}
	if (!isset($_COOKIE['admin_auth'])) {
		$sql = "select username, password, user_code from users where user_id = " . $_COOKIE['user_id'];
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$auth = hash_hmac('ripemd128', strtolower($row['username']).$row['password'].$row['user_code'], '2c9e328c5710ded701e7b3bad70eeea6');
		setcookie('auth', $auth, 0, '/');
	}
}

$admin_auth = array('user');
require '../header.php';

$d = unixtojd();
$day = date("N");
$end = $d + 56;

switch ($day) {
    case 1:
        $end += 3;
        break;
    case 2:
        $end += 2;
        break;
    case 3:
        $end += 1;
        break;
    case 4:
        break;
    case 5:
		$end += 6;
        break;
    case 6:
        $end += 5;
        break;
    case 7:
		$end += 4;
        break;
    default:
        break;
}
$start = $end - 83;

// ***** REPORT DATES ***** //
include("../classes/report.php");
$parshos = array();
$sql = "SELECT * FROM parshos
		where start >= $start 
		and end <= $end 
		ORDER BY start";    
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
    array_push($parshos, $row);
}


// ***** REPORT DATES ***** //

include("../classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new \classes\admin($row);
$admin->get_markable_children();

$children = array();
foreach ($admin->children as $child) {
	//filter out children with no school/class id
	if (!empty($child->school_id) && !empty($child->class_id)) {
		$children[] = $child;
	}
}

if (isset($debug) && $debug) {
    echo "<pre>";
    print_r($children);
    echo "</pre>";
}

//$weekStart = $end - 55;
$diff = 0;
switch ($day) {
	case 1:
		$diff = 3;
		break;
	case 2:
		$diff = 4;
		break;
	case 3:
		$diff = 5;
		break;
	case 4:
		$diff = 6;
		break;
	case 5:
		break;
	case 6:
		$diff = 1;
		break;
	case 7:
		$diff = 2;
		break;
}
$weekStart = $d - $diff;
$weekEnd = $weekStart + 6;

if (isset($_GET['start'])) {
	if ($_GET['user'] != -1) $user_id = $_GET['user'];
	$weekStart 	= $_GET['start'];
	$weekEnd		= $_GET['end']; 
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Print Missions</title>
		<link rel="stylesheet" href="newStyle.css?v=2.2" type="text/css" />
	</head>
	
	<body>
		<!--
		<div id="oldMissions" align="center">
			<a href="../parents_print_report.php">Take me back to the old style mission sheets!</a><br />
			Click <a href="../parentSettings.php">here</a> to add or remove pictures from mission sheets.</a>
		</div>
		-->		
		<div id="marking">
			<div id="outerParent">
				<div id="student">
					<div class="arrow-left"></div>
					<select name='user' id="user">
						<option value='-1'>All Children</option>
						<?
						foreach ($children as $child) {
							$id = $child->user_id;
							$name = $child->first . ' ' . $child->last;
							if ($id == $user_id) {
								echo "<option value='" . $id . "' selected='selected'>" . $name . "</option>";
							} else {
								echo "<option value='" . $id . "'>" . $name . "</option>";
							}
						}
						?>
					</select>
					<div class="arrow-right"></div>
				</div>
				<div id="parsha">
					<div class="arrow-left"></div>
					<select name='parsha' id="parshaSelection">
						<?
						foreach ($parshos as $parsha) {
							if ($weekStart == $parsha['start']) {
								echo "<option value='" . $parsha['start'] . ':' . $parsha['end'] . 
									"' selected='selected'>" . $parsha['name'] . "</option>";
							} else {
								echo "<option value='" . $parsha['start'] . ':' . $parsha['end'] . 
									"'>" . $parsha['name'] . "</option>";
							}
						}
						?>
					</select>
					<div class="arrow-right"></div>
				</div>
				<!--
				<div>
					<button id='change'>Change</button>
				</div>
				-->
			</div>			
		</div>
		<p class="noPrint" style="margins: auto; text-align: center; color: red; margin-top: -10px; margin-bottom: 20px;">
			Updated printing instructions! (please click on "Printing Instructions"). For best results please use Mozilla Firefox.<br />
			Please do a test printing on the computer you will use for printing missions throughout the year. Thank you.
		</p>
		<div id="instructions" class="noPrint">
        	<input type="button" value="Printing Instructions" 
        		onclick="window.open('instructions/', '_blank', 'width=650, height=475, menubar=no, scrollbars=yes, status=no, toolbar=no, titlebar=no')" />
			<input type="button" value="Print" onclick="window.print()" />
			<!--<input type="button" value="Back" onclick="window.location = '../admin.php'" />-->
		</div>
		<?
		require_once 'classes/missions.php';
		require_once 'classes/noPicMission.php';
		require_once 'classes/picMission.php';
		
		$missions = array();		
		if ( isset($user_id) ) {
			$m = new Missions( $weekStart, $weekEnd, $user_id );
			$missions[] = $m->getMissions();
			//echo "<pre>"; print_r($m->getMissions()); echo "</pre>"; exit;
		} else {
			foreach ($children as $child) {
				$m = new Missions( $weekStart, $weekEnd, $child->user_id );
				$missions[] = $m->getMissions();
			}
		}
		
		$objMissions = array();
		foreach ( $missions as $info ) {
			foreach ( $info as $mission ) {
				$type = $mission->pic_mission_type;
				$objMissions[] = MissionDisplay::getInstance( $type, $mission );
			}
		}
		
		foreach ($objMissions as $obj) {
			$id = $obj->user_id;
			if ($obj->lang_id == 1) {
				echo "<div class='userMission' id='user-" . $id . "' >";
			} else if ($obj->lang_id == 2) {
				echo "<div class='userMission he' id='user-" . $id . "' dir='rtl' >";
			}
			$obj->printMission();
			echo "</div>";
			echo "<div style='clear: both; page-break-after: always'></div>";
		}
		?>
	</body>
	<script src="../jquery.js"></script>
	<script>
		$( function() {
			var admin = <?=$_GET['admin']?>;
			$(".arrow-left").click( function() {
				var val = $(this).parent().find("option:selected").prev().val();
				var type = $(this).parent().attr('id');
				if (val === undefined) {
					alert("There are no previous " + type + "s.");
					return;
				}
				
				var student, parsha;
				switch (type) {
					case 'student':
						student = val;
						parsha = $("#parsha select").val();
						break;
					case 'parsha':
						student = $("#student select").val();
						parsha = val;
				}
				
				var pos = parsha.indexOf(':');
				var start = parsha.substring(0, pos);
				var end = parsha.substring(pos+1);
				window.location.href = "newParentPrint.php?bypass=1&admin=" + admin + "&user=" + student + "&start=" + start + "&end=" + end;
			});
			
			$(".arrow-right").click( function() {
				var val = $(this).parent().find("option:selected").next().val();
				var type = $(this).parent().attr('id');
				if (val === undefined) {
					alert("There are no more " + type + "s.");
					return;
				}
				
				var student, parsha;
				switch (type) {
					case 'student':
						student = val;
						parsha = $("#parsha select").val();
						break;
					case 'parsha':
						student = $("#student select").val();
						parsha = val;
				}
				
				var pos = parsha.indexOf(':');
				var start = parsha.substring(0, pos);
				var end = parsha.substring(pos+1);
				window.location.href = "newParentPrint.php?bypass=1&admin=" + admin + "&user=" + student + "&start=" + start + "&end=" + end;
			});
			
			$("#user").change( function() {
				var student = $(this).val();
				var parsha = $("#parsha select").val();
				var pos = parsha.indexOf(':');
				var start = parsha.substring(0, pos);
				var end = parsha.substring(pos+1);
				window.location.href = "newParentPrint.php?bypass=1&admin=" + admin + "&user=" + student + "&start=" + start + 
					"&end=" + end;
			});
			
			$("#parshaSelection").change( function() {
				var student = $("#student select").val();
				var parsha = $(this).val();
				var pos = parsha.indexOf(':');
				var start = parsha.substring(0, pos);
				var end = parsha.substring(pos+1);
				window.location.href = "newParentPrint.php?bypass=1&admin=" + admin + "&user=" + student + "&start=" + start + 
					"&end=" + end;
			});
			
			$("#change").click( function() {
				var student = $("#student select").val();
				if (student == 1) student = -1;
				var parsha = $("#parsha select").val();
				var pos = parsha.indexOf(':');
				var start = parsha.substring(0, pos);
				var end = parsha.substring(pos+1);
				window.location.href = "newParentPrint.php?bypass=1&admin=" + admin + "&user=" + student + "&start=" + start + 
					"&end=" + end;
			});
			
			$(".userMission").each( function() {			
				var user = $(this).attr('id');	
				var user_id = user.substring(user.indexOf('-') + 1);
		    	var image = 'All';
		    	var elem = this;
		    	
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
							100	: 'Sticker - Brias Haguf_outline bw.png',
                            121 :  "day-school-Jewish Day 250 px.svg",
                            122 :  "day-school-Jewish Uniform 250px.svg",
                            124 :  "day-school-Health 250px.svg",
                            125 :  "day-school_Torah 250px.svg",
                            126 :  "day-school_Shabbat 250px.svg",
                            127 :  "day-school_Special Days 250px.svg",
                            129 :  "day-school_Kosher 250px.svg",
                            130 :  "day-school_Tefilla.svg",
                            131 :  "day-school-ahavat-yisrael.svg",
                            132 :  "day-school-brachot.svg",
                            133 :  "day-school-Tzedaka.svg",
                            134 :  "day-school-honoring parents 250px.svg",
                            135 :  "day-school-Middot-icon.svg"
		            	}
		            	
		            	var str = "<div class='finalFooter'>";
		                $.each(data, function(i, val) { 
		                    str += "<span class='footer_info'>";
		                    var j = 0;
		                    var s = stickers;
		                    $.each(val, function(indx, value) {
		                        //build footer info
		                        if (j++ == 0) { //first get sticker info
                                    if (i in s) {
                                        if (i < 120) str += "<img src='/mission_report/stickerOutlines/" + s[i] + "' /><br /><b>" + indx + "</b><br />";
                                        else str += "<img src='/mission_report/campaignLogos/" + s[i] + "' /><br /><b>" + indx + "</b><br />";
                                    }
                                    else str += "<img src='' /><br /><b>" + indx + "</b><br />";
		                        } else { //then get medal info
		                            str += "<i>" + value + " to " + indx + "</i>";
		                        }
		                    });
		                    str += "</span>"; 
		                });
		                str += "</div>";
		                $(elem).find("#" + user_id).append(str);
		            }
		    	});
	        });
	    	//window.print();
		});
	</script>
</html>