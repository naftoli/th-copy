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
$users = [7758285, 7758286, 7777839, 7757841, 7763853, 7763907, 7774646, 7774762, 7788190, 7775731, 7788241, 7753604,
    7754164, 7754206, 7756274, 7756301, 7757958, 7758437, 7758438, 7758709, 7758711, 7759739, 7760063, 7760210,
    7760764, 7762147, 7763343, 7763360, 7763365, 7763862, 7763894, 7763985, 7766276, 7769591, 7769592, 7769938,
    7770733, 7770774, 7770845, 7771505, 7771766, 7772497, 7773258, 7773291, 7773335, 7773759, 7774054, 7774148,
    7774227, 7774443, 7774536, 7775239, 7775522, 7775598, 7775749, 7776626, 7776716, 7777314, 7777427, 7777840,
    7778217];

$children = getChildren($school_id, 'all', $users);
if (!empty($children)) {
    $sheet = createSpreadSheet($children);
    $file_name = 'special.tsv';
    createFile($file_name, $sheet);
}

downloadFile();