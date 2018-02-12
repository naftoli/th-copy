<?
//change dir to root
chdir("../");

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
	
$user_id = 8273;
$start_date = 2457011;
$end_date = 2457017;

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

if ($user_id > 0) {
    $sql = "SELECT * FROM users WHERE user_id=" . $user_id;
}
else {
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

if ($user_id > 0) { 
    $row = mysql_fetch_assoc($query);
    $user = new user($row);
    $user->get_rank();
    $student_count = 1;
    $user->get_school_class();
    $user->get_user_tracks(-1, $start_date, $end_date); 
}
else {
	$users = array();
    while ($row = mysql_fetch_assoc($query)) {
        $user = new user($row);
        $user->get_rank();
        $user->get_school_class();
        $user->get_user_tracks(-1, $start_date, $end_date);
        array_push($users, $user);
    }
    $student_count = count($users);
}

$numLabels = count($user->daily_labels) + count($user->weekly_labels) + count($user->shabbos_labels) + count($user->no_label_subjects);
$tracks = $user->user_tracks;
$numTasks = 0;
$types = array('daily_tasks', 'weekly_tasks', 'shabbos_tasks', 'no_label_tasks');
foreach ($tracks as $track) {
	foreach ($types as $type) {
		$numTasks += count($track->$type);
	}
}
$totalRows = $numLabels + $numTasks;
//echo $totalPages;

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
		$columnizeFirst = 11;
		$newPageFirst = 22;
		$columnizeReg = 15;
		$newPageReg = 30;
		$columnizeLast = 14;
		$newPageLast = 28;
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

//echo "<pre>"; print_r($user); echo "</pre>"; exit;
?>