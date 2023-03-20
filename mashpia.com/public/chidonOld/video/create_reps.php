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

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$gender = $_REQUEST['type'];
require 'functions.php';

foreach ($schools as $school_id => $school) {
    $children = getSchoolReps($school_id, $gender);
    if (! empty($children)) {
        $sheet = createRepsSheet($children, $school_id, $gender);
        $file_name = $school . ".tsv";
        createFile($file_name, $sheet, false, '');
    }
}

downloadFile();