<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('max_execution_time', 300);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission to be here.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require 'functions.php';

$genders = ['M', 'F'];
$tracks = ['yesod', 'yediah', 'havonah', 'iyun'];
$final_marks = getFinalMarks();

foreach ($genders as $gender) {
    $children = getAllChildrenByGender($gender);
    $sorted = [];
    foreach ($children as $child) {
        $award = getAward($child);
        if ($award) {
            $award = $tracks[$award-1];
            $child['award'] = $award;
            $sorted[$award][] = $child;
        }
    }
    foreach ($tracks as $track) {
        if (isset($sorted[$track])) {
            $sheets = createAwardCeremonyData($sorted[$track]);
            $file_name = 'sheets/' . $track . "_" . strtolower($gender) . ".csv";
            createFile($file_name, $sheets, true);
        }
    }
}

$dir = "sheets/";
downloadFile($dir);