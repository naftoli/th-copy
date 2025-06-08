<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . "/header.php";

if ($admin_user['auth'] != 'super') {
    echo "No permission to be here.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$from_yr = GlobalSettings::getChidonRegYear() - 4;

// get all children from th_chidon from past years
$stmt = $MASHPIA_DB->prepare("
   SELECT 
        user_id 
    FROM
        th_chidon tc 
        JOIN users u ON u.user_id = tc.user_id 
        JOIN classes c ON c.class_id = u.class_id 
        JOIN schools s ON s.school_id = u.school_id
    WHERE
        tc.year >= :yr
");
$stmt->execute([
    ':yr' => $from_yr
]);
$stmt->debugDumpParams();
$children = $stmt->fetchAll();

$ids = array_map(function ($child) {
    return $child['user_id'];
}, $children);
$history = KHK::getEligibilityFromHistory($ids, $from_yr);
echo "<pre>"; print_r($children); print_r($ids); print_r($history); echo "</pre>";
    