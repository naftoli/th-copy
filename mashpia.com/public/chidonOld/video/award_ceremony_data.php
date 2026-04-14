<?php
ob_start();
ini_set('max_execution_time', 300);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission to be here.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
$ct = new ChidonTests($year);

require_once $_SERVER['DOCUMENT_ROOT'] . '/chidon_shipping/class.chidonShipping.php';
$cs = new ChidonShipping($year);

require 'functions.php';

$sheet_dir = __DIR__ . '/sheets';
if (!is_dir($sheet_dir)) {
    mkdir($sheet_dir, 0777, true);
}
foreach (scandir($sheet_dir) as $file) {
    if ($file === '.' || $file === '..') continue;
    $path = $sheet_dir . '/' . $file;
    if (is_file($path)) {
        unlink($path);
    }
}

$genders = ['M', 'F'];
$tracks = ['yesod', 'yediah', 'havonah', 'iyun'];
$final_marks = getFinalMarks();

foreach ($genders as $gender) {
    $children = getAllChildrenByGender($gender);
    $sorted = [];
    foreach ($children as $child) {
        $award = getAward($child);
        if ($award) {
            if (is_numeric($award)) {
                $award = $tracks[(int)$award - 1] ?? '';
            } else {
                $award = strtolower((string)$award);
            }

            if ($award && in_array($award, $tracks, true)) {
                $child['award'] = $award;
                $sorted[$award][] = $child;
            }
        }
    }
    foreach ($tracks as $track) {
        if (isset($sorted[$track])) {
            $sheets = createAwardCeremonyData($sorted[$track]);
            $file_name = $sheet_dir . '/' . $track . "_" . strtolower($gender) . ".csv";
            createFile($file_name, $sheets, true);
        }
    }
}

downloadFile($sheet_dir, 'AwardCeremonyData.zip');