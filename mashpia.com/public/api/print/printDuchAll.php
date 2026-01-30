<?php
ini_set('max_execution_time', 600);
ini_set('memory_limit', '3072M');

require_once( '../header/header.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/missions.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/noPicMission.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/picMission.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/DSMission.php' );
require_once $_SERVER['DOCUMENT_ROOT'] . '/mobile/streaks/classes/class.streaks.php';
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mobile/streaks/classes/class.accomplished.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/duch_task.php' );

$date_range = 30;
$end = unixtojd();
$start = $end - $date_range + 1; // one less b/c we include start and end date in total number of days

// * Generate the missions using the legacy code
$mission = new Missions( $start, $end, 0, intval($_GET['id']), 0, true, true, true );
$missions = $mission->getMissions();

echo json_encode($missions);