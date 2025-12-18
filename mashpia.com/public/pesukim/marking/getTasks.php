<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

function getCurrentWeekDates() {
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
	return ['start' => $start, 'end' => $end];
}

$student_id = (int)$_POST['student'];
if ($student_id > 0) {
    include($_SERVER['DOCUMENT_ROOT'] . '/classes/user.php');
    include($_SERVER['DOCUMENT_ROOT'] . '/classes/user_track.php');
    include($_SERVER['DOCUMENT_ROOT'] . '/classes/school_class.php');
    include($_SERVER['DOCUMENT_ROOT'] . '/class.taskExceptions.php');
    include($_SERVER['DOCUMENT_ROOT'] . '/classes/date_tasks_mission.php');
    include($_SERVER['DOCUMENT_ROOT'] . '/classes/daily_task.php');
    include($_SERVER['DOCUMENT_ROOT'] . '/classes/weekly_task.php');
    include($_SERVER['DOCUMENT_ROOT'] . '/classes/shabbos_task.php');
    include($_SERVER['DOCUMENT_ROOT'] . '/classes/no_label_task.php');
    include($_SERVER['DOCUMENT_ROOT'] . '/classes/task.php');
    include($_SERVER['DOCUMENT_ROOT'] . '/classes/date_tasks_mark.php');
    include($_SERVER['DOCUMENT_ROOT'] . '/classes/pesukim_task.php');
    include($_SERVER['DOCUMENT_ROOT'] . '/pesukim/class.pesukim.php');

    $stmt = $MASHPIA_DB->prepare("SELECT * FROM users WHERE user_id = :student_id");
    $stmt->execute(['student_id' => $student_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['error' => 'Student not found']);
        exit;
    }

    // get current week's dates
    $dates = getCurrentWeekDates();

    $user = new user($user);
    $user->get_rank();
    $user->get_school_class();
    $user->get_user_tracks(136, $dates['start'], $dates['end'], [], $user->lang_id);

    echo json_encode($user);
}