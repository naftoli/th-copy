<? 
$admin_auth = array('school');
require '../header.php';

$students = array();
$sql = "select u.user_id, u.first, u.last from users u
        join classes c on c.class_id = u.class_id
        where u.school_id = 255
        and u.user_registered > 0
        order by c.class_grade, c.class_sub, u.last, u.first";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $students[$row['user_id']] = $row['first'] . ' ' . $row['last'];
}

if (isset($_GET['user'])) {
    $user_id        = $_GET['user'];
	$startReport 	= $_GET['start'];
	$endReport		= $_GET['end'];
	$dblSided		= $_GET['dblSided'];
	$showDate		= $_GET['showDate']; 
} else {
	$d = unixtojd();
    $day = date("N", jdtounix($d));
    $end = $d;
    
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
    $startReport = $end + 1;
    $endReport = $startReport + 6;
    $dblSided = 1;
    $showDate = 1;
    $user_id = key( $students );
}
/*
$d = unixtojd();
$day = date("N");
$end = $d + 56; //add 8 weeks ahead

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
$start = $end - 90; //subtract 12 weeks from end
*/
require_once '../class.globalSettings.php';
$arrDates = GlobalSettings::getCurYearDates();
$start = $arrDates['start'];
$end = $arrDates['end'];

// ***** REPORT DATES ***** //
include("../classes/report.php");
$reports = array();
$sql = "SELECT * FROM reports 
		WHERE report_type='mission_cover_sheet' 
		AND visibility != 'none' 
		and start_date >= $start 
		and end_date <= $end 
		ORDER BY start_date";    
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
    $report = new report($row);
    array_push($reports, $report);
}
// ***** REPORT DATES ***** //
/*
$he = false;
if (isset($_GET['he']) && $_GET['he'] == 1) {
	$he = true;
}
 * 
 */
?>
<!DOCTYPE html>
<!--
<? if ($he) : ?>
<html dir="rtl" lang="he">
<? else : ?>
<html>
<? endif; ?>
-->
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Print Missions</title>
		<link rel="stylesheet" href="newStyle.css?v=2.3" type="text/css" />
	</head>
	
	<body>
		<div id="marking">
			<div id="outer">
				<div id="student">
					<div class="arrow-left"></div>
					<select name='user'>
						<?
                        foreach ($students as $id => $name) {
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
					<select name='parsha'>
						<?
						foreach ($reports as $report) {
							if ($startReport == $report->start_date) {
								echo "<option value='" . $report->start_date . ':' . $report->end_date . 
									"' selected='selected'>" . $report->report_name . "</option>";
							} else {
								echo "<option value='" . $report->start_date . ':' . $report->end_date . 
									"'>" . $report->report_name . "</option>";
							}
						}
						?>
					</select>
					<div class="arrow-right"></div>
				</div>
				<div id='change'>
					<button>Change</button>
				</div>
			</div>
		</div>
		<div style="clear: both"></div>	
		<div id="instructions" class="noPrint">
        	<input type="button" value="Print Instructions" 
        		onclick="window.open('instructions.php', '_blank', 'width=750, height=700, menubar=no, scrollbars=no, status=no, toolbar=no, titlebar=no')" />
			<input type="button" value="Print" onclick="window.print()" />
			<input type="button" value="Back" onclick="window.location = '../admin.php'" />
		</div>
		<?
		require_once 'classes/missions.php';
		require_once 'classes/noPicMission.php';
		require_once 'classes/picMission.php';
		
		$missions = array();		
		if ( $user_id != -1 ) {
			$m = new Missions( $startReport, $endReport, $user_id );
			$missions[] = $m->getMissions();
		}
		//echo "<pre>"; print_r( $missions ); echo "</pre>"; exit;
		
		$objMissions = array();
		foreach ( $missions as $info ) {
			foreach ( $info as $mission ) {
				$type = $mission->pic_mission_type;
				//$type = 1;
				$objMissions[] = MissionDisplay::getInstance( $type, $mission );
			}
		}
		
		foreach ( $objMissions as $obj ) {
			$obj->setDateDisplay( $showDate );
			$obj->setDblSided( $dblSided );
			$id = $obj->user_id;
			if ($obj->lang_id == 1) {
				echo "<div class='userMission' id='user-" . $id . "' >";
			} else if ($obj->lang_id == 2) {
				echo "<div class='userMission he' id='user-" . $id . "' dir='rtl' >";
			}
			$debug = false;
			if (isset($_GET['debug'])) $debug = true;
			$obj->printMission( $debug );
			echo "</div>";
			echo "<div style='clear: both; page-break-after: always'></div>";
		}
		?>
	</body>
	<script src="../jquery.js"></script>
	<script>
		$( function() {
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
						grade = $("#grade select").val();
						student = val;
						parsha = $("#parsha select").val();
						break;
					case 'parsha':
						grade = $("#grade select").val();
						student = $("#student select").val();
						parsha = val;
				}
				
				var school = 255;
				var dblSided = <?=$dblSided?>;
				var showDate = <?=$showDate?>;
				var pos = parsha.indexOf(':');
				var start = parsha.substring(0, pos);
				var end = parsha.substring(pos+1);
				window.location.href = "newSchoolPrintOT.php?school=" + school + "&user=" + student + "&start=" + start + "&end=" + end + "&dblSided=" + dblSided + "&showDate=" + showDate;
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
						grade = $("#grade select").val();
						student = val;
						parsha = $("#parsha select").val();
						break;
					case 'parsha':
						grade = $("#grade select").val();
						student = $("#student select").val();
						parsha = val;
				}
				
				var school = 255;
				var dblSided = <?=$dblSided?>;
				var showDate = <?=$showDate?>;
				var pos = parsha.indexOf(':');
				var start = parsha.substring(0, pos);
				var end = parsha.substring(pos+1);
				window.location.href = "newSchoolPrintOT.php?school=" + school + "&user=" + student + "&start=" + start + "&end=" + end + "&dblSided=" + dblSided + "&showDate=" + showDate;
			});
			
			
			$("#change").click( function() {
				var school = 255;
				var dblSided = <?=$dblSided?>;
				var showDate = <?=$showDate?>;
				var student = $("#student select").val();
				if (student == 1) student = -1;
				var parsha = $("#parsha select").val();
				var pos = parsha.indexOf(':');
				var start = parsha.substring(0, pos);
				var end = parsha.substring(pos+1);
				window.location.href = "newSchoolPrintOT.php?school=" + school + "&user=" + student + "&start=" + start + "&end=" + end + "&dblSided=" + dblSided + "&showDate=" + showDate;
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
		                $(elem).find("#" + user_id).append(str);
		            }
		    	});
	        });
	    	//window.print();
		});
	</script>
</html>