<?php
require $_SERVER['DOCUMENT_ROOT'] . '/chidonOld/chidon_drive/classes/WalkingZones.php';
$w = new WalkingZones;
$num = $_POST['num'];
$street = $_POST['street'];
$cross = $w->getCrossStreets( $street, $num );
if ( !is_array( $cross ) ) {
  echo json_encode([
    'success'   =>  false,
    'error'     =>  $cross
  ]);
} else {
  echo json_encode([
    'success'   =>  true,
    'data'      =>  $cross
  ]);
}