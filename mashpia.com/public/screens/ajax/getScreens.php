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
    SELECT s.*, 
           COALESCE(ss.show_promotions, 0) as show_promotions,
           COALESCE(ss.promotions_days, 7) as promotions_days,
           COALESCE(ss.promotions_gender, '0') as promotions_gender,
           COALESCE(ss.show_birthdays, 0) as show_birthdays,
           COALESCE(ss.birthdays_days, 7) as birthdays_days,
           COALESCE(ss.birthdays_gender, '0') as birthdays_gender,
           COALESCE(ss.show_chidon, 0) as show_chidon,
           COALESCE(ss.show_chayolei, 0) as show_chayolei
    FROM screens s
    LEFT JOIN screen_settings ss ON s.screen_id = ss.screen_id
    WHERE s.school_id IN (" . implode(',', $school_ids) . ") 
    ORDER BY s.school_id DESC
");

$screens_array = [];
foreach ($screens as $screen) {
    $school_name = $schools[$screen['school_id']];
    $screen['school_name'] = $school_name;
    $screens_array[$screen['school_id']][] = $screen;
}

echo json_encode($screens_array);