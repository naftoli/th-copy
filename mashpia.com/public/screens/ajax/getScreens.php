<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once __DIR__ . '/../../header.php';
require_once __DIR__ . '/../../api/header/db.php';
require_once __DIR__ . '/../../class.adminSchools.php';

$adminSchools = new adminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $adminSchools->getSchools();
$school_ids = array_keys($schools);

$screens = $MASHPIA_DB->query("
    SELECT * FROM screens 
    WHERE school_id IN (" . implode(',', $school_ids) . ") 
    ORDER BY school_id DESC
");

$screens_array = [];
foreach ($screens as $screen) {
    $school_name = $schools[$screen['school_id']];
    $screen['school_name'] = $school_name;
    $screens_array[$screen['school_id']][] = $screen;
}

echo json_encode($screens_array);