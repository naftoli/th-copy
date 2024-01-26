<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/utils/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$db = getDbHandle();
$schools = getSchools();
$year = getChidonYear();
$ct = new ChidonTests($year);
$types = $ct->getTypes();

$sql = "
    SELECT 
        tc.th_chidon_id, 
        tc.khk_reg, 
        tc.paid, 
        tc.date_paid, 
        tc.test_type,
        tc.reward_type,
        tc.award_type,
        tc.trip, 
        u.user_id,
        u.user_serial,
        u.first,
        u.last,
        u.school_id, 
        u.class_id,
        c.class_grade,
        c.class_sub
    FROM
        th_chidon tc
            JOIN
        users u USING (user_id)
            JOIN
        classes c ON c.class_id = u.class_id
    WHERE
        tc.year = :year AND u.school_id in (" . implode(',', array_keys($schools)) . ") 
    ORDER BY u.school_id, c.class_grade, c.class_sub, u.last, u.first
";
$stmt = $db->prepare($sql);
$res = $stmt->execute([
    'year' => $year
]);
//$stmt->debugDumpParams();
$info = [];
if ($res) {
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $ids = array_map(function($row) { return $row['user_id']; }, $rows);
    $khk_eligibility = KHK::getKHKEligibility($ids)[0];
    foreach ($rows as $row) {
        $ct->setStudents($row['school_id'], $row['class_id'], $row['user_id']);
        $ct->setScores();
        $ct->calculateMarks();
        $marks = $ct->getMarks();
        $highest_track = $ct->getHighestTrack($marks[$row['th_chidon_id']], $row['user_id']);
        // setup row with needed info
        $row['highest_track'] = $highest_track;
        $row['grade'] = $row['class_grade'] . ($row['class_sub'] ? '-' . $row['class_sub'] : '');
        $row['khk_eligible'] = $khk_eligibility[$row['user_id']] ? 1 : 0;
        $row['khk_passed_tests'] = getKhkPassed($row);
        $row['reward'] = getReward($row);
        $row['award'] = getAward($row);
        $row['raised'] = getRaised($row);
        $row['fee'] = getFee($row);
        $row['trip'] = getTrip($row);
        $row['extra_purchases'] = getExtraPurchases($row);
        $info[$row['school_id']][] = $row;
    }
}

echo json_encode([
    'success'   => $res,
    'info'      => $info,
    'error'     => $db->errorInfo()[2] ?? '',
    'super'     => getAuth() == 'super' ? 1 : 0,
    'types'     => $types,
    'schools'   => $schools
]);

function getKhkPassed($row) {
    if (! $row['khk_reg']) return false;
    else {
        global $db;
        $marks = [];
        $sql = "select * from th_khk_marks where th_chidon_id = " . $row['th_chidon_id'];
        $stmt = $db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $marks[$row['test_number']] = $row['mark'];
        }

        $total = 0;
        $num_tests = 0;
        $passing_mark = 70;
        foreach ($marks as $mark) {
            $total += $mark;
            $num_tests++;
        }
        $avg = intval($total / $num_tests);
        return $avg >= $passing_mark;
    }
}

function getReward($row) {
    $reward = trim($row['reward_type']);
    if (empty($reward) || $reward == 'highest track passed') return $row['highest_track'];
    else if ($reward == 'no reward') return '';
    else return $reward;
}

function getAward($row) {
    $award = trim($row['award_type']);
    if (empty($award) || $award == 'highest final passed') return $row['reward'];
    else if ($award == 'no award') return '';
    else return $award;
}

function getRaised($row) {
    global $db, $year;
    $sql = "
        SELECT 
            IFNULL( SUM(subsidy_amount), 0 ) AS total 
        FROM
            chidon_user_subsidies
        WHERE
            chidon_year = :year 
                AND user_id = :user
    ";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute([
        ':year' => $year,
        ':user' => $row['user_id']
    ]);
    if ($res) {
        $row = $stmt->fetch();
        return $row['total'];
    } else {
        return 0;
    }
}

function getFee($row) {
    $fees = [
        'maven'     => 36,
        'pro'       => 100,
        'expert'    => 200,
        'genius'    => 200,
        ''          => 0
    ];
    $fee = $fees[$row['reward']];
    // check if already paid for registration
    if (intval($row['paid']) > 0) $fee -= intval($row['paid']);
    // check how much was raised
    if ($row['raised'] > 0) $fee -= intval($row['raised']);
    // zero out negative fees
    if ($fee < 0) $fee = 0;
    return $fee;
}

function getTrip($row) {
    return $row['trip'] ? ($row['trip'] . ' Trip') : '';
}

function getExtraPurchases($row) {
    return '';
}

function getPreRegAmount($user_id) {

}