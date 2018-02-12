<?
$campaigns = array();
$school = $_GET['id'];

require_once '../db.php';
require_once '../class.tasksCustomizationNew.php';
$tc = new TasksCustomizationNew;
$campaigns = $tc->getCampaigns( $school );

echo json_encode($campaigns);
?>