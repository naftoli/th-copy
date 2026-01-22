<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
set_time_limit(300);
ini_set('max_execution_time', 300);

require_once $_SERVER['DOCUMENT_ROOT'] . '/utils/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$db = getDbHandle();
$chidon_yr = getChidonYear();
$year = $_GET['year'] && $_GET['year'] > 0 ? $_GET['year'] : $chidon_yr;
$schools = getSchools(intval($chidon_yr) != intval($year));
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
        aa.admin_id, 
        tci.highest_track 
    FROM
        th_chidon tc
            JOIN
        users u USING (user_id)
            JOIN
        classes c ON c.class_id = u.class_id
            JOIN
        admin_auths aa ON aa.id = u.user_id 
            LEFT JOIN 
        th_chidon_info tci on tc.year = tci.year and tc.user_id = tci.user_id 
    WHERE
        tc.year = :year ";
if ($admin_user['auth'] != 'super') {
    $sql .= " AND u.school_id in (" . implode(',', array_keys($schools)) . ")";
}
$sql .= " ORDER BY u.school_id, c.class_grade, c.class_sub, u.last, u.first";
$stmt = $db->prepare($sql);
$res = $stmt->execute([
    'year' => $year
]);
//$stmt->debugDumpParams();
$actual_schools = [];
$info = [];
if ($res) {
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $ids = array_map(function($row) { return $row['user_id']; }, $rows);
    $khk_eligibility = KHK::getUltimateTripEligibility($ids)[0];
    $khk_marks = getKhKMarks($year);
    $raised = getAllRaised($year);
    $family_credit = getAllFamilyCredit();
    $personal_credit = getAllPersonalCredit();
    $ct->overrideStudents($rows);
    $ct->setScores();
    $ct->calculateMarks();
    $marks = $ct->getMarks();
    $scores = $ct->getScores();
    foreach ($rows as $row) {
        $highest_track = $ct->getHighestTrack($marks[$row['th_chidon_id']], $row['user_id'], false, 3, false, false, $row['highest_track']);
        // check if child passed Iyun through cumulative marks
        $cumulative = $ct->calculateCumulative($row, $scores[$row['th_chidon_id']]);
        if ($cumulative == 'iyun') $highest_track = 'genius';
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
        $row['personal_credit'] = getPersonalCredit($row);
        $row['family_credit'] = getFamilyCredit($row);
        $row['coupon_credit'] = getCouponCredit($row);
        $info[$row['school_id']][] = $row;
        $actual_schools[$row['school_id']] = $schools[$row['school_id']];
    }
}

echo json_encode([
    'success'   => $res,
    'info'      => $info,
    'error'     => $db->errorInfo()[2] ?? '',
    'super'     => getAuth() == 'super' ? 1 : 0,
    'types'     => $types,
    'schools'   => $actual_schools
]);

function getKhKMarks($year) {
    global $db;
    $info = [];
    $sql = "select * from th_khk_marks tkm 
            join th_chidon tc on tkm.th_chidon_id = tc.th_chidon_id         
            where tc.year = :year";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':year' => $year
    ]);
    $marks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($marks as $mark) {
        $info[$mark['th_chidon_id']][] = $mark;
    }
    return $info;
}

function getKhkPassed($row) {
    if (! $row['khk_reg']) return false;
    if (! $row['th_chidon_id']) return false;
    else {
        global $khk_marks;
        $marks = $khk_marks[$row['th_chidon_id']];

        $total = 0;
        $num_tests = 0;
        $passing_mark = 70;
        foreach ($marks as $mark) {
            $total += $mark['mark'];
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

function getCouponCredit($row) {
    global $db, $year;

    $sql = "
        SELECT 
            user_id, IFNULL( SUM(value), 0 ) AS total 
        FROM
            coupon_codes
        WHERE
            year = :year 
            AND serial_num = (
                SELECT user_serial FROM users WHERE user_id = :user_id
            )
            AND type = 'chidon' 
            AND date_redeemed is null 
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':year' => $year,
        ':user_id' => $row['user_id']
    ]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['total'] ?? 0;
}

function getAllRaised($year) {
    global $db;

    $raised = [];
    $sql = "
        SELECT 
            user_id, IFNULL( SUM(subsidy_amount), 0 ) AS total 
        FROM
            chidon_user_subsidies
        WHERE
            chidon_year = :year 
        GROUP BY 
            user_id
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':year' => $year
    ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $raised[$row['user_id']] = $row['total'];
    }
    return $raised;
}

function getRaised($row) {
    global $raised;
    return $raised[$row['user_id']] ?? 0;
}

function getFee($row) {
    $fees = [
        'maven'     => 50,
        'pro'       => 105,
        'expert'    => 205,
        'genius'    => 205
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

function getAllShippingInfo() {
    global $db, $year;

    $shipping = [];
    $stmt = $db->prepare("
        SELECT * FROM registration_charges where year = :year 
        AND type IN ('RRSUSA', 'RRSCAN', 'RRSINT') 
        GROUP BY admin_id
    ");
    $stmt->execute([
        ':year' => $year
    ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $shipping[$row['admin_id']] = $row;
    }
    return $shipping;
}

function getShippingInfo($row) {
    // check if there's a shipping code in the db for any children in this admin
    global $shipping;
    return isset($shipping[$row['admin_id']]) ? 'shipping' : 'pickup';
}

function getAllFamilyCredit() {
    global $db, $year;
    
    $credit = [];
    $stmt = $db->prepare("
        SELECT IFNULL(SUM(amount), 0) as total, admin_id 
        FROM registration_charges 
        WHERE year = :year AND type = 'RRFAM'
        GROUP BY admin_id
    ");
    $stmt->execute([
        ':year' => $year
    ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $credit[$row['admin_id']] = $row['total'];
    }
    return $credit;
}

function getFamilyCredit($row) {
    global $family_credit;
    return $family_credit[$row['user_id']] ?? 0;
}

function getAllPersonalCredit() {
    global $db, $year;
    
    $credit = [];
    $stmt = $db->prepare("
        SELECT IFNULL(SUM(amount), 0) as total, user_id  
        FROM registration_charges 
        WHERE year = :year AND type in ('RRYSD', 'RRYDA', 'RRHVN')
        GROUP BY user_id 
    ");
    $stmt->execute([
        ':year' => $year
    ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $credit[$row['user_id']] = $row['total'];
    }
    return $credit;
}

function getPersonalCredit($row) {
    global $personal_credit;
    return $personal_credit[$row['user_id']] ?? 0;
}