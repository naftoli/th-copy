<?
$cat = $_GET['task'];
$subject_id = $_GET['subject'];
$school = isset( $_GET['school'] ) ? $_GET['school'] : 0;
$class = isset( $_GET['grade'] ) ? $_GET['grade'] : 0;
$user = $_GET['user'];
$start = $_GET['start'];
$end = $_GET['end'];

require_once '../db.php';
require_once '../class.tasksCustomizationNew.php';

$tc = new TasksCustomizationNew;
$tc->setStart( $start );
$tc->setEnd( $end );
$tc->setType( $user, $class, $school );
$missions = $tc->getMissions( $cat, $subject_id );
echo json_encode($missions);
?>