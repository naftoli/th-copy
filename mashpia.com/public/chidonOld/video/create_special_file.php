<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo 'No Permission to be here.';
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require_once $_SERVER['DOCUMENT_ROOT'] . '/chidon_shipping/class.chidonShipping.php';
$cs = new ChidonShipping($year);

require 'functions.php';

$prizes = getUserPrizes();
$marks = getMarks();
$final_marks = getFinalMarks();

$school_id = 61;
$users = [];

$children = getChildren($school_id, 'all', $users);
if (!empty($children)) {
    $sheet = createSpreadSheet($children);
    $file_name = 'special.tsv';
    createFile($file_name, $sheet);
}

downloadFile();