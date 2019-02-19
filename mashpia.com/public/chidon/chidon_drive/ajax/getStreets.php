<?php
require '../classes/WalkingZones.php';
$w = new WalkingZones;
$streets = $w->getStreets();
if ( $streets ) {
  echo json_encode([
    'success'   =>  true, 
    'streets'   =>  $streets
  ]);
}