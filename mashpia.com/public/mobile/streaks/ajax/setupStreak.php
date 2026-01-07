<?php
ini_set("display_errors", 1);
ini_set('error_reporting', E_ALL);
define( "MASHPIA_AUTH_REQUIRED", true );
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mobile/streaks/classes/class.streaks.php';

$gridId = intval($_POST['gridId']);
$userId = intval($_POST['userId']);
$dates = GlobalSettings::getCurYearDates();
$start = $dates['start'];
$end = $dates['end'];
$numDays = 90;

$streaks = new Streaks($userId, $start, $end, $numDays);
$success = $streaks->setupStreak($gridId);

echo json_encode(['success' => $success]);