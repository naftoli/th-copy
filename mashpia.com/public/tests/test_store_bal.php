<?php
require '../db.php';
require '../class.points.php';

if (isset($_GET['id'])) $user_id = $_GET['id'];
else $user_id = 8273;
$p = new Points( $user_id );
$p->setDebugOn();
$bal = $p->getStorePoints();
echo $bal;

$points = $p->getTotalThisYear();
echo "<br />" . $points;