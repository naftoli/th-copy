<?php
$admin_auth = ['school'];
require_once '../../header.php';

if ($admin_user['auth'] != 'super') {
    echo "Not authorized";
    exit;
}

require_once '../../class.adminSchools.php';
$adminSchools = new adminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $adminSchools->getSchools();

echo json_encode($schools);