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
            $child['award'] = $award;
            $sorted[$award][] = $child;
        }
    }
    foreach ($tracks as $track) {
        if (count($sorted[$track])) {
            $newSheet = [];
            $sheets = createAwardCeremonyData($sorted[$track]);
            foreach ($sheets as $sheet) $newSheet += $sheet;
            $file_name = $track . "_" . strtolower($gender) . ".csv";
            createFile($file_name, $newSheet, true);
        }
    }
}

downloadFile();