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
$itemsBySchool = getItemsBySchool($items, $users);
$recruitmentPrizes = getRecruitmentPrizes();
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Chidon Missing Items Shipping Report</title>
    <style>
        body {
            font-family: Arial, Verdana, sans-serif;
            font-size: 14px;
        }
        tr, th, td {
            font-size: 14px;
            padding: 10px;
            border-bottom: 1px solid grey;
        }
    </style>
</head>
<body>
    <h1>Chidon Missing Items Shipping Report</h1>
    <?php
    echo "<pre>"; print_r($itemsBySchool); echo "</pre>";
    foreach ($itemsBySchool as $school_id => $item) {
        echo "<h2>" . $schools[$school_id] . "</h2>";
        $info = [];
        foreach ($item as $desc => $details) {
            foreach ($details as $value) {
                if (isset($info[$desc][$value])) $info[$desc][$value]++;
                else $info[$desc][$value] = 1;
            }
        }
        echo "<pre>"; print_r($info); echo "</pre>";
    }
    ?>
</body>
</html>