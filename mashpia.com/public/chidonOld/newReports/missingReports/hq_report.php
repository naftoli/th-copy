<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . "/header.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/class.adminSchools.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/class.globalSettings.php";

$year = GlobalSettings::getChidonYear();
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

require 'functions.php';

$items = getAllMissingItems();
$users = getMissingUsers($items);
$recruitmentPrizes = getRecruitmentPrizes();
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Chidon Missing Items Report</title>
    <link href="../../../admin_styles.css" rel="stylesheet" type="text/css">
    <style>
        tr, th, td {
            font-size: 14px;
            padding: 10px;
            border-bottom: 1px solid grey;
        }
    </style>
</head>
<body>
    <?php include('../../../admin_header.php'); ?>
    <h1>Chidon Missing Items Report</h1>
    <table>
        <tr>
            <th>Serial Number</th>
            <th>School</th>
            <th>Grade</th>
            <th>Student</th>
            <th>Missing Items</th>
        </tr>
        <?php
        foreach ($users as $id => $user) {
            echo "User ID: " . $id . "<br />";
            echo "<pre>"; print_r($items); echo "</pre>"; exit;
            $grade = $user['class_grade'] . ($user['class_sub'] ? '-' . $user['class_sub'] : '');
            echo "<tr><td>" . $user['user_serial'] . "</td><td>" . $schools[$user['school_id']] . "</td><td>" . $grade .
                "</td><td>" . ($user['first'] . ' ' . $user['last']) . "</td><td>";
            foreach ($items[$id] as $item) {
                echo parseItem($item, $user) . "<br />";
            }
            echo "</td></tr>";
        }
        ?>
    </table>
</body>
</html>