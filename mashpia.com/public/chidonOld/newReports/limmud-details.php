<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

if (! (isset($_GET['id']) && isset($GET['test']))) {
    header('Location: limmud-report.php');
    exit;
}

$year = GlobalSettings::getChidonYear();
$chidon = new ChidonTests();
$user_id = $_GET['id'];
$test_num = $_GET['test'];
$info = $chidon->getLimmudInfo($user_id, $test_num);

require_once 'codeForReport.php';
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Limmud Detailed Report</title>
        <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
        <style>
          tr, th, td {
            font-size: 14px;
            padding: 6px;
          }
        </style>
    </head>
    <body>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
        <h1>Limmud Detailed Report</h1>

    </body>
</html>