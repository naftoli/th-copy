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
$stmt = $MASHPIA_DB->prepare("
   SELECT 
        *  
    FROM
        th_chidon tc 
        JOIN users u ON u.user_id = tc.user_id 
        JOIN classes c ON c.class_id = u.class_id 
        JOIN schools s ON s.school_id = u.school_id
    WHERE
        tc.year >= :yr
");
$stmt->execute([
    ':yr' => $from_yr
]);
$children = $stmt->fetchAll();

$ids = array_map(function ($child) {
    return $child['user_id'];
}, $children);
$history = KHK::getEligibilityFromHistory($ids, $from_yr);
echo "<pre>"; print_r($history); echo "</pre>";
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
            font-size: 12px;
            border-bottom: 1px solid #ddd;
            padding: 5px;
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
                <th>School</th>
                <th>Grade</th>
                <th>First Name</th>
                <th>Last Name</th>
                <?php for ($i = $from_yr; $i <= GlobalSettings::getChidonRegYear(); $i++) { ?>
                    <th><?= $i ?></th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($children as $child) { ?>
            <tr>
                <td><?= $child['school_name'] ?></td>
                <td><?= ($child['class_grade'] . empty($child['class_sub']) ? '' : ' ' . $child['class_sub']) ?></td>
                <td><?= $child['first'] ?></td>
                <td><?= $child['last'] ?></td>
                <?php for ($i = $from_yr; $i <= GlobalSettings::getChidonRegYear(); $i++) { ?>
                    <td><?= isset($history[$child['user_id']][$i]) ? $history[$child['user_id']][$i] : '' ?></td>
                <?php } ?>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
    