<?
$admin_auth = array('school','user'); 
require('header.php'); 
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
include 'class.taskExceptions.php';
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

function pager($page, $totalRendered) {
	/**
	 * figure out when to make second column and when to make new page
	 * if we are on first or last page, columnize after 10 and make new page after 20
	 * if we are on any other page columnize after 12 and make new page after 24
	 *  
	 * returns 1 to columnize and 2 to pagify (0 to do nothing)
	 **/
	
	$columnizeFirst = 10;
	$newPageFirst = 20;
	$columnizeReg = 16;
	$newPageReg = 32;
	$columnizeLast = 12;
	
	global $totalRows;
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
		if ($totalRendered == $columnizeFirst) {
			return 1;
		} else if ($totalRendered >= $newPageFirst) {
			return 2;
		} else {
			return 0;
		}
	} else if ($page == $lastPage) {
		if ($totalRendered == $columnizeLast) {
			return 1;
		} else {
			return 0;
		}
	} else {
		if ($totalRendered == $columnizeReg) {
			return 1;
		} else if ($totalRendered >= $newPageReg) {
			return 2;
		} else {
			return 0;
		}
	}
} 
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Print Mission Sheet</title>
    </head>

    <body>
    	<?
    	foreach ($users as $user) {
			require 'mission_report/create_report.php';
		}
		?>
	</body>
</html>
