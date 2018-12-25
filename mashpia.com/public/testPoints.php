<?php
$admin_auth = array('school');
require 'header.php';
require 'class.points.php';

$user_id = $_GET['id'];
$p = new Points( $user_id );
$p->setDebugOn();
echo "Total Points: " . $p->getTotalPoints() . "<br />";
echo "Total this year: " . $p->getTotalThisYear() . "<br />";
echo "Store Points: " . $p->getStorePoints();
?>