<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

if ($admin_user['auth'] != 'super') {
    die('Access denied');
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require_once 'class.chidonShipping.php';
$cs = new ChidonShipping($year);

require 'data.php';

$purchases = $cs->getExtraPurchases('', 0, ['celebration boxes']);
$status = $cs->getStatus();

echo "<pre>";
print_r($purchases[61577]);
print_r($status[61577]);
echo "</pre>";
