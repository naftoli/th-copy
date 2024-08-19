<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once 'class.TehillimTasks.php';

if ($admin_user['auth'] != 'super') {
    echo "You are not authorized to run this script.";
    exit;
}

$year = GlobalSettings::getRegistrationYear();
$tt = new TehillimTasks($year, $MASHPIA_DB);
$quotas = $tt->getQuotas();

echo "<pre>"; print_r($quotas); echo "</pre>";