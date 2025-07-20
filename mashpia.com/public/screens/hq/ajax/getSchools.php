<?php
$admin_auth = ['school'];
require_once __DIR__ . '/../../../header.php';

if ($admin_user['auth'] != 'super') {
    echo json_encode([]);
    exit;
}

$info = [];
require_once __DIR__ . '/../../../class.adminSchools.php';
$adminSchools = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $adminSchools->getSchools();
foreach ($schools as $school_id => $school_name) {
    $info[] = [
        'id' => $school_id,
        'name' => $school_name
    ];
}echo json_encode($info);
