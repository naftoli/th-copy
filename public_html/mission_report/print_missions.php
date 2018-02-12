<?
$agent = $_SERVER['HTTP_USER_AGENT'];
function pager($page, $totalRendered, $totalRows, $addLabel = 0) {
	/**
	 * figure out when to make second column and when to make new page
	 * if we are on first or last page, columnize after 10 and make new page after 20
	 * if we are on any other page columnize after 12 and make new page after 24
	 *  
	 * returns 1 to columnize and 2 to pagify (0 to do nothing)
	 **/
	
	global $agent;
	if (strpos($agent, 'Firefox')) {
		$columnizeFirst = 10;
		$newPageFirst = 20;
		$columnizeReg = 14;
		$newPageReg = 28;
		$columnizeLast = 12;
		$newPageLast = 24;
	} else if (strpos($agent, 'Chrome')) {
		$columnizeFirst = 9;
		$newPageFirst = 18;
		$columnizeReg = 13;
		$newPageReg = 26;
		$columnizeLast = 10;
		$newPageLast = 22;
	}
	
	$lastPage = 1;
	if ($totalRows > $newPageFirst) {
		$lastPage++;
		if ($totalRows > ($newPageFirst + $newPageReg)) {
			$lastPage++;
			if ($totalRows > ($newPageFirst + ($newPageReg * 2))) {
				$lastPage++;
				if ($totalRows > ($newPageFirst + ($newPageReg * 3))) {
					$lastPage++;
					if ($totalRows > ($newPageFirst + ($newPageReg * 4))) {
						$lastPage++;
						if ($totalRows > ($newPageFirst + ($newPageReg * 5))) {
							$lastPage++;
							if ($totalRows > ($newPageFirst + ($newPageReg * 6))) {
								$lastPage++;
							}
						}
					}
				}
			}
		}
	}
	
	if ($page == 1) {
		if (
			($totalRendered == $columnizeFirst) || 
			($totalRendered < $columnizeFirst && (($totalRendered + $addLabel) >= $columnizeFirst))
			) {
			return 1;
		} else if (($totalRendered + $addLabel) >= $newPageFirst) {
			return 2;
		} else {
			return 0;
		}
	} else if ($page == $lastPage) {
		if (
			($totalRendered == $columnizeLast) || 
			($totalRendered < $columnizeLast && (($totalRendered + $addLabel) >= $columnizeLast))
			) {
			return 1;
		} else if (($totalRendered + $addLabel) >= $newPageLast) {
			return 2;
		} else {
			return 0;
		}
	} else {
		if (
			($totalRendered == $columnizeReg) || 
			($totalRendered < $columnizeReg && (($totalRendered + $addLabel) >= $columnizeReg))
			) {
			return 1;
		} else if (($totalRendered + $addLabel) >= $newPageReg) {
			return 2;
		} else {
			return 0;
		}
	}
} 

chdir("../");
require('db.php'); 
include("classes/subject.php");

$user_id = $_GET['user_id'];
$school_id = $_GET['school_id'];
$class_id = $_GET['class_id'];
$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];

$heDates = array();
$heDatesDisp = array();
$temp = $start_date;
do {
	$he = iconv('WINDOWS-1255', 'UTF-8', jdtojewish($temp, true, CAL_JEWISH_ADD_GERESHAYIM));
	$heArr = explode(' ', $he);
	$heDates[] = $heArr[0] . ' ' . $heArr[1];
	$heDatesDisp[] = $heArr[0];
} while (++$temp <= $end_date);

$sql = "select name from parshos where start = " . $start_date . " and end = " . $end_date;
$result = mysql_query($sql);
if (mysql_num_rows($result) > 0) {
	$row = mysql_fetch_assoc($result);
	$parsha = $row['name'];
} else {
	$parsha = '';
}

include("classes/user.php");
include("classes/user_track.php");
include("classes/school_class.php");
include("class.taskExceptions.php");
include("classes/date_tasks_mission.php");
include("classes/daily_task.php");
include("classes/weekly_task.php");
include("classes/shabbos_task.php");
include("classes/no_label_task.php");
include("classes/task.php");
include("classes/date_tasks_mark.php");

$users = array();
if ($user_id > 0) {
    $sql = "SELECT * FROM users WHERE user_id=" . $user_id;
} else {
if ($class_id > 0) {
    $sql = "SELECT * FROM users WHERE school_id=" . $school_id . " AND class_id=" . $class_id . " and user_registered > 0 order by last, first";
}
else {
    $sql = "SELECT * FROM users u 
            join classes c using (class_id) 
            WHERE u.school_id=" . $school_id . " 
            and u.user_registered > 0 
            order by c.class_grade, c.class_sub, u.last, u.first";
    }
}
$query = mysql_query($sql);

while ($row = mysql_fetch_assoc($query)) {
    $user = new user($row);
    $user->get_rank();
    $user->get_school_class();
    $user->get_user_tracks(-1, $start_date, $end_date);
    array_push($users, $user);
}

$days_of_week = array("F", "ש", "S", "M", "T", "W", "T");
$subjectIcons = array(
	1	=>	'Tehillim.png',
	4	=>	'Tefilla.png',
	12	=>	'Mivtzoim.png',
	13	=>	'Niggunim.png',
	16	=>	'hiskashrus.png',
	21	=>	'sefer hamitzvos.png',
	27	=>	'',
	40	=>	'Yom Dipagra.png',
	41	=>	'Father Son.png',
	42	=>	'Footsteps.png',
	45	=>	'Cheshbon Hanefesh.png',
	90	=>	'Chitas.png',
	100	=>	'Brias Haguf.png'
);
chdir("mission_report");
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <?
		if (strpos($agent, 'Firefox')) {
			echo "<link rel='stylesheet' type='text/css' href='style.css' />";
		} else if (strpos($agent, 'Chrome')) {
			echo "<link rel='stylesheet' type='text/css' href='style2.css' />";
		}
        ?>
        <script src="js/jquery.js"></script>
        <title>Print Mission Sheet</title>
    </head>

    <body>
    	<?
    	$num = count($users);
    	foreach ($users as $k => $user) {
			require 'create_report.php';
			if ($k != ($num - 1)) {
				echo "<div style='page-break-after: always'></div>";
			}
		}
		?>
	</body>
	
	<script>
		$(function() {
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
	            		1	:	'Sticker - WWTC bw.png',
						4	:	'Sticker - Tefilah bw.png',
						12	:	'Sticker - Mivtzoim bw.png',
						13	:	'Sticker - Nigunnim bw.png',
						16	:	'Sticker - Hiskashrus bw.png',
						21	:	'Sticker - Sefer Hamitzvos bw.png',
						27	:	'Sticker - Tanya bw.png',
						40	:	'Sticker - Yomei Dipagra bw.png',
						41	:	'Sticker - Avos Ubanim b w.png',
						42	:	'Sticker - Halachta Bidrachav bw.png',
						45	:	'Sticker - Cheshbon Hanefesh bw.png',
						90	:	'Sticker - Chitas bw.png',
						100	:	'Sticker - Brias Haguf_outline bw.png'
	            	}
	            	var str = "<div>";
	                $.each(data, function(i, val) { 
	                    str += "<span class='footer_info'>";
	                    var j = 0;
	                    var s = stickers;
	                    $.each(val, function(indx, value) {
	                        //build footer info
	                        if (j++ == 0) { //first get sticker info
	                            str += "<img src='image/" + s[i] + "' /><br /><b>" + indx + "</b><br />";
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
		});
	</script>
	
</html>
