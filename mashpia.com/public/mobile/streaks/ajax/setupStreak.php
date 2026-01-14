<?php
ini_set("display_errors", 1);
ini_set('error_reporting', E_ALL);
define( "MASHPIA_AUTH_REQUIRED", true );
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mobile/streaks/classes/class.streaks.php';

$streakId = intval($_POST['streakId']);
$userId = intval($_POST['userId']);
$end = unixtojd();
$start = $end - 90;

$streaks = new Streaks($userId, $start, $end);
$success = $streaks->setupStreak($streakId);
$error = $streaks->getError();
echo json_encode(['success' => $success, 'error' => $error]);