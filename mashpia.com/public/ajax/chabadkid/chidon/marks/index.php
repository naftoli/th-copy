<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$ct = new ChidonTests();
$year = GlobalSettings::getChidonYear();

function getUserID($chidon_id) {
    $sql = "select user_id from th_chidon where th_chidon_id = " . mysql_real_escape_string($chidon_id);
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    return $row['user_id'];
}

function checkTrackAndSchool($user_id, $level) {
    global $year, $ct;
    $tracks = ['maven', 'pro', 'expert', 'genius'];

    $sql = "select tc.*, u.school_id as school, u.class_id from th_chidon tc 
            join users u using (user_id) 
            where tc.year = $year and u.user_id = $user_id";
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    // check school - only Lubavitcher Yeshiva (school id: 9)
    $school_id = $row['school'];
    // if ($school_id != 9) return false;
    // check track
    $ht = $ct->getHighestTrackPassed($row)['highest_track'];
    $key = array_search($ht, $tracks);
    if ($key && $level <= ++$key) return true; // only marks for levels that are less than or equal to the highest track can be saved
    return false;
}

$test_num = intval($_POST['test_num']);
if ($test_num < 4) {
    $closing_dates = ChidonTests::getClosingDates();
    $today = new DateTime();
    if ($today > $closing_dates[$test_num]) { // closing date of tests
        echo json_encode([
            'success' => false,
            'message' => 'The deadline has passed.'
        ]);
        exit;
    }
}

if ($test_num == 4) {
    $levels = [
        'maven'   => 1,
        'pro'     => 2,
        'expert'  => 3,
        'genius'  => 4
    ];

    $qrys = [];
    $info = $_POST['scores'];
    foreach ($info as $id => $more) {
        $user_id = getUserID($id);
        foreach ($more as $test_num => $scores) {
            foreach ($scores as $type => $mark) {
                $mark = intval($mark);
                $track = 'track_' . $levels[$type];
                // make sure child is allowed to enter mark for this level
                $allowed = checkTrackAndSchool($user_id, $levels[$type]);
                if ($allowed) {
                    $sql = "insert into th_chidon_finals 
                            set year = $year, 
                            user_id = $user_id, 
                            $track = $mark 
                            on duplicate key update $track = $mark";
                    $qrys[] = $sql;
                }
            }
        }
    }
    $success = true;
    mysql_query('set autocommit=0');
    mysql_query('begin');
    foreach ($qrys as $qry) {
        if (! mysql_query($qry)) {
            $success = false;
            break;
        }
    }
    if ($success) mysql_query('commit');
    else mysql_query('rollback');
    mysql_query('set autocommit=1');
} else {
    $marks = [];
    $levels = [];
    $info = $_POST['scores'];
    foreach ($info as $id => $more) {
        foreach ($more as $test_num => $other) {
            foreach ($other as $type => $scores) {
                foreach ($scores as $level => $mark) {
                    $marks[$id][$test_num]["$type"] = $mark;
                    $levels[$id][$test_num] = $level;
                }
            }
        }
    }
    $success = $ct->insertScores($marks, $levels);
}
?>
<!DOCTYPE html>
<html>
    <head>
        <script>
            let success = <?= $success ? 1 : 0 ?>;
            let test = <?= $test_num ?>;
            if (success) {
                alert('Marks saved.')
                if (test == 4) location.href = "https://mashpia.com/chidonTests/finals.php";
                else location.href = "https://mashpia.com/chidonTests/marks.php?test_num=" + test;
            }
            else alert('Error saving marks.')
        </script>
    </head>
</html>
