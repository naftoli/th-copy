<?
$campaigns = array();
$user = $_GET['id'];

require_once '../db.php';
require_once '../class.tasksCustomizationNew.php';
$tc = new TasksCustomizationNew;
$campaigns = $tc->getCampaignsForChild( $user );

echo json_encode($campaigns);
?>