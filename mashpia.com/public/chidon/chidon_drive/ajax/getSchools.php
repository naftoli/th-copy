<?php
require '../classes/ChidonDrive.php';
require '../classes/Communities.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$year = GlobalSettings::getChidonYear();
$c = new Communities;
$communities = $c->getCommunities();

$schools = [];
try {
  
} catch ( Exception $e ) {
  echo json_encode([
    'success' =>  false,
    'message' =>  $e->getMessage()
  ]);
}

// sort first by amount then by community name
foreach ( $leaderboard as $percent => $info ) {
  ksort( $leaderboard[$percent] );
}
asort( $leaderboard );

echo json_encode([
  'success' =>  true,
  'data'    =>  $leaderboard
]);