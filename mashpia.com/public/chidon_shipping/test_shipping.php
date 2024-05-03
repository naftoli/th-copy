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

$prizes = $cs->getAmbassadorPrizes('', 0);
echo "<pre>"; print_r($prizes); echo "</pre>";