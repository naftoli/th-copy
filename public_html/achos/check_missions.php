<?
require_once 'db.php';
require_once 'class.missionMarks.php';

$mm = new MissionMarks(42, 0, 2456607, 2456613);
$mm->checkMissionCompletion();
?>
