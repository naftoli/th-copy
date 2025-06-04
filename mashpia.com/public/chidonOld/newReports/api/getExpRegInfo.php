<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
set_time_limit(300);
ini_set('max_execution_time', 300);

require_once $_SERVER['DOCUMENT_ROOT'] . '/utils/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$db = getDbHandle();
$schools = getSchools();
$year = $_GET['year'] && $_GET['year'] > 0 ? $_GET['year'] : getChidonYear();
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
        tc.ultimate_trip, 
        tc.confirmed_info, 
        u.user_id,
        u.user_serial,
        u.first,
        u.last,
        u.school_id, 
        u.class_id,
        c.class_grade,
        c.class_sub, 
        aa.admin_id  
    FROM
        th_chidon tc
            JOIN
        users u USING (user_id)
            JOIN
        classes c ON c.class_id = u.class_id
            JOIN
        admin_auths aa ON aa.id = u.user_id
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
    $khk_eligibility = KHK::getUltimateTripEligibility($ids)[0];
    foreach ($rows as $row) {
        $ct->setStudents($row['school_id'], $row['class_id'], $row['user_id']);
        $ct->setScores();
        $ct->calculateMarks();
        $marks = $ct->getMarks();
        $highest_track = $ct->getHighestTrack($marks[$row['th_chidon_id']], $row['user_id']);
        $scores = $ct->getScores();
        // check if child passed Iyun through cumulative marks
        $cumulative = $ct->calculateCumulative($row, $scores[$row['th_chidon_id']]);
        if ($cumulative == 'iyun') $highest_track = 'genius';
        // setup row with needed info
        $row['highest_track'] = $highest_track;
        $row['grade'] = $row['class_grade'] . ($row['class_sub'] ? '-' . $row['class_sub'] : '');
        $row['khk_eligible'] = $row['class_grade'] == '8' ? $khk_eligibility[$row['user_id']] ? 1 : 0 : 0;
        $row['khk_passed_tests'] = getKhkPassed($row);
        $row['reward'] = getReward($row);
        $row['award'] = getAward($row);
        $row['raised'] = getRaised($row);
        $row['fee'] = getFee($row);
        $row['trip'] = getTrip($row);
        $row['shipping'] = in_array($row['school_id'], [61, 269]) ? getShippingInfo($row) : '';
        $row['credit'] = getPersonalCredit($row);
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
    if (empty($award) || $award == 'highest final passed') return $row['highest_track'];
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
        'genius'    => 200
    ];
    $reward = $row['reward'];
    $fee = array_key_exists($reward, $fees) ? $fees[$reward] : 0;
    // check if already paid for registration
    if (intval($row['paid']) > 0) $fee -= intval($row['paid']);
    // check how much was raised
    if ($row['raised'] > 0) $fee -= intval($row['raised']);
    // zero out negative fees
    if ($fee < 0) $fee = 0;
    return $fee;
}

function getTrip($row) {
    switch ($row['trip']) {
        case 'east':
            return 'East Coast';
            break;
        case 'west':
            return 'West Coast';
            break;
        case 'europe':
            return 'Europe';
            break;
        case 'no_trip':
            return 'No Trip';
            break;
        default:
            return '';
            break;
    }
}

function getExtraPurchases($row) {
    return '';
}

function getShippingInfo($row) {
    // check if there's a shipping code in the db for any children in this admin
    global $db, $year;

    $stmt = $db->prepare("
        SELECT * FROM registration_charges where year = :year and admin_id = :admin AND type IN ('RRSUSA', 'RRSCAN', 'RRSINT') 
    ");
   $stmt->execute([
        ':year' => $year,
        ':admin'   => $row['admin_id']
    ]);
    // find out if there's any rows
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return count($rows) > 0 ? 'shipping' : 'pickup';
}

function getPersonalCredit($row) {
    global $db, $year;
    
    $stmt = $db->prepare("
        SELECT IFNULL(SUM(amount), 0) as total FROM registration_charges WHERE year = :year AND admin_id = :admin AND type = 'RRFAM'
    ");
    $res = $stmt->execute([
        ':year' => $year,
        ':admin' => $row['admin_id']
    ]);
    if ($res) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    } else {
        return 0;
    }
}