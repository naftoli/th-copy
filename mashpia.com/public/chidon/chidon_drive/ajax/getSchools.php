<?php
require __DIR__ . '/../classes/ChidonDrive.php';
require __DIR__ . '/../classes/Communities.php';
require __DIR__ . '/../../../class.globalSettings.php';

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