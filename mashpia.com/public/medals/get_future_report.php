<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();

// make sure it's hq
if ($admin_user['auth'] != 'super') {
    echo 'You are not authorized to view this page.';
    exit;
}

function getUsers($school) {
    global $year, $MASHPIA_DB;

    $users = [];
    $sql = "select * from users u 
            join user_registration ur on ur.user_id = u.user_id 
            where u.school_id = :school 
            and ur.year = :year 
            and u.user_registered > 0";
    $stmt = $MASHPIA_DB->prepare($sql);
    $stmt->execute(['school' => $school, 'year' => $year]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $users[] = $row['user_id'];
    }

    return $users;
}

function getSubjects($user_id) {
    global $MASHPIA_DB;

    $subjects = [];
    $sql = "select subject_id from user_tracks where enrolled = 1";
    $result = $MASHPIA_DB->query($sql);
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $subjects[] = $row['subject_id'];
    }

    return $subjects;
}

function missionsDone($user_id) {
    global $MASHPIA_DB;

    // find out where the child is holding in terms of how many missions were already done for this subject
    $sql = "
            SELECT 
                subject_id, COUNT(*) AS total
            FROM
                date_tasks_mission_marks
            WHERE
                user_id = :user
            GROUP BY subject_id
    ";
    $stmt = $MASHPIA_DB->prepare($sql);
    $stmt->execute([':user' => $user_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $missions_by_subject = [];
    foreach ($rows as $row) {
        $missions_by_subject[$row['subject_id']] = intval($row['total']);
    }

    return $missions_by_subject;
}

function futureMissions($subject_id, $user_id, $end_date) {
    global $MASHPIA_DB;

    $jd = unixtojd(); // today
    $sql = "
        SELECT 
            COUNT(*) as mission_count
        FROM
            date_tasks_missions dtm
                JOIN
            user_tracks ut USING (subject_id)
                JOIN
            users u USING (user_id)
        WHERE
            dtm.subject_id = $subject_id 
                AND dtm.end_date >= $jd
                AND dtm.end_date <= $end_date
                AND u.school_type_id = dtm.school_type_id
                AND ut.track_id = dtm.track_id
                AND ut.level = dtm.level
                AND u.user_id = $user_id";

    $stmt = $MASHPIA_DB->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return intval($row['mission_count']);
}

function getEligibleMedals($user_id) {
    global $end_date, $ms;

    // get subjects that user is enrolled into
    $subjects = getSubjects($user_id);

    // find out how many missions were already done
    $missions_done = missionsDone($user_id);

    // find out how many more medals can be earned by certain date by subject
    $numMedals = 0;
    foreach ($subjects as $subject) {
        $future = futureMissions($subject, $user_id, $end_date);
        $current = $missions_done[$subject];
        $total = $current + $future;
        $current_medal = $ms->calcHighestMedal($subject, $current);
        $future_medal = $ms->calcHighestMedal($subject, $total);
        $medal_difference = $future_medal - $current_medal;
        // make sure there's no negative even though that would be a big issue if there was
        if ($medal_difference < 0) $medal_difference = 0;
        $numMedals += $medal_difference;
    }

    return $numMedals;
}

//******************** SCRIPT START HERE ************************//

// get school id from post
$school_id = $_REQUEST['school_id'];

// get all registered users in this school
$users = getUsers($school_id);

require_once 'class.medalsSubjects.php';
$ms = new MedalsSubjects();

$possible_medals = [];
foreach ($users as $user_id) {
    echo "User ID: " . $user_id . "\n";
    $num_medals = getEligibleMedals($user_id);
    echo "Num Medals: " . $num_medals . "\n";
    $possible_medals[$user_id] = $num_medals;
}

echo json_encode($possible_medals);