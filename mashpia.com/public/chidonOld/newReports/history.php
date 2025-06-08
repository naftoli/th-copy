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
        u.user_serial,
        c.class_grade,
        c.class_sub,
        s.school_name
    FROM
        th_chidon tc 
        JOIN users u ON u.user_id = tc.user_id 
        JOIN classes c ON c.class_id = u.class_id 
        JOIN schools s ON s.school_id = u.school_id
    WHERE
        tc.year >= :yr AND tc.year < :cur_yr
    ORDER BY
        tc.year, s.school_name, c.class_grade, c.class_sub, u.last, u.first
");
$stmt->execute([
    ':yr' => $from_yr,
    ':cur_yr' => GlobalSettings::getChidonYear()
]);
$children = $stmt->fetchAll();
foreach ($children as $child) {
    $info[$child['year']][$child['user_id']] = $child;
}

$marks = [];
$stmt = $MASHPIA_DB->prepare("
    SELECT 
        * 
    FROM
        th_chidon_marks 
    WHERE
        th_chidon_id IN (SELECT th_chidon_id FROM th_chidon WHERE year >= :yr) 
    ORDER BY
        th_chidon_id, test_type, test_number
");
$stmt->execute([
    ':yr' => $from_yr
]);
$rows = $stmt->fetchAll();
foreach ($rows as $row) {
    $marks[$row['th_chidon_id']][$row['test_type']][] = $row;
}

$data = [];
foreach ($info as $year => $users) {
    foreach ($users as $user_id => $user) {
        $data[$year][$user_id] = $user;
        $data[$year][$user_id]['marks'] = isset($marks[$user['th_chidon_id']]) ? $marks[$user['th_chidon_id']] : [];
    }
}
// echo "<pre>"; print_r($data); echo "</pre>";

$years = [];
foreach ($data as $year => $users) {
    $years[] = $year;
}

$types = [
    'maven' => 'Yesod',
    'pro'   => 'Yediah',
    'expert'=> 'Havonah',
    'genius'=> 'Iyun'
];

$fields = [
    'year' => 'Year',
    'school_name' => 'School', 
    'class_grade' => 'Grade', 
    'class_sub' => 'Sub', 
    'user_serial' => 'Serial', 
    'first' => 'First', 
    'last' => 'Last', 
    'reg_date' => 'Enrollment Date', 
    'date_paid' => 'Registration Date', 
    'test_type' => 'Track Signed Up For', 
    'reward_type' => 'Reward Override', 
    'award_type' => 'Award Override', 
    'marks' => 'Marks'
];

$mark_fields = [
    'test_number' => 'Test Number',
    'answered_correctly' => 'Answered Correctly',
    'total_questions'   => 'Total Questions', 
    'level' => 'Level'
];
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
                <?php foreach ($fields as $field => $label) { ?>
                    <th><?= $label ?></th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php 
            foreach ($data as $year => $users) { 
                foreach ($users as $user_id => $user) { 
                    ?>
                    <tr>
                        <?php foreach ($fields as $field => $label) { ?>
                            <td>
                                <?php 
                                if ($field == 'marks') {
                                    $marks = $user['marks'];
                                    if (empty($marks)) {
                                        echo 'N/A';
                                    } else {
                                    // create table for marks
                                    ?>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Track</th>
                                                <?php foreach ($mark_fields as $field => $label) { ?>
                                                    <th><?= $label ?></th>
                                                <?php } ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($marks as $track => $details) { ?>
                                                <tr>
                                                    <td><?= $types[$track] ?></td>
                                                    <?php foreach ($mark_fields as $field => $label) { ?>
                                                        <td><?= isset($details[$field]) ? $details[$field] : 'N/A' ?></td>
                                                    <?php } ?>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                    <?php
                                    }
                                } else if (in_array($field, ['test_type', 'reward_type', 'award_type'])) {
                                    echo isset($types[$user[$field]]) ? $types[$user[$field]] : 'N/A';
                                } else {
                                    echo isset($user[$field]) ? $user[$field] : 'N/A';
                                }
                                ?>
                            </td>
                        <?php } ?>
                    </tr>
                    <?php
                }
            }
            ?>
        </tbody>
    </table>
</body>
</html>