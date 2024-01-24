<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/utils/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

checkAuth();
$db = getDbHandle();
$schools = getSchools();
$year = getChidonYear();
$ct = new ChidonTests($year);
$types = $ct->getTypes();

$sql = "
    SELECT 
        tc.th_chidon_id,
        tc.paid, 
        tc.date_paid, 
        tc.test_type,
        tc.reward_type,
        tc.award_type,
        tc.trip, 
        tc.khk_experience,
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
    'year' => $year,
]);
//$stmt->debugDumpParams();
$info = [];
if ($res) {
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $ct->setStudents($row['school_id'], $row['class_id'], $row['user_id']);
        $ct->setScores();
        $ct->calculateMarks();
        $marks = $ct->getMarks();
        $highest_track = $ct->getHighestTrack($marks[$row['th_chidon_id']], $row['user_id']);
        // setup row with needed info
        $row['marks'] = $marks;
        $row['highest_track'] = $highest_track;
        $row['grade'] = $row['class_grade'] . ($row['class_sub'] ? '-' . $row['class_sub'] : '');
        $row['reward'] = in_array($row['reward_type'], ['highest track passed', '']) ? $highest_track : $row['reward_type'];
        $row['award'] = in_array($row['award_type'], ['highest track passed', '']) ? $highest_track : $row['award_type'];
        $row['khk'] = getKHK($row);
        $row['fee'] = getFee($row);
        $row['raised'] = getRaised($row);
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
    'types'     => $types
]);

function getKHK($row) {
    global $year;
    return KHK::getKHKEligibility([$row['user_id']], ($year - 1))[0][$row['user_id']] == 1 ? 1 : 0;
}

function getFee($row) {
    $fees = [
        'maven' => 36,
        'pro'   => 100,
        'expert' => 200,
        'genius' => 200,
        'khk'    => 350
    ];
    if ($row['khk']) return $fees['khk'];
    else return $fees[$row['reward']];
}

function getRaised($row) {
    global $year;
    $db = getDbHandle();
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

function getTrip($row) {
    if ($row['khk_experience']) return 'KHK Trip';
    else return $row['trip'] ? $row['trip'] . ' Trip' : '';
}

function getExtraPurchases($row) {
    return '';
}