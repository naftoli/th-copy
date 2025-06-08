<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . "/header.php";

if ($admin_user['auth'] != 'super') {
    echo "No permission to be here.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$from_yr = GlobalSettings::getChidonRegYear() - 4;

// get all children from th_chidon from past years
$info = [];
$stmt = $MASHPIA_DB->prepare("
    SELECT 
        tc.th_chidon_id,
        tc.user_id, 
        tc.year, 
        tc.reg_date, 
        tc.date_paid, 
        tc.test_type, 
        tc.reward_type, 
        tc.award_type, 
        u.first,
        u.last,
        c.class_grade,
        c.class_sub,
        s.school_name
    FROM
        th_chidon tc 
        JOIN users u ON u.user_id = tc.user_id 
        JOIN classes c ON c.class_id = u.class_id 
        JOIN schools s ON s.school_id = u.school_id
    WHERE
        tc.year >= :yr AND tc.year != :cur_yr
    ORDER BY
        s.school_name, c.class_grade, c.class_sub, u.last, u.first
");
$stmt->execute([
    ':yr' => $from_yr,
    ':cur_yr' => GlobalSettings::getChidonYear()
]);
$children = $stmt->fetchAll();
foreach ($children as $child) {
    $info[$child['user_id']][$child['year']] = $child;
}

$marks = [];
$stmt = $MASHPIA_DB->prepare("SELECT * FROM th_chidon_marks WHERE th_chidon_id IN (SELECT th_chidon_id FROM th_chidon WHERE year >= :yr)");
$stmt->execute([
    ':yr' => $from_yr
]);
$rows = $stmt->fetchAll();
foreach ($rows as $row) {
    $marks[$row['th_chidon_id']][] = $row;
}

$data = [];
foreach ($info as $user_id => $years) {
    foreach ($years as $year => $details) {
        $data[$user_id][$year] = $details;
        $data[$user_id][$year]['marks'] = isset($marks[$details['th_chidon_id']]) ? $marks[$details['th_chidon_id']] : [];
    }
}
echo "<pre>"; print_r($data); echo "</pre>";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Eligibility History</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        tr, th, td {
            font-family: Arial, sans-serif;
            font-size: 14px;
            border-bottom: 1px solid #ddd;
            padding: 10px;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                
            </tr>
        </thead>
        <tbody>
            
        </tbody>
    </table>
</body>
</html>