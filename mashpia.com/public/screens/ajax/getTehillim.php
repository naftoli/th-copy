<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once __DIR__ . '/../../header/header.php';
require_once __DIR__ . '/../../class.shabbosMevorchim.php';
$sm = new ShabbosMevorchim();

require_once __DIR__ . '/../../class.adminSchools.php';
$adminSchools = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $adminSchools->getSchools();

