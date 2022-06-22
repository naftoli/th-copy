<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
require 'class.points.php';

$user_id = $_GET['id'];
$p = new Points( $user_id );
$p->setDebugOn();
//echo $p->getV2Points();
//exit;
echo "Total Points: " . $p->getTotalPoints() . "<br /><br />";
echo "Total this year: " . $p->getTotalThisYear() . "<br /><br />";
echo "Store Points: " . $p->getStorePoints();

//$taskDetails = $p->getTasksPointsDetails();
//$storeDetails = $p->getStorePointsDetails();
//echo "<pre>";
//print_r( $storeDetails );
//echo "</pre>";
?>