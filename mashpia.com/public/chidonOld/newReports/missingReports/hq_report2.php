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
    $totals = [];
    foreach ($users as $id => $user) {
        // if logged in as regular school, don't show all kids
        if (!in_array($user['school_id'], array_keys($schools))) continue;
        $grade = $user['class_grade'] . ($user['class_sub'] ? '-' . $user['class_sub'] : '');
        echo "<tr><td>" . $user['user_serial'] . "</td><td>" . $schools[$user['school_id']] . "</td><td>" . $grade .
            "</td><td>" . ($user['first'] . ' ' . $user['last']) . "</td><td>";
        foreach ($items[$id] as $item) {
            $desc = parseItem($item, $user);
            echo $desc . "<br />";
            if (isset($totals[$desc])) $totals[$desc]++;
            else $totals[$desc] = 1;
        }
        echo "</td></tr>";
    }
    // sort totals
    ksort($totals);
    echo "</table>";
    echo "<br /><br />";
    echo "<h3>Totals</h3>";
    echo "<table><tr><th>Item</th><th>Amount</th></tr>";
    foreach ($totals as $item => $total) {
        if (strpos($item, 'Comment:') === false)
            echo "<tr><td>" . $item . "</td><td>" . $total . "</td></tr>";
    }
    ?>
</table>
</body>
</html>