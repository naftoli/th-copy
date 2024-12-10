<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('max_execution_time', 300); // update max execution time to 5 min

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

function getMissionsDone($school_id) {
    global $MASHPIA_DB;

    // find out where the child is holding in terms of how many missions were already done for this subject
    $sql = "
        SELECT 
            user_id, subject_id, COUNT(*) AS total
        FROM
            date_tasks_mission_marks dtm
                JOIN
            users u USING (user_id)
        WHERE
            u.school_id = :school
        GROUP BY user_id , subject_id
    ";
    $stmt = $MASHPIA_DB->prepare($sql);
    $stmt->execute([':school' => $school_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $missions_by_subject = [];
    foreach ($rows as $row) {
        $missions_by_subject[$row['user_id']][$row['subject_id']] = intval($row['total']);
    }

    return $missions_by_subject;
}

function getSubjects($school_id) {
    global $MASHPIA_DB;

    $subjects = [];
    $sql = "
        SELECT DISTINCT
            subject_id
        FROM
            user_tracks ut
                JOIN
            users u USING (user_id)
        WHERE
            u.school_id = :school AND ut.enrolled = 1
        ORDER BY subject_id";
    $stmt = $MASHPIA_DB->prepare($sql);
    $stmt->execute([':school' => $school_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $subjects[] = $row['subject_id'];
    }

    return $subjects;
}

function getUserSubjects($school_id) {
    global $MASHPIA_DB;

    $subjects = [];
    $sql = "
        SELECT 
            user_id, subject_id
        FROM
            user_tracks ut
                JOIN
            users u USING (user_id)
        WHERE
            u.school_id = :school AND ut.enrolled = 1
        ORDER BY user_id";
    $result = $MASHPIA_DB->prepare($sql);
    $result->execute([':school' => $school_id]);
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $subjects[$row['user_id']][] = $row['subject_id'];
    }

    return $subjects;
}

function futureMissions($user_id) {
    global $end_date, $user_subjects, $MASHPIA_DB;

    $missions = [];
    $sql = "
        SELECT 
            COUNT(*) AS num_missions
        FROM
            date_tasks_missions dtm
                JOIN
            date_tasks dt USING (date_tasks_mission_id)
                JOIN
            user_tracks ut USING (subject_id)
                JOIN
            users u USING (user_id)
        WHERE
            dtm.subject_id = :subject 
                AND dtm.start_date >= :today 
                AND dtm.end_date <= :end_date 
                AND u.school_type_id = dtm.school_type_id
                AND ut.track_id = dtm.track_id
                AND ut.level = dtm.level
                AND u.lang_id = dtm.lang_id
                AND u.user_id = :user 
                AND dt.mandatory_qty = 1";

    $stmt = $MASHPIA_DB->prepare($sql);
    foreach ($user_subjects[$user_id] as $subject_id) {
        $stmt->execute([
            ':subject'  => $subject_id,
            ':today'    => unixtojd(),
            ':end_date' => $end_date,
            ':user'     => $user_id
        ]);
//        if ($user_id == 19274) $stmt->debugDumpParams();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $missions[$subject_id] = intval($row['num_missions']);
    }

    return $missions;
}

function getEligibleMedals($user_id) {
    global $ms, $missions_done, $user_subjects, $future_missions;

    $numMedals = 0;
    $future_missions = futureMissions($user_id);
//    if ($user_id == 19274) {
//        echo "<pre>";
//        print_r($future_missions);
//        echo "</pre>";
//    }
    // find out how many more medals can be earned by certain date by subject
    foreach ($user_subjects[$user_id] as $subject) {
        $future = $future_missions[$subject] ?? 0;
        $current = $missions_done[$user_id][$subject] ?? 0;
        $total = $current + $future;
        $current_medal = $ms->calcHighestMedal($subject, $current);
        $future_medal = $ms->calcHighestMedal($subject, $total);
        $medal_difference = $future_medal - $current_medal;
        // make sure there's no negative even though that would be a big issue if there was
        if ($medal_difference < 0) $medal_difference = 0;
        $numMedals += $medal_difference;
//        if ($user_id == 19274) {
//            echo "Subject: " . $subject . "\n";
//            echo "Current Missions: " . $current . "\n";
//            echo "Future Missions: " . $future . "\n";
//            echo "Current Medal: " . $current_medal . "\n";
//            echo "Future Medal: " . $future_medal . "\n";
//            echo "Difference: " . $medal_difference . "\n";
//            echo "\n";
//        }
    }

    return $numMedals;
}

//******************** SCRIPT START HERE ************************//

$info = file_get_contents("php://input");
$info = json_decode($info, true);

$school_id = $info['school_id'];
$end_date = $info['end_date'];

// needed for knowing how many missions are needed per subject per medal
require_once 'class.medalsSubjects.php';
$ms = new MedalsSubjects();

// get all registered users in this school
$users = getUsers($school_id);
$missions_done = getMissionsDone($school_id);
//$subjects = getSubjects($school_id);
$user_subjects = getUserSubjects($school_id);

// calculate possible medals
$possible_medals = [];
foreach ($users as $user_id) {
    $num_medals = getEligibleMedals($user_id);
    $possible_medals[$user_id] = $num_medals;
}

echo json_encode($possible_medals);